<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_menyebutkan_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('User-agent: *');
        $response->assertSee('Disallow: /admin');
        $response->assertSee('Sitemap: '.route('sitemap'), false);
    }

    public function test_sitemap_valid_dan_hanya_memuat_konten_terindeks(): void
    {
        $category = Category::factory()->create();
        $published = Article::factory()->for($category)->create();
        $draft = Article::factory()->draft()->create();
        $emptyCategory = Category::factory()->create();

        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $xml = simplexml_load_string($response->getContent());

        $this->assertNotFalse($xml, 'Sitemap harus berupa XML yang valid.');
        $this->assertSame('urlset', $xml->getName());

        $locations = array_map(
            fn ($url) => (string) $url->loc,
            iterator_to_array($xml->url, false)
        );

        $this->assertContains(route('home'), $locations);
        $this->assertContains(route('articles.index'), $locations);
        $this->assertContains(route('categories.show', $category), $locations);
        $this->assertContains(route('articles.show', $published), $locations);
        $this->assertNotContains(route('articles.show', $draft), $locations);
        $this->assertNotContains(route('categories.show', $emptyCategory), $locations);
    }

    public function test_sitemap_dimulai_dengan_deklarasi_xml(): void
    {
        $content = $this->get(route('sitemap'))->getContent();

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $content);
    }

    public function test_feed_rss_valid(): void
    {
        $article = Article::factory()->create(['title' => 'Artikel untuk Feed']);

        $response = $this->get(route('feed'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');

        $content = $response->getContent();

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $content);

        $xml = simplexml_load_string($content);

        $this->assertNotFalse($xml, 'Feed harus berupa XML yang valid.');
        $this->assertSame('rss', $xml->getName());
        $this->assertSame(route('articles.show', $article), (string) $xml->channel->item[0]->link);
        $this->assertSame('Artikel untuk Feed', (string) $xml->channel->item[0]->title);
    }

    public function test_halaman_artikel_memiliki_meta_tag_seo_lengkap(): void
    {
        $article = Article::factory()->create(['title' => 'Panduan SEO']);

        $response = $this->get(route('articles.show', $article));

        $response->assertOk();
        $response->assertSee('<title>Panduan SEO &mdash; '.config('app.name').'</title>', false);
        $response->assertSee('<link rel="canonical" href="'.route('articles.show', $article).'">', false);
        $response->assertSee('<meta property="og:type" content="article">', false);
        $response->assertSee('<meta property="og:url" content="'.route('articles.show', $article).'">', false);
        $response->assertSee('<meta property="og:title" content="Panduan SEO">', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
    }

    public function test_og_image_memakai_cover_artikel_bila_tersedia(): void
    {
        $article = Article::factory()->create(['cover_image' => 'covers/contoh.jpg']);

        $expected = url('/storage/covers/contoh.jpg');

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('<meta property="og:image" content="'.$expected.'">', false);
    }

    public function test_og_image_memakai_gambar_bawaan_bila_tanpa_cover(): void
    {
        $article = Article::factory()->create();

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('<meta property="og:image" content="'.url('images/og-default.png').'">', false);
    }

    public function test_cover_url_mengembalikan_null_bila_tanpa_gambar(): void
    {
        $this->assertNull(Article::factory()->create()->coverUrl());
    }

    public function test_cover_url_mengembalikan_url_absolut(): void
    {
        $article = Article::factory()->create(['cover_image' => 'covers/contoh.jpg']);

        $this->assertSame(url('/storage/covers/contoh.jpg'), $article->coverUrl());
    }

    public function test_cover_url_mengembalikan_url_eksternal_apa_adanya(): void
    {
        $url = 'https://picsum.photos/seed/seo/1200/675';
        $article = Article::factory()->create(['cover_image' => $url]);

        $this->assertSame($url, $article->coverUrl());

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('<meta property="og:image" content="'.$url.'">', false);
    }

    public function test_halaman_publik_menyertakan_tag_dasar(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertSee('<link rel="alternate" type="application/rss+xml"', false)
            ->assertSee('<link rel="icon"', false);
    }

    public function test_halaman_admin_tidak_diindeks_mesin_pencari(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_halaman_404_merender_tampilan_kustom(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman Tidak Ditemukan');
    }
}
