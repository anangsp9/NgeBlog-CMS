<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CommentRequest;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $status = in_array($status, ['pending', 'approved', 'rejected'], true) ? $status : 'pending';

        $comments = Comment::query()
            ->with(['article', 'user'])
            ->where('status', $status)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = Comment::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.comments.index', compact('comments', 'status', 'counts'));
    }

    public function update(CommentRequest $request, Comment $comment): RedirectResponse
    {
        $validated = $request->validated();

        $comment->status = $validated['status'];
        $comment->save();

        return back()->with('status', "Status komentar diubah menjadi {$validated['status']}.");
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('status', 'Komentar berhasil dihapus.');
    }
}
