<x-layouts.app title="Dashboard">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard</h1>
            <p class="mt-1 text-sm text-slate-600">Operational summary, borrowing trends, and circulation analytics.</p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <p class="text-xs uppercase tracking-wide text-slate-500">Books</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['total_books'] }}</p>
            <p class="text-xs text-slate-500">Available copies: {{ $stats['available_copies'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase tracking-wide text-slate-500">Members</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['total_members'] }}</p>
            <p class="text-xs text-slate-500">Active loans: {{ $stats['active_loans'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase tracking-wide text-slate-500">Borrow Pipeline</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['pending_requests'] }}</p>
            <p class="text-xs text-slate-500">Pending requests</p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase tracking-wide text-slate-500">Risk Monitor</p>
            <p class="mt-2 text-2xl font-bold text-rose-700">{{ $stats['overdue_loans'] }}</p>
            <p class="text-xs text-slate-500">Return rate: {{ $stats['return_rate'] }}%</p>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <div class="panel">
            <h2 class="text-lg font-semibold text-slate-900">Borrow Trend (Last 6 Months)</h2>
            <div class="mt-4 space-y-3">
                @php $maxTrend = max($borrowTrend->max('value'), 1); @endphp
                @foreach ($borrowTrend as $point)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs text-slate-600">
                            <span>{{ $point['label'] }}</span>
                            <span>{{ $point['value'] }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-sky-500" style="width: {{ ($point['value'] / $maxTrend) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="panel">
            <h2 class="text-lg font-semibold text-slate-900">Top Borrowed Books</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="pb-2">Book</th>
                            <th class="pb-2">Borrows</th>
                            <th class="pb-2">Active</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($topBooks as $row)
                            <tr>
                                <td class="py-2 font-medium text-slate-800">{{ $row->title }}</td>
                                <td class="py-2 text-slate-600">{{ $row->borrow_count }}</td>
                                <td class="py-2 text-slate-600">{{ $row->active_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-slate-500">No borrow data yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <div class="panel">
            <h2 class="text-lg font-semibold text-slate-900">Category Performance</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="pb-2">Category</th>
                            <th class="pb-2">Books</th>
                            <th class="pb-2">Borrows</th>
                            <th class="pb-2">Avg Return Days</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($categoryPerformance as $row)
                            <tr>
                                <td class="py-2 font-medium text-slate-800">{{ $row->name }}</td>
                                <td class="py-2 text-slate-600">{{ $row->total_books }}</td>
                                <td class="py-2 text-slate-600">{{ $row->total_borrows }}</td>
                                <td class="py-2 text-slate-600">{{ $row->avg_return_days ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-slate-500">No category analytics available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <h2 class="text-lg font-semibold text-slate-900">Most Active Members</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="pb-2">Member</th>
                            <th class="pb-2">ID</th>
                            <th class="pb-2">Borrows</th>
                            <th class="pb-2">Current</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($topMembers as $row)
                            <tr>
                                <td class="py-2 font-medium text-slate-800">{{ $row->name }}</td>
                                <td class="py-2 text-slate-600">{{ $row->membership_no }}</td>
                                <td class="py-2 text-slate-600">{{ $row->total_borrows }}</td>
                                <td class="py-2 text-slate-600">{{ $row->current_loans }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-slate-500">No active member data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
