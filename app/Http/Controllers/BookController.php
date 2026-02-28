<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->string('q')->toString();
        $categoryId = $request->integer('category');
        $availability = $request->string('availability')->toString();

        $books = Book::query()
            ->with(['category', 'authors'])
            ->withCount([
                'borrows as total_borrow_count',
                'activeBorrows as active_borrow_count',
            ])
            ->search($search)
            ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
            ->when(
                $availability === 'available',
                fn ($query) => $query->whereRaw(
                    'copies > (SELECT COUNT(*) FROM borrows WHERE borrows.book_id = books.id AND borrows.status = ? AND borrows.return_date IS NULL)',
                    [Borrow::STATUS_APPROVED]
                )
            )
            ->when(
                $availability === 'unavailable',
                fn ($query) => $query->whereRaw(
                    'copies <= (SELECT COUNT(*) FROM borrows WHERE borrows.book_id = books.id AND borrows.status = ? AND borrows.return_date IS NULL)',
                    [Borrow::STATUS_APPROVED]
                )
            )
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('books.index', [
            'books' => $books,
            'categories' => Category::orderBy('name')->get(),
            'filters' => [
                'q' => $search,
                'category' => $categoryId > 0 ? $categoryId : null,
                'availability' => $availability,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeManagement();

        return view('books.create', [
            'book' => new Book,
            'categories' => Category::orderBy('name')->get(),
            'authors' => Author::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeManagement();
        $validated = $this->validateBook($request);

        $book = Book::create($validated);
        $book->authors()->sync($request->input('author_ids', []));

        return redirect()
            ->route('books.index')
            ->with('status', 'Book created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        return redirect()->route('books.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        $this->authorizeManagement();

        return view('books.edit', [
            'book' => $book->load('authors'),
            'categories' => Category::orderBy('name')->get(),
            'authors' => Author::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        $this->authorizeManagement();
        $validated = $this->validateBook($request, $book);

        $book->update($validated);
        $book->authors()->sync($request->input('author_ids', []));

        return redirect()
            ->route('books.index')
            ->with('status', 'Book updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $this->authorizeManagement();

        $activeBorrows = $book->activeBorrows()->count();

        if ($activeBorrows > 0) {
            return back()->withErrors([
                'book' => 'This book has active loans and cannot be deleted.',
            ]);
        }

        $book->authors()->detach();
        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('status', 'Book deleted successfully.');
    }

    private function validateBook(Request $request, ?Book $book = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'isbn' => [
                'required',
                'string',
                'max:32',
                Rule::unique('books', 'isbn')->ignore($book?->id),
            ],
            'category_id' => ['required', 'exists:categories,id'],
            'published_year' => ['nullable', 'integer', 'between:1000,'.(now()->year + 1)],
            'copies' => ['required', 'integer', 'min:1', 'max:500'],
            'author_ids' => ['required', 'array', 'min:1'],
            'author_ids.*' => ['required', 'exists:authors,id'],
        ]);
    }

    private function authorizeManagement(): void
    {
        abort_unless(auth()->user()?->isAdminOrStaff(), 403);
    }
}
