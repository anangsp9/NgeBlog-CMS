<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $articleCounts = $this->countByStatus(new Article);
        $commentCounts = $this->countByStatus(new Comment);

        return view('admin.dashboard', [
            'totalArticles' => $articleCounts->sum(),
            'publishedArticles' => $articleCounts->get('published', 0),
            'draftArticles' => $articleCounts->get('draft', 0),
            'totalComments' => $commentCounts->sum(),
            'pendingComments' => $commentCounts->get('pending', 0),
            'approvedComments' => $commentCounts->get('approved', 0),
            'rejectedComments' => $commentCounts->get('rejected', 0),
            'totalCategories' => Category::count(),
            'totalUsers' => User::count(),
        ]);
    }

    /**
     * Satu query GROUP BY per tabel, pengganti beberapa count() terpisah.
     *
     * @return Collection<int, int>
     */
    private function countByStatus(Article|Comment $model): Collection
    {
        return $model->newQuery()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total);
    }
}
