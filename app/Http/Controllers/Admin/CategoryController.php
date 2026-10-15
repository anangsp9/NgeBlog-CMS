<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('articles')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create', ['category' => new Category]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $name = $request->validated()['name'];

        Category::create([
            'name' => $name,
            'slug' => UniqueSlug::for(new Category, $name),
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', "Kategori \"{$name}\" berhasil dibuat.");
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $name = $request->validated()['name'];

        $category->update([
            'name' => $name,
            'slug' => $category->name === $name
                ? $category->slug
                : UniqueSlug::for($category, $name, $category->getKey()),
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', "Kategori \"{$name}\" berhasil diperbarui.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->articles()->exists()) {
            return back()->with('error', "Kategori \"{$category->name}\" masih memiliki artikel dan tidak bisa dihapus.");
        }

        $name = $category->name;
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('status', "Kategori \"{$name}\" berhasil dihapus.");
    }
}
