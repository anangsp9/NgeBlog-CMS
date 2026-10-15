@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="mx-auto max-w-md">
    <h1 class="text-2xl font-bold text-gray-900">Login</h1>
    <p class="mt-1 text-sm text-gray-500">Masuk untuk mengakses dashboard.</p>

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
            >
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
            >
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input
                id="remember"
                name="remember"
                type="checkbox"
                class="rounded border-gray-300 text-gray-900 focus:ring-0"
            >
            <label for="remember" class="text-sm text-gray-600">Remember me</label>
        </div>

        <button
            type="submit"
            class="w-full rounded-md bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-700"
        >
            Login
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-medium text-gray-900 hover:underline">Daftar</a>
    </p>
</div>
@endsection
