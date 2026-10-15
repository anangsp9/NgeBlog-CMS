<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $authors = ArticleContentPool::commentAuthors();
        $sentences = ArticleContentPool::commentSentences();

        return [
            'article_id' => Article::factory(),
            'user_id' => null,
            'name' => $authors[random_int(0, count($authors) - 1)],
            'email' => fake()->unique()->safeEmail(),
            'content' => $sentences[random_int(0, count($sentences) - 1)],
            'status' => 'approved',
        ];
    }
}
