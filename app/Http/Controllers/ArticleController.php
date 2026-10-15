<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $categorySlug = $request->string('category')->trim()->value();
        $sort = $request->string('sort')->toString() === 'oldest' ? 'oldest' : 'newest';

        $articles = Article::published()
            ->with(['category', 'user'])
            ->search($search ?: null)
            ->when($categorySlug, fn ($query) => $query->whereHas(
                'category',
                fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug)
            ))
            ->when($sort === 'oldest',
                fn ($query) => $query->oldest('published_at'),
                fn ($query) => $query->latest('published_at')
            )
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->withCount(['articles as published_count' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->get();

        return view('articles.index', [
            'articles' => $articles,
            'categories' => $categories,
            'search' => $search,
            'activeCategory' => $categorySlug,
            'sort' => $sort,
        ]);
    }

    public function show(Article $article): View
    {
        abort_if($article->status !== 'published', 404);

        $article->load(['category', 'user']);

        $related = Article::published()
            ->with('category')
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->getKey())
            ->latest('published_at')
            ->take(4)
            ->get();

        // Dipaginasi: artikel yang viral bisa punya ribuan komentar. Versi sebelumnya
        // memuat seluruh baris beserta kolom content (longText) ke memori dalam sekali jalan.
        $comments = $article->approvedComments()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('articles.show', compact('article', 'related', 'comments'));
    }
}
