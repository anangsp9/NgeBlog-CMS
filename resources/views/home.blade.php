@extends('layouts.app')

@section('title', config('app.name'))

@section('content')
<div class="space-y-12">
    @if ($featured)
        <section>
            <h2 class="sr-only">Artikel Pilihan</h2>
            <a
                href="{{ route('articles.show', $featured) }}"
                class="group grid gap-6 overflow-hidden rounded-lg border border-gray-200 bg-white transition hover:shadow-md lg:grid-cols-2"
            >
                <div class="flex aspect-[16/10] items-center justify-center bg-gray-100">
                    @if ($featured->cover_image)
                        <img
                            src="{{ $featured->coverUrl() }}"
                            alt="{{ $featured->title }}"
                            class="h-full w-full object-cover"
                        >
                    @else
                        <span class="text-sm font-medium uppercase tracking-wide text-gray-400">
                            {{ $featured->category?->name }}
                        </span>
                    @endif
                </div>

                <div class="flex flex-col justify-center p-6 lg:p-10">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Artikel Pilihan
                    </p>
                    <h1 class="mt-3 text-3xl font-bold leading-tight text-gray-900 group-hover:underline">
                        {{ $featured->title }}
                    </h1>
                    <p class="mt-4 text-base leading-relaxed text-gray-600">
                        {{ $featured->excerpt }}
                    </p>
                    <p class="mt-6 text-xs text-gray-500">
                        {{ $featured->user?->name }}
                        <span aria-hidden="true">&middot;</span>
                        {{ $featured->category?->name }}
                        <span aria-hidden="true">&middot;</span>
                        {{ \App\Support\Tanggal::pendek($featured->published_at) }}
                    </p>
                </div>
            </a>
        </section>
    @endif

    @if ($popularCategories->isNotEmpty())
        <section>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Kategori Populer</h2>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($popularCategories as $category)
                    <a
                        href="{{ route('categories.show', $category) }}"
                        class="rounded-full border border-gray-300 px-4 py-1.5 text-sm text-gray-700 transition hover:border-gray-900 hover:text-gray-900"
                    >
                        {{ $category->name }}
                        <span class="ml-1 text-xs text-gray-400">{{ $category->published_count }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section>
        <div class="flex items-baseline justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Artikel Terbaru</h2>
            <a href="{{ route('articles.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                Lihat semua &rarr;
            </a>
        </div>

        @if ($latest->isEmpty())
            <p class="mt-6 text-sm text-gray-500">Belum ada artikel yang dipublikasikan.</p>
        @else
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($latest as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
