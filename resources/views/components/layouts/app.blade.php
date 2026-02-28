<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Library Hub' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    @php
        $user = auth()->user();
        $isManager = $user->isAdminOrStaff();
    @endphp

    <div class="min-h-screen">
        <header class="border-b border-slate-200/80 bg-white/70 backdrop-blur">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <div>
                    <a href="{{ route('dashboard') }}" class="text-lg font-bold tracking-tight text-slate-900">Library Control Panel</a>
                    <p class="text-xs text-slate-500">Role: <span class="font-semibold uppercase tracking-wide text-sky-700">{{ $user->role }}</span></p>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn-secondary">Logout</button>
                </form>
            </div>
        </header>

        <main class="mx-auto grid w-full max-w-7xl gap-5 px-4 py-6 sm:px-6 lg:grid-cols-[250px_1fr] lg:px-8">
            <aside class="panel h-fit">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Navigation</p>
                <nav class="space-y-2 text-sm">
                    <a href="{{ route('dashboard') }}" class="block rounded-xl px-3 py-2 transition hover:bg-slate-100 {{ request()->routeIs('dashboard') ? 'bg-slate-100 font-semibold text-slate-900' : 'text-slate-700' }}">Dashboard</a>
                    <a href="{{ route('books.index') }}" class="block rounded-xl px-3 py-2 transition hover:bg-slate-100 {{ request()->routeIs('books.*') ? 'bg-slate-100 font-semibold text-slate-900' : 'text-slate-700' }}">Books Catalog</a>

                    @if ($user->hasRole('member'))
                        <a href="{{ route('borrows.index') }}" class="block rounded-xl px-3 py-2 transition hover:bg-slate-100 {{ request()->routeIs('borrows.index') ? 'bg-slate-100 font-semibold text-slate-900' : 'text-slate-700' }}">My Borrows</a>
                        <a href="{{ route('borrows.create') }}" class="block rounded-xl px-3 py-2 transition hover:bg-slate-100 {{ request()->routeIs('borrows.create') ? 'bg-slate-100 font-semibold text-slate-900' : 'text-slate-700' }}">Request Borrow</a>
                    @endif

                    @if ($isManager)
                        <a href="{{ route('borrows.manage') }}" class="block rounded-xl px-3 py-2 transition hover:bg-slate-100 {{ request()->routeIs('borrows.manage') ? 'bg-slate-100 font-semibold text-slate-900' : 'text-slate-700' }}">Manage Borrows</a>
                        <a href="{{ route('books.create') }}" class="block rounded-xl px-3 py-2 transition hover:bg-slate-100 {{ request()->routeIs('books.create') ? 'bg-slate-100 font-semibold text-slate-900' : 'text-slate-700' }}">Add New Book</a>
                    @endif
                </nav>
            </aside>

            <section class="space-y-4">
                @if (session('status'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <p class="font-semibold">Please review the following issues:</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </section>
        </main>
    </div>
</body>
</html>
