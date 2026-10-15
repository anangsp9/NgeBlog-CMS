<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_utama_dapat_diakses_oleh_pengunjung(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('NgeBlog!');
    }

    public function test_halaman_utama_menampilkan_artikel_terbaru(): void
    {
        $published = Article::factory()->create([
            'title' => 'Artikel Terbaru PUBLISHEDXYZ',
            'published_at' => now()->subDay(),
        ]);

        Article::factory()->draft()->create(['title' => 'Artikel Draft XYZ']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee('Artikel Draft XYZ');
    }

    public function test_pengunjung_dapat_membaca_artikel_yang_dipublikasikan(): void
    {
        $article = Article::factory()->create([
            'title' => 'Panduan Belajar Laravel',
            'excerpt' => 'Ringkasan singkat artikel.',
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('Panduan Belajar Laravel')
            ->assertSee('Ringkasan singkat artikel.');
    }

    public function test_artikel_berstatus_draft_tidak_dapat_diakses(): void
    {
        $article = Article::factory()->draft()->create();

        $this->get(route('articles.show', $article))->assertNotFound();
    }

    public function test_daftar_artikel_hanya_menampilkan_yang_dipublikasikan(): void
    {
        $published = Article::factory()->create(['title' => 'Artikel Terbit ABC123']);
        $draft = Article::factory()->draft()->create(['title' => 'Artikel Draf ABC123']);

        $this->get(route('articles.index'))
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee($draft->title);
    }

    public function test_pengunjung_dapat_mencari_artikel(): void
    {
        Article::factory()->create([
            'title' => 'Belajar Docker Compose',
            'excerpt' => 'Konten tentang orkestrasi container.',
            'content' => 'Penjelasan lengkap docker compose.',
        ]);

        $this->get(route('articles.index', ['search' => 'Docker Compose']))
            ->assertOk()
            ->assertSee('Belajar Docker Compose');
    }

    public function test_pencarian_dengan_karakter_wildcard_tidak_meledak(): void
    {
        Article::factory()->create(['title' => 'Judul Biasa Saja']);

        $this->get(route('articles.index', ['search' => '%_']))
            ->assertOk()
            ->assertDontSee('Judul Biasa Saja');
    }

    public function test_pengunjung_dapat_memfilter_artikel_berdasarkan_kategori(): void
    {
        $category = Category::factory()->create(['name' => 'Teknologi']);

        $inCategory = Article::factory()->for($category)->create(['title' => 'Artikel Teknologi']);
        $outside = Article::factory()->create(['title' => 'Artikel Luar Kategori']);

        $this->get(route('articles.index', ['category' => $category->slug]))
            ->assertOk()
            ->assertSee($inCategory->title)
            ->assertDontSee($outside->title);
    }

    public function test_urutan_artikel_dapat_diubah(): void
    {
        $terlama = Article::factory()->create([
            'title' => 'Artikel Paling Lama',
            'published_at' => now()->subYear(),
        ]);

        $terbaru = Article::factory()->create([
            'title' => 'Artikel Paling Baru',
            'published_at' => now(),
        ]);

        $response = $this->get(route('articles.index', ['sort' => 'oldest']));

        $response->assertOk();
        $response->assertSeeInOrder([$terlama->title, $terbaru->title]);

        $response = $this->get(route('articles.index', ['sort' => 'newest']));

        $response->assertSeeInOrder([$terbaru->title, $terlama->title]);
    }

    public function test_halaman_kategori_menampilkan_artikel_milik_kategori_tersebut(): void
    {
        $category = Category::factory()->create();
        $article = Article::factory()->for($category)->create();

        $this->get(route('categories.show', $category))
            ->assertOk()
            ->assertSee($article->title);
    }

    public function test_artikel_menampilkan_hanya_komentar_yang_disetujui(): void
    {
        $article = Article::factory()->create();

        // Lewat factory: 'status' sengaja di luar $fillable Comment, sehingga
        // comments()->create(['status' => ...]) akan membuang statusnya diam-diam.
        $approved = Comment::factory()->create([
            'article_id' => $article->id,
            'content' => 'Komentar yang sudah disetujui.',
            'status' => 'approved',
        ]);

        Comment::factory()->create([
            'article_id' => $article->id,
            'content' => 'Komentar yang masih pending.',
            'status' => 'pending',
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee($approved->content)
            ->assertDontSee('Komentar yang masih pending.');
    }

    public function test_admin_melihat_link_dashboard_pada_navbar(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('home'))
            ->assertOk()
            ->assertSee(route('admin.dashboard'));
    }
}
