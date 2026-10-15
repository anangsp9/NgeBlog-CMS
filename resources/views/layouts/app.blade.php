<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteName = config('app.name');
        $metaTitle = trim(strip_tags($__env->yieldContent('title', $siteName)));
        $metaDescription = trim(strip_tags($__env->yieldContent('description', 'NgeBlog! — Nulis. Terbit. Beres. Blog sederhana untuk belajar menulis.')));
        $metaUrl = trim($__env->yieldContent('canonical', url()->current())) ?: url()->current();
        $metaImage = trim($__env->yieldContent('image', '')) ?: url('images/og-default.png');
        $metaType = trim($__env->yieldContent('og_type', 'website')) ?: 'website';
    @endphp

    <title>{{ $metaTitle }}@if ($metaTitle !== $siteName) &mdash; {{ $siteName }}@endif</title>

    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $metaUrl }}">

    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $metaType }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $metaUrl }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:image:alt" content="{{ $metaTitle }}">
    <meta property="og:locale" content="id_ID">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $metaImage }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="alternate" type="application/rss+xml" title="{{ $siteName }}" href="{{ route('feed') }}">

    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
<nav class="sticky top-0 z-30 border-b border-gray-200 bg-white/95 backdrop-blur" aria-label="Navigasi utama">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-4">
        <a href="{{ route('home') }}" class="shrink-0 text-lg font-bold tracking-tight text-gray-900">
            NgeBlog!
        </a>

        <label
            for="main-nav"
            class="-mr-2 cursor-pointer rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 lg:hidden"
        >
            <span class="sr-only">Menu</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" d="M3 5h14M3 10h14M3 15h14"/>
            </svg>
        </label>

        {{-- Varian peer-checked Tailwind menghasilkan selector `:is(.peer:checked ~ *)`,
             artinya hanya berlaku pada SAUDARA KANDUNG, bukan turunan. Input ini wajib
             duduk sebagai sibling langsung dari #main-menu; bila diletakkan di luar
             <nav>, toggle hamburger tidak pernah membuka menu apa pun. --}}
        <input
            type="checkbox"
            id="main-nav"
            class="peer sr-only"
            aria-label="Buka menu navigasi"
        >

        <div
            id="main-menu"
            class="invisible absolute inset-x-0 top-full max-h-[calc(100vh-4rem)] overflow-y-auto border-b border-gray-200 bg-white px-4 pb-4 opacity-0 transition peer-checked:visible peer-checked:opacity-100 lg:static lg:visible lg:flex lg:max-h-none lg:items-center lg:gap-5 lg:overflow-visible lg:border-0 lg:bg-transparent lg:px-0 lg:pb-0 lg:opacity-100"
        >
            <div class="flex flex-col gap-1 text-sm lg:flex-row lg:items-center lg:gap-5">
                <a href="{{ route('articles.index') }}" class="rounded-md px-2 py-2 font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 lg:px-0 lg:py-0 lg:hover:bg-transparent">
                    Articles
                </a>

                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-md px-2 py-2 font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 lg:px-0 lg:py-0 lg:hover:bg-transparent">
                            Dashboard
                        </a>
                    @endif

                    <span class="truncate px-2 py-2 text-gray-500 lg:px-0 lg:py-0">{{ auth()->user()->name }}</span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-md px-2 py-2 text-left font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 lg:w-auto lg:px-0 lg:py-0 lg:hover:bg-transparent">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-md px-2 py-2 font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 lg:px-0 lg:py-0 lg:hover:bg-transparent">
                        Login
                    </a>
                    <a
                        href="{{ route('register') }}"
                        class="rounded-md bg-gray-900 px-3 py-2 font-semibold text-white hover:bg-gray-700 lg:px-3 lg:py-2"
                    >
                        Register
                    </a>
                @endguest
            </div>
        </div>
    </div>
</nav>

<main class="mx-auto max-w-6xl px-4 py-8">
    @include('partials.flash')

    @yield('content')
</main>

<footer class="mt-16 border-t border-gray-200 bg-white">
    <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-8 text-sm text-gray-500 sm:flex-row sm:items-center sm:justify-between">
        <p>&copy; {{ date('Y') }} {{ $siteName }} &mdash; Nulis. Terbit. Beres.</p>

        <nav class="flex flex-wrap gap-4" aria-label="Navigasi footer">
            <a href="{{ route('home') }}" class="hover:text-gray-900">Home</a>
            <a href="{{ route('articles.index') }}" class="hover:text-gray-900">Articles</a>
            <a href="{{ url('sitemap.xml') }}" class="hover:text-gray-900">Sitemap</a>
            <a href="{{ url('feed') }}" class="hover:text-gray-900">RSS</a>
        </nav>
    </div>
</footer>

<script>
    (function () {
        var toggle = document.getElementById('main-nav');

        if (!toggle) {
            return;
        }

        document.querySelectorAll('nav a[href]').forEach(function (link) {
            link.addEventListener('click', function () {
                toggle.checked = false;
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                toggle.checked = false;
            }
        });
    })();
</script>
</body>
</html>