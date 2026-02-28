<x-layouts.app :title="$isManager ? 'Manage Borrows' : 'My Borrows'">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $isManager ? 'Borrow Management' : 'My Borrow Requests' }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $isManager ? 'Review requests, issue books, and close returns.' : 'Track your borrow request lifecycle and due dates.' }}</p>
        </div>
        @if (! $isManager)
            <a href="{{ route('borrows.create') }}" class="btn-primary">New Request</a>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <p class="text-xs uppercase tracking-wide text-slate-500">Pending</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $summary['pending'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase tracking-wide text-slate-500">Approved</p>
            <p class="mt-2 text-2xl font-bold text-sky-700">{{ $summary['approved'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase tracking-wide text-slate-500">Overdue</p>
            <p class="mt-2 text-2xl font-bold text-rose-700">{{ $summary['overdue'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase tracking-wide text-slate-500">Returned</p>
            <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $summary['returned'] }}</p>
        </div>
    </div>

    <form method="GET" action="{{ $isManager ? route('borrows.manage') : route('borrows.index') }}" class="panel grid gap-3 md:grid-cols-4">
        <div class="md:col-span-3">
            <label class="label-text" for="q">Search book/member</label>
            <input type="text" id="q" name="q" value="{{ $filters['q'] }}" class="input-control" placeholder="Search...">
        </div>
        <div>
            <label class="label-text" for="status">Status</label>
            <select id="status" name="status" class="input-control">
                <option value="">All statuses</option>
                <option value="pending" @selected($filters['status'] === 'pending')>Pending</option>
                <option value="approved" @selected($filters['status'] === 'approved')>Approved</option>
                <option value="rejected" @selected($filters['status'] === 'rejected')>Rejected</option>
                <option value="returned" @selected($filters['status'] === 'returned')>Returned</option>
            </select>
        </div>
        <div class="md:col-span-4 flex justify-end gap-2">
            <a href="{{ $isManager ? route('borrows.manage') : route('borrows.index') }}" class="btn-secondary">Reset</a>
            <button class="btn-primary">Filter</button>
        </div>
    </form>

    <div class="space-y-3">
        @forelse ($borrows as $borrow)
            @php
                $statusClasses = [
                    'pending' => 'bg-amber-100 text-amber-700',
                    'approved' => 'bg-sky-100 text-sky-700',
                    'rejected' => 'bg-rose-100 text-rose-700',
                    'returned' => 'bg-emerald-100 text-emerald-700',
                ];
                $badgeClass = $statusClasses[$borrow->status] ?? 'bg-slate-100 text-slate-700';
            @endphp
            <article class="panel">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ $borrow->book->title }}</h2>
                        <p class="text-sm text-slate-600">
                            Member: {{ $borrow->member->name }}
                            @if ($borrow->member->membership_no)
                                ({{ $borrow->member->membership_no }})
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            Borrow Date: {{ $borrow->borrow_date?->format('M d, Y') }}
                            · Due: {{ $borrow->due_date?->format('M d, Y') ?? 'N/A' }}
                            · Returned: {{ $borrow->return_date?->format('M d, Y') ?? 'Not returned' }}
                        </p>
                        @if ($borrow->is_overdue)
                            <p class="mt-1 text-xs font-semibold text-rose-700">Overdue</p>
                        @endif
                    </div>

                    <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $badgeClass }}">
                        {{ $borrow->status }}
                    </span>
                </div>

                <div class="mt-3 grid gap-4 md:grid-cols-3">
                    <div class="md:col-span-2 space-y-2 text-sm text-slate-700">
                        @if ($borrow->requested_note)
                            <p><span class="font-semibold text-slate-900">Request note:</span> {{ $borrow->requested_note }}</p>
                        @endif
                        @if ($borrow->processed_note)
                            <p><span class="font-semibold text-slate-900">Processed note:</span> {{ $borrow->processed_note }}</p>
                        @endif
                        @if ($borrow->processedBy)
                            <p class="text-xs text-slate-500">Last processed by {{ $borrow->processedBy->name }} on {{ $borrow->processed_at?->format('M d, Y h:i A') }}</p>
                        @endif
                    </div>

                    @if ($borrow->proof_photo_path)
                        <div>
                            <p class="label-text">Proof Photo</p>
                            <a href="{{ asset('storage/'.$borrow->proof_photo_path) }}" target="_blank" class="block overflow-hidden rounded-xl border border-slate-200">
                                <img src="{{ asset('storage/'.$borrow->proof_photo_path) }}" alt="Proof photo" class="h-32 w-full object-cover">
                            </a>
                        </div>
                    @endif
                </div>

                @if ($isManager)
                    <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                        @if ($borrow->status === 'pending')
                            <form action="{{ route('borrows.approve', $borrow) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="date" name="due_date" value="{{ $borrow->due_date?->toDateString() ?? now()->addDays(14)->toDateString() }}" class="input-control w-auto" required>
                                <input type="text" name="processed_note" class="input-control w-64" placeholder="Approval note (optional)">
                                <button class="btn-primary">Approve</button>
                            </form>

                            <form action="{{ route('borrows.reject', $borrow) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="text" name="processed_note" class="input-control w-64" placeholder="Rejection reason (optional)">
                                <button class="btn-danger">Reject</button>
                            </form>
                        @endif

                        @if ($borrow->status === 'approved')
                            <form action="{{ route('borrows.return', $borrow) }}" method="POST">
                                @csrf
                                <button class="btn-secondary">Mark Returned</button>
                            </form>
                        @endif
                    </div>
                @endif
            </article>
        @empty
            <div class="panel text-center text-slate-500">No borrow records found.</div>
        @endforelse

        <div>
            {{ $borrows->links() }}
        </div>
    </div>
</x-layouts.app>
