@props(['article'])

<article class="group flex h-full flex-col overflow-hidden rounded-lg border border-gray-200 bg-white transition hover:shadow-md">
    <div class="flex aspect-[16/9] items-center justify-center bg-gray-100">
        @if ($article->cover_image)
            <img
                src="{{ $article->coverUrl() }}"
                alt="{{ $article->title }}"
                width="1200"
                height="675"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover"
            >
        @else
            <span class="text-sm font-medium uppercase tracking-wide text-gray-400">
                {{ $article->category?->name }}
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-5">
        @if ($article->category)
            <a
                href="{{ route('categories.show', $article->category) }}"
                class="text-xs font-semibold uppercase tracking-wide text-gray-500 hover:text-gray-900"
            >
                {{ $article->category->name }}
            </a>
        @endif

        <h3 class="mt-2 text-lg font-semibold leading-snug text-gray-900">
            <a href="{{ route('articles.show', $article) }}" class="hover:underline">
                {{ $article->title }}
            </a>
        </h3>

        <p class="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-gray-600">
            {{ $article->excerpt }}
        </p>

        <div class="mt-4 flex items-center gap-2 text-xs text-gray-500">
            <span>{{ $article->user?->name }}</span>
            <span aria-hidden="true">&middot;</span>
            <time datetime="{{ $article->published_at?->toDateString() }}">
                {{ \App\Support\Tanggal::pendek($article->published_at) }}
            </time>
        </div>
    </div>
</article>
