<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Support\Xml;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    private const LIMIT = 20;

    /**
     * RSS 2.0 feed untuk artikel terbaru yang sudah dipublikasikan.
     */
    public function __invoke(): Response
    {
        $articles = Article::query()
            ->published()
            ->with(['category', 'user'])
            ->latest('published_at')
            ->limit(self::LIMIT)
            ->get();

        return Xml::response(
            view('feed', ['articles' => $articles])->render(),
            'application/rss+xml'
        );
    }
}
