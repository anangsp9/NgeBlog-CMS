<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ config('app.name') }}</title>
        <link>{{ route('home') }}</link>
        <description>NgeBlog! — Nulis. Terbit. Beres.</description>
        <language>id</language>
        <lastBuildDate>{{ $articles->first()?->published_at?->toRssString() ?? now()->toRssString() }}</lastBuildDate>
        <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />

        @foreach ($articles as $article)
            <item>
                <title>{{ $article->title }}</title>
                <link>{{ route('articles.show', $article) }}</link>
                <guid isPermaLink="true">{{ route('articles.show', $article) }}</guid>
                <pubDate>{{ $article->published_at->toRssString() }}</pubDate>
                @if ($article->category)
                    <category>{{ $article->category->name }}</category>
                @endif
                @if ($article->user)
                    <author>{{ $article->user->email }}</author>
                @endif
                <description>{{ $article->excerpt }}</description>
            </item>
        @endforeach
    </channel>
</rss>