<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BorrowController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isManager = $user->isAdminOrStaff();
        $status = $request->string('status')->toString();
        $searchTerm = $request->string('q')->toString();

        $filteredQuery = Borrow::query()
            ->with(['book.category', 'member', 'processedBy'])
            ->when(! $isManager, function ($builder) use ($user) {
                $builder->whereHas('member', fn ($memberQuery) => $memberQuery->where('user_id', $user->id));
            })
            ->when(filled($status), fn ($builder) => $builder->where('status', $status))
            ->when(filled($searchTerm), function ($builder) use ($searchTerm) {
                $builder->where(function ($nested) use ($searchTerm) {
                    $nested->whereHas('book', fn ($bookQuery) => $bookQuery->where('title', 'like', "%{$searchTerm}%"))
                        ->orWhereHas('member', fn ($memberQuery) => $memberQuery->where('name', 'like', "%{$searchTerm}%")->orWhere('membership_no', 'like', "%{$searchTerm}%"));
                });
            });

        $summaryQuery = clone $filteredQuery;
        $borrows = (clone $filteredQuery)
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'pending' => (clone $summaryQuery)->where('status', Borrow::STATUS_PENDING)->count(),
            'approved' => (clone $summaryQuery)->where('status', Borrow::STATUS_APPROVED)->count(),
            'overdue' => (clone $summaryQuery)->overdue()->count(),
            'returned' => (clone $summaryQuery)->where('status', Borrow::STATUS_RETURNED)->count(),
        ];

        return view('borrows.index', [
            'borrows' => $borrows,
            'summary' => $summary,
            'isManager' => $isManager,
            'filters' => [
                'q' => $searchTerm,
                'status' => $status,
            ],
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->hasRole('member'), 403);

        $books = Book::query()
            ->with('authors')
            ->withCount('activeBorrows')
            ->orderBy('title')
            ->get()
            ->filter(fn (Book $book) => $book->copies > $book->active_borrows_count)
            ->values();

        return view('borrows.create', [
            'books' => $books,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasRole('member'), 403);

        $validated = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'due_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:'.now()->addDays(30)->toDateString()],
            'requested_note' => ['nullable', 'string', 'max:1000'],
            'proof_photo' => ['required', 'image', 'max:4096'],
        ]);

        $member = $this->resolveMember($user->id);

        if ($member->activeBorrows()->count() >= 3) {
            return back()->withErrors(['borrow' => 'Borrow limit reached. Return a book before requesting another one.'])->withInput();
        }

        $book = Book::findOrFail($validated['book_id']);

        if (! $this->isBookAvailable($book)) {
            return back()->withErrors(['borrow' => 'No copies are currently available for this book.'])->withInput();
        }

        $path = $request->file('proof_photo')->store('borrow-proofs', 'public');

        Borrow::create([
            'member_id' => $member->id,
            'book_id' => $book->id,
            'borrow_date' => now()->toDateString(),
            'due_date' => $validated['due_date'],
            'status' => Borrow::STATUS_PENDING,
            'returned' => false,
            'proof_photo_path' => $path,
            'requested_note' => $validated['requested_note'] ?? null,
        ]);

        return redirect()
            ->route('borrows.index')
            ->with('status', 'Borrow request created. A librarian will review it shortly.');
    }

    public function approve(Request $request, Borrow $borrow)
    {
        $this->ensureManager($request);

        $validated = $request->validate([
            'due_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:'.now()->addDays(60)->toDateString()],
            'processed_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($borrow->status !== Borrow::STATUS_PENDING) {
            return back()->withErrors(['borrow' => 'Only pending requests can be approved.']);
        }

        if (! $this->isBookAvailable($borrow->book)) {
            return back()->withErrors(['borrow' => 'Cannot approve this request: book is no longer available.']);
        }

        $borrow->update([
            'status' => Borrow::STATUS_APPROVED,
            'due_date' => $validated['due_date'],
            'processed_note' => $validated['processed_note'] ?? null,
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        return back()->with('status', 'Borrow request approved.');
    }

    public function reject(Request $request, Borrow $borrow)
    {
        $this->ensureManager($request);

        $validated = $request->validate([
            'processed_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($borrow->status !== Borrow::STATUS_PENDING) {
            return back()->withErrors(['borrow' => 'Only pending requests can be rejected.']);
        }

        $borrow->update([
            'status' => Borrow::STATUS_REJECTED,
            'processed_note' => $validated['processed_note'] ?? null,
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        return back()->with('status', 'Borrow request rejected.');
    }

    public function markReturned(Request $request, Borrow $borrow)
    {
        $this->ensureManager($request);

        if ($borrow->status !== Borrow::STATUS_APPROVED) {
            return back()->withErrors(['borrow' => 'Only approved borrows can be marked as returned.']);
        }

        $borrow->update([
            'status' => Borrow::STATUS_RETURNED,
            'return_date' => now()->toDateString(),
            'returned' => true,
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        return back()->with('status', 'Borrow marked as returned.');
    }

    private function resolveMember(int $userId): Member
    {
        return Member::firstOrCreate(
            ['user_id' => $userId],
            [
                'membership_no' => 'LIB-'.now()->format('Y').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ]
        );
    }

    private function isBookAvailable(Book $book): bool
    {
        $activeCount = Borrow::query()
            ->where('book_id', $book->id)
            ->where('status', Borrow::STATUS_APPROVED)
            ->whereNull('return_date')
            ->count();

        return $book->copies > $activeCount;
    }

    private function ensureManager(Request $request): void
    {
        abort_unless($request->user()->isAdminOrStaff(), 403);
    }
}
