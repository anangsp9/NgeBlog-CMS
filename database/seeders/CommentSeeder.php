<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $articles = Article::all();

        foreach ($articles as $article) {
            for ($i = 0; $i < 5; $i++) {
                $status = ['approved', 'approved', 'approved', 'pending', 'rejected'][rand(0, 4)];
                Comment::factory()->create([
                    'article_id' => $article->id,
                    'status' => $status,
                ]);
            }
        }
    }
}
