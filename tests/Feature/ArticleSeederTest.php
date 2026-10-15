<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Support\CoverImage;
use Database\Seeders\ArticleSeeder;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_setiap_artikel_hasil_seed_mendapat_cover_link(): void
    {
        $this->seed(CategorySeeder::class);
        $this->seed(ArticleSeeder::class);

        $this->assertSame(50, Article::count());
        $this->assertSame(0, Article::whereNull('cover_image')->count());
        $this->assertSame(0, Article::where('cover_image', '')->count());

        $article = Article::query()->orderBy('id')->firstOrFail();

        // Seed memakai slug, sehingga satu artikel selalu menunjuk gambar yang
        // sama, dan backfill data lama menghasilkan URL identik dengan seeder.
        $this->assertSame(CoverImage::randomUrl($article->slug), $article->cover_image);
        $this->assertSame($article->cover_image, $article->coverUrl());
    }
}
