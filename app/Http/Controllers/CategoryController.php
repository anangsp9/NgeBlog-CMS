<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Category $category): View
    {
        $articles = $category->publishedArticles()
            ->with(['category', 'user'])
            ->latest('published_at')
            ->paginate(12);

        $categories = Category::query()
            ->withCount(['articles as published_count' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->get();

        return view('categories.show', [
            'category' => $category,
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }
}
