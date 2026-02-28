<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | Library Hub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div class="mx-auto flex min-h-screen max-w-6xl items-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid w-full gap-8 lg:grid-cols-2">
            <section class="panel space-y-4">
                <p class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-sky-700">Library Workflow</p>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Manage lending operations with role-based control</h1>
                <p class="text-sm leading-6 text-slate-600">Track borrow requests, approve returns, monitor overdue books, and inspect analytics from a unified dashboard.</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="panel-muted">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Borrow Tracking</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">Photo-backed request validation</p>
                    </div>
                    <div class="panel-muted">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Insights</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">Complex category and member trends</p>
                    </div>
                </div>
            </section>

            <section class="panel">
                <h2 class="text-xl font-bold text-slate-900">Sign in</h2>
                <p class="mt-1 text-sm text-slate-600">Use your registered account to continue.</p>

                @if (session('status'))
                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ $errors->first() }}</div>
                @endif

                <form action="{{ route('login.submit') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="label-text">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input-control" placeholder="member@example.com">
                    </div>
                    <div>
                        <label for="password" class="label-text">Password</label>
                        <input id="password" type="password" name="password" required class="input-control" placeholder="********">
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        <span>Remember me</span>
                    </label>
                    <button class="btn-primary w-full">Login</button>
                </form>

                <p class="mt-5 text-sm text-slate-600">
                    New member?
                    <a href="{{ route('register') }}" class="font-semibold text-sky-700 hover:text-sky-900">Create account</a>
                </p>
            </section>
        </div>
    </div>
</body>
</html>
