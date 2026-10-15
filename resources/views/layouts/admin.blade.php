<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <title>@yield('title', 'Admin') &mdash; NgeBlog!</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-100 text-gray-900 antialiased">
@php
    $links = [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard'],
        ['route' => 'admin.articles.index', 'label' => 'Articles'],
        ['route' => 'admin.categories.index', 'label' => 'Categories'],
        ['route' => 'admin.comments.index', 'label' => 'Comments'],
        ['route' => 'admin.users.index', 'label' => 'Users'],
    ];
@endphp

<div class="flex min-h-screen">
    {{-- Varian peer-checked Tailwind menghasilkan selector `:is(.peer:checked ~ *)`
         yang hanya berlaku pada saudara kandung. Input, overlay, dan aside harus
         sejajar dalam satu induk; bila input berada di luar wadah ini, aside tidak
         akan pernah bergeser masuk saat tombol menu diklik di layar sempit. --}}
    <input
        type="checkbox"
        id="admin-nav"
        class="peer sr-only"
        aria-label="Buka menu navigasi admin"
    >

    <label
        for="admin-nav"
        class="fixed inset-0 z-30 hidden bg-gray-900/50 peer-checked:block lg:hidden"
        aria-hidden="true"
    ></label>

    <aside
        class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full border-r border-gray-200 bg-white transition-transform duration-200 peer-checked:translate-x-0 lg:static lg:z-auto lg:w-60 lg:translate-x-0"
        aria-label="Navigasi admin"
    >
        <div class="flex items-center justify-between px-6 py-5">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-base font-bold tracking-tight">NgeBlog!</a>
                <p class="mt-0.5 text-xs uppercase tracking-wide text-gray-400">Admin Panel</p>
            </div>

            <label for="admin-nav" class="-mr-2 rounded-md p-2 text-gray-500 hover:bg-gray-100 lg:hidden">
                <span class="sr-only">Tutup menu</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" d="M6 6l8 8M14 6l-8 8"/>
                </svg>
            </label>
        </div>

        <nav class="space-y-1 px-3 pb-6 text-sm">
            @foreach ($links as $link)
                <a
                    href="{{ route($link['route']) }}"
                    data-admin-nav-link
                    @class([
                        'block rounded-md px-3 py-2 font-medium',
                        'bg-gray-900 text-white' => request()->routeIs($link['route']),
                        'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => ! request()->routeIs($link['route']),
                    ])
                >
                    {{ $link['label'] }}
                </a>
            @endforeach

            <a href="{{ route('home') }}" class="block rounded-md px-3 py-2 font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900">
                &larr; Lihat Blog
            </a>
        </nav>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-gray-200 bg-white px-4 py-3 lg:px-8">
            <label for="admin-nav" class="-ml-2 rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 lg:hidden">
                <span class="sr-only">Buka menu</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" d="M3 5h14M3 10h14M3 15h14"/>
                </svg>
            </label>

            <span class="min-w-0 flex-1 truncate text-sm text-gray-500">
                Login sebagai <span class="font-semibold text-gray-900">{{ auth()->user()->name }}</span>
            </span>

            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button type="submit" class="text-sm font-medium text-gray-600 hover:text-gray-900">Logout</button>
            </form>
        </header>

        <main class="flex-1 p-4 lg:p-8">
            @include('partials.flash')

            @yield('content')
        </main>
    </div>
</div>

<script>
    (function () {
        var toggle = document.getElementById('admin-nav');

        if (!toggle) {
            return;
        }

        var close = function () {
            toggle.checked = false;
        };

        document.querySelectorAll('[data-admin-nav-link]').forEach(function (link) {
            link.addEventListener('click', close);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                close();
            }
        });
    })();
</script>
</body>
</html>