<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Support\UniqueSlug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniqueSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_dibuat_dari_judul(): void
    {
        $slug = UniqueSlug::for(new Article, 'Belajar Laravel & Docker');

        $this->assertSame('belajar-laravel-docker', $slug);
    }

    public function test_slug_kosong_di_ganti_dengan_nilai_bawaan(): void
    {
        $this->assertSame('item', UniqueSlug::for(new Article, '!!!'));
    }

    public function test_slug_kolisi_ditambahkan_urutan_berikutnya(): void
    {
        Article::factory()->create(['slug' => 'belajar-laravel']);

        $this->assertSame('belajar-laravel-2', UniqueSlug::for(new Article, 'Belajar Laravel'));
    }

    public function test_slug_mempertahankan_nilai_saat_mengedit_diri_sendiri(): void
    {
        $article = Article::factory()->create(['slug' => 'belajar-laravel']);

        $this->assertSame('belajar-laravel', UniqueSlug::for($article, 'Belajar Laravel', $article->id));
    }

    public function test_setiap_record_menjaga_slug_miliknya_saat_diedit(): void
    {
        $first = Article::factory()->create(['slug' => 'belajar-laravel']);
        $second = Article::factory()->create(['slug' => 'belajar-laravel-2']);

        $this->assertSame('belajar-laravel', UniqueSlug::for($first, 'Belajar Laravel', $first->id));
        $this->assertSame('belajar-laravel-2', UniqueSlug::for($second, 'Belajar Laravel', $second->id));
    }

    public function test_record_baru_mendapat_slug_berikutnya(): void
    {
        Article::factory()->create(['slug' => 'belajar-laravel']);
        Article::factory()->create(['slug' => 'belajar-laravel-2']);

        $this->assertSame('belajar-laravel-3', UniqueSlug::for(new Article, 'Belajar Laravel'));
    }
}
