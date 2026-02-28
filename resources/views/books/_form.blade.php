<div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="label-text" for="title">Title</label>
        <input id="title" type="text" name="title" value="{{ old('title', $book->title) }}" required class="input-control">
    </div>

    <div>
        <label class="label-text" for="isbn">ISBN</label>
        <input id="isbn" type="text" name="isbn" value="{{ old('isbn', $book->isbn) }}" required class="input-control">
    </div>

    <div>
        <label class="label-text" for="published_year">Published Year</label>
        <input id="published_year" type="number" name="published_year" value="{{ old('published_year', $book->published_year) }}" class="input-control" min="1000" max="{{ now()->year + 1 }}">
    </div>

    <div>
        <label class="label-text" for="category_id">Category</label>
        <select id="category_id" name="category_id" required class="input-control">
            <option value="">Select category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('category_id', $book->category_id) === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="label-text" for="copies">Copies</label>
        <input id="copies" type="number" name="copies" value="{{ old('copies', $book->copies ?: 1) }}" required class="input-control" min="1" max="500">
    </div>

    <div class="md:col-span-2">
        <label class="label-text" for="author_ids">Authors</label>
        @php
            $selectedAuthorIds = old('author_ids', $book->authors->pluck('id')->all());
        @endphp
        <select id="author_ids" name="author_ids[]" multiple required class="input-control min-h-32">
            @foreach ($authors as $author)
                <option value="{{ $author->id }}" @selected(in_array($author->id, $selectedAuthorIds))>{{ $author->name }} ({{ $author->country ?? 'Unknown' }})</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500">Hold Ctrl/Cmd to select multiple authors.</p>
    </div>
</div>

<div class="mt-6 flex justify-end gap-2">
    <a href="{{ route('books.index') }}" class="btn-secondary">Cancel</a>
    <button class="btn-primary">{{ $submitLabel }}</button>
</div>
