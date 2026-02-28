<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\Category;
use App\Models\Member;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = $this->stats();

        $topBooks = Book::query()
            ->select('books.id', 'books.title')
            ->selectRaw('COUNT(borrows.id) as borrow_count')
            ->selectRaw('SUM(CASE WHEN borrows.status = ? AND borrows.return_date IS NULL THEN 1 ELSE 0 END) as active_count', [Borrow::STATUS_APPROVED])
            ->leftJoin('borrows', 'borrows.book_id', '=', 'books.id')
            ->groupBy('books.id', 'books.title')
            ->orderByDesc('borrow_count')
            ->limit(6)
            ->get();

        $categoryPerformance = Category::query()
            ->select('categories.id', 'categories.name')
            ->selectRaw('COUNT(DISTINCT books.id) as total_books')
            ->selectRaw('COUNT(borrows.id) as total_borrows')
            ->selectRaw('ROUND(AVG(CASE WHEN borrows.return_date IS NOT NULL THEN DATEDIFF(borrows.return_date, borrows.borrow_date) END), 1) as avg_return_days')
            ->leftJoin('books', 'books.category_id', '=', 'categories.id')
            ->leftJoin('borrows', 'borrows.book_id', '=', 'books.id')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_borrows')
            ->limit(8)
            ->get();

        $topMembers = Member::query()
            ->select('members.id', 'members.name', 'members.membership_no')
            ->selectRaw('COUNT(borrows.id) as total_borrows')
            ->selectRaw('SUM(CASE WHEN borrows.status = ? AND borrows.return_date IS NULL THEN 1 ELSE 0 END) as current_loans', [Borrow::STATUS_APPROVED])
            ->join('borrows', 'borrows.member_id', '=', 'members.id')
            ->groupBy('members.id', 'members.name', 'members.membership_no')
            ->orderByDesc('total_borrows')
            ->limit(6)
            ->get();

        $borrowTrend = $this->borrowTrend();

        return view('dashboard.index', [
            'stats' => $stats,
            'topBooks' => $topBooks,
            'categoryPerformance' => $categoryPerformance,
            'topMembers' => $topMembers,
            'borrowTrend' => $borrowTrend,
        ]);
    }

    private function stats(): array
    {
        $totalBooks = Book::count();
        $totalCopies = (int) Book::sum('copies');
        $activeBorrowedCopies = Borrow::approved()->whereNull('return_date')->count();

        return [
            'total_books' => $totalBooks,
            'total_members' => Member::count(),
            'active_loans' => $activeBorrowedCopies,
            'pending_requests' => Borrow::pending()->count(),
            'overdue_loans' => Borrow::overdue()->count(),
            'available_copies' => max($totalCopies - $activeBorrowedCopies, 0),
            'return_rate' => $this->returnRate(),
        ];
    }

    private function returnRate(): float
    {
        $totals = Borrow::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as returned', [Borrow::STATUS_RETURNED])
            ->first();

        if (! $totals || $totals->total == 0) {
            return 0.0;
        }

        return round(($totals->returned / $totals->total) * 100, 1);
    }

    private function borrowTrend(): Collection
    {
        $start = now()->copy()->subMonths(5)->startOfMonth();

        $rawTrend = Borrow::query()
            ->whereDate('borrow_date', '>=', $start)
            ->selectRaw("DATE_FORMAT(borrow_date, '%Y-%m') as month_key")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->pluck('total', 'month_key');

        return collect(range(0, 5))->map(function (int $offset) use ($start, $rawTrend): array {
            $month = $start->copy()->addMonths($offset);
            $key = $month->format('Y-m');

            return [
                'label' => $month->format('M Y'),
                'value' => (int) ($rawTrend[$key] ?? 0),
            ];
        });
    }
}
