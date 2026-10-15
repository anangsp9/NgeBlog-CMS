<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminArticleManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_dapat_membuat_artikel_baru(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Belajar Docker Compose',
            'category_id' => $category->id,
            'excerpt' => 'Ringkasan artikel.',
            'content' => 'Paragraf pertama artikel.',
            'status' => 'published',
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1200, 675),
        ]);

        $response->assertRedirect(route('admin.articles.index'));

        $article = Article::firstWhere('title', 'Belajar Docker Compose');

        $this->assertNotNull($article);
        $this->assertSame('belajar-docker-compose', $article->slug);
        $this->assertSame($admin->id, $article->user_id);
        $this->assertSame('published', $article->status);
        $this->assertNotNull($article->published_at);

        Storage::disk('public')->assertExists($article->cover_image);
    }

    public function test_slug_dibuat_unik_saat_judul_sama(): void
    {
        $admin = $this->admin();
        $category = Category::factory()->create();

        $payload = [
            'title' => 'Judul Yang Sama',
            'category_id' => $category->id,
            'excerpt' => 'Ringkasan.',
            'content' => 'Isi artikel.',
            'status' => 'draft',
        ];

        $this->actingAs($admin)->post(route('admin.articles.store'), $payload);
        $this->actingAs($admin)->post(route('admin.articles.store'), $payload);

        $this->assertSame(2, Article::where('title', 'Judul Yang Sama')->count());
        $this->assertSame(2, Article::pluck('slug')->filter()->unique()->count());
    }

    public function test_draft_tidak_mengisi_published_at(): void
    {
        $admin = $this->admin();
        $category = Category::factory()->create();

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Artikel Draft Baru',
            'category_id' => $category->id,
            'excerpt' => 'Ringkasan.',
            'content' => 'Isi artikel.',
            'status' => 'draft',
        ]);

        $article = Article::firstWhere('title', 'Artikel Draft Baru');

        $this->assertNotNull($article);
        $this->assertNull($article->published_at);
    }

    public function test_validasi_menolak_judul_kosong(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.articles.create'))
            ->post(route('admin.articles.store'), [
                'title' => '',
                'category_id' => Category::factory()->create()->id,
                'excerpt' => 'Ringkasan.',
                'content' => 'Isi artikel.',
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.articles.create'))
            ->assertSessionHasErrors('title');
    }

    public function test_validasi_menolak_kategori_yang_tidak_ada(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.articles.create'))
            ->post(route('admin.articles.store'), [
                'title' => 'Artikel Tanpa Kategori',
                'category_id' => 9999,
                'excerpt' => 'Ringkasan.',
                'content' => 'Isi artikel.',
                'status' => 'draft',
            ])
            ->assertSessionHasErrors('category_id');
    }

    public function test_validasi_menolak_cover_yang_bukan_gambar(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->from(route('admin.articles.create'))
            ->post(route('admin.articles.store'), [
                'title' => 'Artikel dengan Dokumen',
                'category_id' => Category::factory()->create()->id,
                'excerpt' => 'Ringkasan.',
                'content' => 'Isi artikel.',
                'status' => 'draft',
                'cover_image' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('cover_image');
    }

    public function test_admin_dapat_memperbarui_artikel(): void
    {
        $admin = $this->admin();
        $article = Article::factory()->for($admin)->create(['status' => 'draft']);

        $newCategory = Category::factory()->create();

        $this->actingAs($admin)->put(route('admin.articles.update', $article), [
            'title' => 'Judul Yang Diperbarui',
            'category_id' => $newCategory->id,
            'excerpt' => 'Ringkasan baru.',
            'content' => 'Isi baru.',
            'status' => 'published',
        ])->assertRedirect(route('admin.articles.index'));

        $article->refresh();

        $this->assertSame('Judul Yang Diperbarui', $article->title);
        $this->assertSame('judul-yang-diperbarui', $article->slug);
        $this->assertSame($newCategory->id, $article->category_id);
        $this->assertNotNull($article->published_at);
    }

    public function test_mengganti_cover_menghapus_file_lama(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $category = Category::factory()->create();

        $article = Article::factory()->create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'cover_image' => 'covers/lama.jpg',
        ]);

        $oldCover = $article->cover_image;
        Storage::disk('public')->put($oldCover, 'lama');

        $this->actingAs($admin)->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'category_id' => $category->id,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'status' => 'published',
            'cover_image' => UploadedFile::fake()->image('baru.jpg', 800, 450),
        ])->assertRedirect(route('admin.articles.index'));

        $article->refresh();

        $this->assertNotSame($oldCover, $article->cover_image);
        Storage::disk('public')->assertMissing($oldCover);
        Storage::disk('public')->assertExists($article->cover_image);
    }

    public function test_admin_dapat_menghapus_artikel_beserta_cover(): void
    {
        Storage::fake('public');

        $article = Article::factory()->create(['cover_image' => 'covers/gambar.jpg']);
        Storage::disk('public')->put($article->cover_image, 'gambar');

        $this->actingAs($this->admin())
            ->delete(route('admin.articles.destroy', $article))
            ->assertRedirect(route('admin.articles.index'));

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        Storage::disk('public')->assertMissing($article->cover_image);
    }

    public function test_admin_dapat_membuat_artikel_dengan_cover_dari_link(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $category = Category::factory()->create();
        $url = 'https://picsum.photos/seed/uji-cover/1200/675';

        $response = $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Artikel Dari Link',
            'category_id' => $category->id,
            'excerpt' => 'Ringkasan artikel.',
            'content' => 'Paragraf artikel.',
            'status' => 'draft',
            'cover_url' => $url,
        ]);

        $response->assertRedirect(route('admin.articles.index'));

        $article = Article::firstWhere('title', 'Artikel Dari Link');

        $this->assertNotNull($article);
        $this->assertSame($url, $article->cover_image);
        $this->assertSame($url, $article->coverUrl());
        // Gambar dibaca dari link oleh browser, jadi tidak ada berkas yang
        // ditulis ke storage.
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_cover_file_dan_link_bersamaan_ditolak(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $category = Category::factory()->create();

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Artikel Dua Cover',
            'category_id' => $category->id,
            'excerpt' => 'Ringkasan.',
            'content' => 'Isi artikel.',
            'status' => 'draft',
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1200, 675),
            'cover_url' => 'https://picsum.photos/seed/uji/1200/675',
        ])->assertSessionHasErrors('cover_url');

        $this->assertDatabaseMissing('articles', ['title' => 'Artikel Dua Cover']);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_cover_url_harus_url_http_atau_https(): void
    {
        $admin = $this->admin();
        $category = Category::factory()->create();

        $payload = [
            'title' => 'Artikel URL Rusak',
            'category_id' => $category->id,
            'excerpt' => 'Ringkasan.',
            'content' => 'Isi artikel.',
            'status' => 'draft',
        ];

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [...$payload, 'cover_url' => 'bukan-sebuah-url'])
            ->assertSessionHasErrors('cover_url');

        // Skema selain http/https tidak boleh lolos, agar src gambar tidak
        // pernah mengeksekusi skema berbahaya.
        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [...$payload, 'cover_url' => 'ftp://example.com/gambar.jpg'])
            ->assertSessionHasErrors('cover_url');

        $this->assertDatabaseMissing('articles', ['title' => 'Artikel URL Rusak']);
    }

    public function test_mengganti_cover_link_dengan_file(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $category = Category::factory()->create();
        $previousCover = 'https://picsum.photos/seed/lama/1200/675';

        $article = Article::factory()->create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'cover_image' => $previousCover,
        ]);

        $this->actingAs($admin)->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'category_id' => $category->id,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'status' => 'published',
            'cover_image' => UploadedFile::fake()->image('baru.jpg', 800, 450),
        ])->assertRedirect(route('admin.articles.index'));

        $article->refresh();

        $this->assertNotSame($previousCover, $article->cover_image);
        Storage::disk('public')->assertExists($article->cover_image);
    }

    public function test_update_tanpa_cover_baru_mempertahankan_cover_link(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $category = Category::factory()->create();
        $previousCover = 'https://picsum.photos/seed/tetap/1200/675';

        $article = Article::factory()->create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'cover_image' => $previousCover,
        ]);

        $this->actingAs($admin)->put(route('admin.articles.update', $article), [
            'title' => 'Judul Baru Tanpa Cover',
            'category_id' => $category->id,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'status' => 'published',
        ])->assertRedirect(route('admin.articles.index'));

        $article->refresh();

        $this->assertSame($previousCover, $article->cover_image);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_admin_dapat_menghapus_artikel_dengan_cover_link(): void
    {
        Storage::fake('public');

        $article = Article::factory()->create([
            'cover_image' => 'https://picsum.photos/seed/hapus/1200/675',
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.articles.destroy', $article))
            ->assertRedirect(route('admin.articles.index'));

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_menghapus_kategori_berisi_artikel_ditolak(): void
    {
        $admin = $this->admin();
        $category = Category::factory()->create();
        Article::factory()->for($category)->create();

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_daftar_artikel_admin_bisa_disaring(): void
    {
        $admin = $this->admin();

        Article::factory()->for($admin)->create(['title' => 'Sudah Terbit Unik']);
        Article::factory()->for($admin)->draft()->create(['title' => 'Masih Draf Unik']);

        $this->actingAs($admin)
            ->get(route('admin.articles.index', ['status' => 'draft']))
            ->assertOk()
            ->assertSee('Masih Draf Unik')
            ->assertDontSee('Sudah Terbit Unik');
    }

    public function test_pembuatan_mengulang_ketika_slug_bentrok(): void
    {
        $admin = $this->admin();
        $category = Category::factory()->create();

        Article::factory()->create(['title' => 'Judul Bentrok', 'slug' => 'judul-bentrok']);

        // UniqueSlug memakai pola cek-lalu-tulis, jadi dua permintaan serentak
        // dengan judul sama bisa memilih slug identik. Di sini percobaan pertama
        // dipaksa memakai slug yang sudah dipakai orang lain untuk memicu kondisi
        // yang sama secara deterministik.
        $attempts = 0;
        Article::creating(function (Article $article) use (&$attempts) {
            $attempts++;

            if ($attempts === 1) {
                $article->slug = 'judul-bentrok';
            }
        });

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Judul Bentrok',
                'category_id' => $category->id,
                'excerpt' => 'Ringkasan.',
                'content' => 'Isi artikel.',
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.articles.index'));

        $this->assertGreaterThan(1, $attempts, 'Percobaan pertama seharusnya bentrok lalu diulang.');

        $created = Article::where('title', 'Judul Bentrok')->orderByDesc('id')->first();

        $this->assertNotNull($created);
        $this->assertNotSame('judul-bentrok', $created->slug, 'Artikel tetap boleh memakai slug yang sudah diambil.');
        $this->assertSame(
            Article::where('title', 'Judul Bentrok')->count(),
            Article::where('title', 'Judul Bentrok')->pluck('slug')->unique()->count(),
            'Tidak boleh ada dua artikel dengan slug sama.'
        );
    }

    public function test_cover_lama_tidak_hilang_bila_update_gagal(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $category = Category::factory()->create();

        $article = Article::factory()->create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'cover_image' => 'covers/lama.jpg',
        ]);

        Storage::disk('public')->put('covers/lama.jpg', 'isi lama');

        // Listener global, karena route model binding membangun instance Article
        // yang berbeda dari objek yang kita punya di sini.
        Article::saving(fn () => throw new \RuntimeException('simulasi kegagalan penyimpanan'));

        $this->actingAs($admin)->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'category_id' => $category->id,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'status' => 'published',
            'cover_image' => UploadedFile::fake()->image('baru.jpg', 800, 450),
        ])->assertStatus(500);

        // Baris database tidak jadi berganti, jadi berkas lamanya harus masih ada.
        Storage::disk('public')->assertExists('covers/lama.jpg');
        $this->assertSame('covers/lama.jpg', $article->fresh()->cover_image);
    }
}
