<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $content = ArticleContentPool::pick();

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $content['title'],
            'slug' => Str::slug($content['title']).'-'.fake()->unique()->numerify('####'),
            'excerpt' => $content['excerpt'],
            'content' => implode("\n\n", $content['paragraphs']),
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
