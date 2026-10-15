<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function store(CommentRequest $request, Article $article): RedirectResponse
    {
        abort_unless($article->status === 'published', 404);

        $comment = new Comment([
            'article_id' => $article->getKey(),
            ...$request->authorAttributes(),
            'content' => $request->validated('content'),
        ]);

        // 'status' di luar $fillable, jadi ditetapkan sebagai properti model.
        // Pengirim komentar tidak pernah bisa menentukan statusnya sendiri.
        $comment->status = 'pending';
        $comment->save();

        return back()->with('status', 'Komentar kamu sedang menunggu persetujuan admin.');
    }
}
