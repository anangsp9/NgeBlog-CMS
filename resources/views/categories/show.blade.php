@extends('layouts.app')

@section('title', $category->name)

@section('content')
<div class="space-y-8">
    <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Kategori</p>
        <h1 class="mt-2 text-3xl font-bold text-gray-900">{{ $category->name }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $articles->total() }} artikel dipublikasikan</p>
    </div>

    <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-6">
        <a
            href="{{ route('articles.index') }}"
            class="rounded-full border border-gray-300 px-4 py-1.5 text-sm text-gray-700 transition hover:border-gray-900 hover:text-gray-900"
        >
            Semua
        </a>
        @foreach ($categories as $item)
            <a
                href="{{ route('categories.show', $item) }}"
                class="rounded-full border px-4 py-1.5 text-sm transition {{ $item->id === $category->id ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-300 text-gray-700 hover:border-gray-900 hover:text-gray-900' }}"
            >
                {{ $item->name }}
            </a>
        @endforeach
    </div>

    @if ($articles->isEmpty())
        <div class="rounded-lg border border-gray-200 bg-white p-10 text-center">
            <p class="text-sm text-gray-500">Belum ada artikel di kategori ini.</p>
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
