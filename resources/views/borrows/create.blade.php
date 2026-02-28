<x-layouts.app title="Request Borrow">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Request Book Borrow</h1>
        <p class="mt-1 text-sm text-slate-600">Upload a photo document as proof before submitting your borrow request.</p>
    </div>

    <form action="{{ route('borrows.store') }}" method="POST" enctype="multipart/form-data" class="panel grid gap-4 md:grid-cols-2">
        @csrf

        <div class="md:col-span-2">
            <label class="label-text" for="book_id">Select Book</label>
            <select id="book_id" name="book_id" required class="input-control">
                <option value="">Choose an available book</option>
                @foreach ($books as $book)
                    @php $available = max($book->copies - $book->active_borrows_count, 0); @endphp
                    <option value="{{ $book->id }}" @selected((int) old('book_id') === $book->id)>
                        {{ $book->title }} ({{ $available }} of {{ $book->copies }} available)
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="label-text" for="due_date">Requested Due Date</label>
            <input id="due_date" type="date" name="due_date" value="{{ old('due_date', now()->addDays(14)->toDateString()) }}" class="input-control" required>
        </div>

        <div>
            <label class="label-text" for="proof_photo">Proof Photo</label>
            <input id="proof_photo" type="file" name="proof_photo" accept="image/*" required class="input-control">
            <p class="mt-1 text-xs text-slate-500">JPG/PNG/WEBP up to 4MB.</p>
        </div>

        <div class="md:col-span-2">
            <label class="label-text" for="requested_note">Request Note (Optional)</label>
            <textarea id="requested_note" name="requested_note" rows="4" class="input-control" placeholder="Reason for borrowing, required references, etc.">{{ old('requested_note') }}</textarea>
        </div>

        <div class="md:col-span-2 flex justify-end gap-2">
            <a href="{{ route('borrows.index') }}" class="btn-secondary">Cancel</a>
            <button class="btn-primary">Submit Request</button>
        </div>
    </form>
</x-layouts.app>
