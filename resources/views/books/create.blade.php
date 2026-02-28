<x-layouts.app title="Add Book">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add New Book</h1>
        <p class="mt-1 text-sm text-slate-600">Register inventory with category and author mapping.</p>
    </div>

    <form action="{{ route('books.store') }}" method="POST" class="panel">
        @csrf
        @include('books._form', ['submitLabel' => 'Create Book'])
    </form>
</x-layouts.app>
