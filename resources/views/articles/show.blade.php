@extends('layouts.app')

@section('title', $article->title)
@section('description', \Illuminate\Support\Str::limit($article->excerpt, 155))
@section('canonical', route('articles.show', $article))
@section('og_type', 'article')
@section('image', $article->coverUrl() ?? '')

@section('content')
<article class="mx-auto max-w-3xl">
    <header class="border-b border-gray-200 pb-8">
        @if ($article->category)
            <a
                href="{{ route('categories.show', $article->category) }}"
                class="text-xs font-semibold uppercase tracking-wide text-gray-500 hover:text-gray-900"
            >
                {{ $article->category->name }}
            </a>
        @endif

        <h1 class="mt-3 text-3xl font-bold leading-tight text-gray-900 sm:text-4xl">
            {{ $article->title }}
        </h1>

        <p class="mt-4 flex flex-wrap items-center gap-2 text-sm text-gray-500">
            <span>{{ $article->user?->name }}</span>
            <span aria-hidden="true">&middot;</span>
            <time datetime="{{ $article->published_at?->toDateString() }}">
                {{ \App\Support\Tanggal::panjang($article->published_at) }}
            </time>
        </p>
    </header>

    @if ($article->cover_image)
        <img
            src="{{ $article->coverUrl() }}"
            alt="{{ $article->title }}"
            width="1200"
            height="675"
            fetchpriority="high"
            class="mt-8 aspect-[16/9] w-full rounded-lg object-cover"
        >
    @endif

    @if ($article->excerpt)
        <p class="mt-8 border-l-4 border-gray-900 pl-4 text-lg leading-relaxed text-gray-700">
            {{ $article->excerpt }}
        </p>
    @endif

    <div class="prose prose-gray mt-8 max-w-none text-base leading-relaxed">
        @foreach (preg_split('/\n\s*\n/', trim($article->content)) as $paragraph)
            @if (trim($paragraph) !== '')
                <p>{{ trim($paragraph) }}</p>
            @endif
        @endforeach
    </div>
</article>

@if ($related->isNotEmpty())
    <section class="mx-auto mt-16 max-w-6xl border-t border-gray-200 pt-10">
        <h2 class="text-2xl font-bold text-gray-900">Artikel Terkait</h2>
        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($related as $item)
                <x-article-card :article="$item" />
            @endforeach
        </div>
    </section>
@endif

<section id="comments" class="mx-auto mt-16 max-w-3xl border-t border-gray-200 pt-10">
    <h2 class="text-2xl font-bold text-gray-900">
        Komentar
        <span class="text-base font-normal text-gray-500">({{ $comments->total() }})</span>
    </h2>

    @if ($comments->isEmpty())
        <p class="mt-4 text-sm text-gray-500">Belum ada komentar yang ditampilkan.</p>
    @else
        <ul class="mt-6 space-y-6">
            @foreach ($comments as $comment)
                <li class="rounded-lg border border-gray-200 bg-white p-5">
                    <div class="flex items-baseline justify-between gap-4">
                        <p class="font-semibold text-gray-900">{{ $comment->name }}</p>
                        <time class="text-xs text-gray-500" datetime="{{ $comment->created_at->toDateString() }}">
                            {{ \App\Support\Tanggal::pendek($comment->created_at, denganWaktu: true) }}
                        </time>
                    </div>
                    <p class="mt-2 text-sm leading-relaxed text-gray-700">{{ $comment->content }}</p>
                </li>
            @endforeach
        </ul>

        <div class="mt-8">
            {{ $comments->links() }}
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('articles.comments.store', $article) }}"
        class="mt-10 rounded-lg border border-gray-200 bg-white p-6"
    >
        @csrf

        <h3 class="font-semibold text-gray-900">Tulis Komentar</h3>
        <p class="mt-1 text-xs text-gray-500">Komentar akan muncul setelah disetujui admin.</p>

        @guest
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
                    >
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
                    >
                </div>
            </div>
        @else
            <p class="mt-5 rounded-md bg-gray-50 px-4 py-3 text-sm text-gray-600">
                Kirim sebagai <span class="font-semibold text-gray-900">{{ auth()->user()->email }}</span>
            </p>
        @endguest

        <div class="mt-4">
            <label for="content" class="block text-sm font-medium text-gray-700">Komentar</label>
            <textarea
                id="content"
                name="content"
                rows="4"
                required
                maxlength="2000"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-900 focus:outline-none focus:ring-0"
            >{{ old('content') }}</textarea>
        </div>

        <button
            type="submit"
            class="mt-4 rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
        >
            Kirim Komentar
        </button>
    </form>
</section>
@endsection
