<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <title>@yield('code') &mdash; {{ config('app.name') }}</title>

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @endif
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
    <main class="mx-auto flex min-h-screen max-w-xl flex-col items-center justify-center px-4 py-16 text-center">
        <p class="text-6xl font-bold tracking-tight text-gray-900 sm:text-7xl">
            @yield('code')
        </p>

        <h1 class="mt-4 text-2xl font-bold text-gray-900 sm:text-3xl">
            @yield('heading')
        </h1>

        <p class="mt-3 text-base leading-relaxed text-gray-600">
            @yield('message')
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a
                href="{{ url('/') }}"
                class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
            >
                Kembali ke Beranda
            </a>

            <a
                href="{{ url('/articles') }}"
                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100"
            >
                Jelajahi Artikel
            </a>
        </div>
    </main>
</body>
</html>