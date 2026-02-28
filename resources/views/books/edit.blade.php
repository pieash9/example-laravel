<x-layouts.app title="Edit Book">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Book</h1>
        <p class="mt-1 text-sm text-slate-600">Update metadata, category, copies, and author assignments.</p>
    </div>

    <form action="{{ route('books.update', $book) }}" method="POST" class="panel">
        @csrf
        @method('PUT')
        @include('books._form', ['submitLabel' => 'Update Book'])
    </form>
</x-layouts.app>
