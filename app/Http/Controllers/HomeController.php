<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featured = Article::published()
            ->with(['category', 'user'])
            ->latest('published_at')
            ->first();

        $latest = Article::published()
            ->with(['category', 'user'])
            ->when($featured, fn ($query) => $query->where('id', '!=', $featured->getKey()))
            ->latest('published_at')
            ->take(9)
            ->get();

        $popularCategories = Category::query()
            ->whereHas('articles', fn ($query) => $query->published())
            ->withCount(['articles as published_count' => fn ($query) => $query->published()])
            ->orderByDesc('published_count')
            ->orderBy('name')
            ->take(6)
            ->get();

        return view('home', compact('featured', 'latest', 'popularCategories'));
    }
}
