<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Support\Xml;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    /**
     * Sitemap XML untuk seluruh halaman publik yang terindeks.
     */
    public function __invoke(): Response
    {
        // cursor() alih-alih get(): sitemap wajib memuat SEMUA artikel terindeks,
        // sehingga get() akan membuat ribuan model Eloquent hidup di memori sekaligus.
        // cursor() menahan pernyataan terbuka dan menghasilkan satu model per waktu.
        $articles = Article::query()
            ->published()
            ->select(['slug', 'updated_at'])
            ->latest('published_at')
            ->cursor();

        $categories = Category::query()
            ->has('publishedArticles')
            ->orderBy('name')
            ->get(['slug', 'updated_at']);

        $lastBuild = Carbon::parse(
            Article::query()->published()->max('updated_at') ?? now()
        )->toAtomString();

        return Xml::response(
            view('sitemap', compact('articles', 'categories', 'lastBuild'))->render()
        );
    }
}
