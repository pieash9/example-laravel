<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register | Library Hub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div class="mx-auto flex min-h-screen max-w-3xl items-center px-4 py-10 sm:px-6 lg:px-8">
        <section class="panel w-full">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Create Member Account</h1>
            <p class="mt-1 text-sm text-slate-600">Registration creates both your login and member profile.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.submit') }}" method="POST" class="mt-6 grid gap-4 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <label for="name" class="label-text">Full Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required class="input-control">
                </div>

                <div>
                    <label for="email" class="label-text">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input-control">
                </div>

                <div>
                    <label for="phone" class="label-text">Phone</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="input-control">
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="label-text">Address</label>
                    <input id="address" type="text" name="address" value="{{ old('address') }}" class="input-control">
                </div>

                <div>
                    <label for="password" class="label-text">Password</label>
                    <input id="password" type="password" name="password" required class="input-control">
                </div>

                <div>
                    <label for="password_confirmation" class="label-text">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="input-control">
                </div>

                <div class="sm:col-span-2 flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Already have an account? Login</a>
                    <button class="btn-primary">Create Account</button>
                </div>
            </form>
        </section>
    </div>
</body>
</html>
