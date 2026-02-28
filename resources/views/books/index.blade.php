<x-layouts.app title="Books Catalog">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Books Catalog</h1>
            <p class="mt-1 text-sm text-slate-600">Browse inventory, track circulation load, and inspect availability.</p>
        </div>
        @if (auth()->user()->isAdminOrStaff())
            <a href="{{ route('books.create') }}" class="btn-primary">Add Book</a>
        @endif
    </div>

    <form method="GET" action="{{ route('books.index') }}" class="panel grid gap-3 md:grid-cols-4">
        <div class="md:col-span-2">
            <label class="label-text" for="q">Search title / ISBN / author</label>
            <input type="text" id="q" name="q" value="{{ $filters['q'] }}" class="input-control" placeholder="Search books...">
        </div>
        <div>
            <label class="label-text" for="category">Category</label>
            <select name="category" id="category" class="input-control">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) $filters['category'] === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label-text" for="availability">Availability</label>
            <select name="availability" id="availability" class="input-control">
                <option value="">All</option>
                <option value="available" @selected($filters['availability'] === 'available')>Available</option>
                <option value="unavailable" @selected($filters['availability'] === 'unavailable')>Unavailable</option>
            </select>
        </div>
        <div class="md:col-span-4 flex justify-end gap-2">
            <a href="{{ route('books.index') }}" class="btn-secondary">Reset</a>
            <button class="btn-primary">Apply Filters</button>
        </div>
    </form>

    <div class="panel overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="pb-3">Book</th>
                    <th class="pb-3">Category</th>
                    <th class="pb-3">Authors</th>
                    <th class="pb-3">Circulation</th>
                    <th class="pb-3">Available</th>
                    @if (auth()->user()->isAdminOrStaff())
                        <th class="pb-3 text-right">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($books as $book)
                    @php $available = max($book->copies - $book->active_borrow_count, 0); @endphp
                    <tr>
                        <td class="py-3">
                            <p class="font-semibold text-slate-900">{{ $book->title }}</p>
                            <p class="text-xs text-slate-500">ISBN {{ $book->isbn }} · {{ $book->published_year ?? 'N/A' }}</p>
                        </td>
                        <td class="py-3 text-slate-700">{{ $book->category?->name }}</td>
                        <td class="py-3 text-slate-700">{{ $book->authors->pluck('name')->join(', ') }}</td>
                        <td class="py-3 text-slate-700">{{ $book->total_borrow_count }} total / {{ $book->active_borrow_count }} active</td>
                        <td class="py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $available > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $available }}/{{ $book->copies }}
                            </span>
                        </td>
                        @if (auth()->user()->isAdminOrStaff())
                            <td class="py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('books.edit', $book) }}" class="btn-secondary px-3 py-1.5">Edit</a>
                                    <form action="{{ route('books.destroy', $book) }}" method="POST" onsubmit="return confirm('Delete this book?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-danger px-3 py-1.5">Delete</button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-500">No books found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $books->links() }}
        </div>
    </div>
</x-layouts.app>
