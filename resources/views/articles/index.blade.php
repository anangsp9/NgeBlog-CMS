@extends('layouts.app')

@section('title', 'Semua Artikel')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Semua Artikel</h1>
        <p class="mt-1 text-sm text-gray-500">
            {{ $articles->total() }} artikel ditemukan
            @if ($search)
                untuk &ldquo;{{ $search }}&rdquo;
            @endif
        </p>
    </div>

    <form method="GET" action="{{ route('articles.index') }}" class="grid gap-4 rounded-lg border border-gray-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="lg:col-span-2">
            <label for="search" class="block text-sm font-medium text-gray-700">Cari</label>
            <input
                id="search"
                name="search"
                type="search"
                value="{{ $search }}"
                placeholder="Judul atau isi artikel..."
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
            >
        </div>

        <div>
            <label for="category" class="block text-sm font-medium text-gray-700">Kategori</label>
            <select
                id="category"
                name="category"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
            >
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected($activeCategory === $category->slug)>
                        {{ $category->name }} ({{ $category->published_count }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="sort" class="block text-sm font-medium text-gray-700">Urutkan</label>
            <select
                id="sort"
                name="sort"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
            >
                <option value="newest" @selected($sort === 'newest')>Terbaru</option>
                <option value="oldest" @selected($sort === 'oldest')>Terlama</option>
            </select>
        </div>

        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4">
            <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                Terapkan
            </button>
            @if ($search || $activeCategory || $sort === 'oldest')
                <a href="{{ route('articles.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:border-gray-900">
                    Reset
                </a>
            @endif
        </div>
    </form>

    @if ($articles->isEmpty())
        <div class="rounded-lg border border-gray-200 bg-white p-10 text-center">
            <p class="text-sm text-gray-500">Tidak ada artikel yang cocok dengan filter ini.</p>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($articles as $article)
                <x-article-card :article="$article" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $articles->links() }}
        </div>
    @endif
</div>
@endsection
