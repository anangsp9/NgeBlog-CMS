<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengunjung_dapat_mengirim_komentar_dan_disimpan_sebagai_pending(): void
    {
        $article = Article::factory()->create();

        $this->post(route('articles.comments.store', $article), [
            'name' => 'Pengunjung Baru',
            'email' => 'pengunjung@example.com',
            'content' => 'Artikelnya sangat membantu.',
        ])->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'article_id' => $article->id,
            'user_id' => null,
            'name' => 'Pengunjung Baru',
            'status' => 'pending',
        ]);
    }

    public function test_komentar_pengunjung_tertunda_tidak_ditampilkan(): void
    {
        $article = Article::factory()->create();

        $this->post(route('articles.comments.store', $article), [
            'name' => 'Pengunjung Baru',
            'email' => 'pengunjung@example.com',
            'content' => 'Komentar yang masih pending.',
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertDontSee('Komentar yang masih pending.');
    }

    public function test_pengunjung_wajib_mengisi_nama_dan_email(): void
    {
        $article = Article::factory()->create();

        $this->from(route('articles.show', $article))
            ->post(route('articles.comments.store', $article), ['content' => 'Tanpa identitas.'])
            ->assertRedirect(route('articles.show', $article))
            ->assertSessionHasErrors(['name', 'email']);
    }

    public function test_email_tidak_valid_ditolak(): void
    {
        $article = Article::factory()->create();

        $this->from(route('articles.show', $article))
            ->post(route('articles.comments.store', $article), [
                'name' => 'Pengunjung',
                'email' => 'bukan-email',
                'content' => 'Komentar yang valid.',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_komentar_terlalu_pendek_ditolak(): void
    {
        $article = Article::factory()->create();

        $this->from(route('articles.show', $article))
            ->post(route('articles.comments.store', $article), [
                'name' => 'Pengunjung',
                'email' => 'pengunjung@example.com',
                'content' => 'ab',
            ])
            ->assertSessionHasErrors('content');
    }

    public function test_user_terdaftar_menggunakan_identitas_dari_akun(): void
    {
        $article = Article::factory()->create();
        $user = User::factory()->create(['name' => 'Siti Rahayu', 'email' => 'siti@example.com']);

        $this->actingAs($user)->post(route('articles.comments.store', $article), [
            'content' => 'Komentar dari user terdaftar.',
        ])->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'article_id' => $article->id,
            'user_id' => $user->id,
            'name' => 'Siti Rahayu',
            'email' => 'siti@example.com',
            'status' => 'pending',
        ]);
    }

    public function test_komentar_tidak_bisa_ditulis_pada_artikel_draft(): void
    {
        $article = Article::factory()->draft()->create();

        $this->post(route('articles.comments.store', $article), [
            'name' => 'Pengunjung',
            'email' => 'pengunjung@example.com',
            'content' => 'Komentar pada artikel draft.',
        ])->assertNotFound();

        $this->assertDatabaseMissing('comments', ['article_id' => $article->id]);
    }

    public function test_admin_dapat_menyetujui_komentar_hingga_tampil_di_halaman_artikel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $article = Article::factory()->create();

        $comment = Comment::factory()->create([
            'article_id' => $article->id,
            'content' => 'Komentar yang akan disetujui.',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.comments.update', $comment), ['status' => 'approved'])
            ->assertRedirect();

        $this->assertSame('approved', $comment->fresh()->status);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('Komentar yang akan disetujui.');
    }

    public function test_admin_dapat_menolak_komentar_hingga_tidak_tampil(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $article = Article::factory()->create();

        $comment = Comment::factory()->create([
            'article_id' => $article->id,
            'content' => 'Komentar yang akan ditolak.',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.comments.update', $comment), ['status' => 'rejected'])
            ->assertRedirect();

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertDontSee('Komentar yang akan ditolak.');
    }

    public function test_status_komentar_tidak_valid_ditolak(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $comment = Comment::factory()->create(['status' => 'pending']);

        $this->actingAs($admin)
            ->from(route('admin.comments.index'))
            ->put(route('admin.comments.update', $comment), ['status' => 'hacked'])
            ->assertSessionHasErrors('status');
    }

    public function test_admin_dapat_menghapus_komentar(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $comment = Comment::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.comments.destroy', $comment))
            ->assertRedirect();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_daftar_moderasi_menampilkan_hanya_komentar_pending(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Comment::factory()->create(['status' => 'pending', 'content' => 'Butuh Moderasi']);
        Comment::factory()->create(['status' => 'approved', 'content' => 'Sudah Disetujui']);

        $this->actingAs($admin)
            ->get(route('admin.comments.index'))
            ->assertOk()
            ->assertSee('Butuh Moderasi')
            ->assertDontSee('Sudah Disetujui');
    }

    public function test_pengiriman_komentar_dibatasi_untuk_mencegah_spam(): void
    {
        $article = Article::factory()->create();

        $payload = [
            'name' => 'Spammer',
            'email' => 'spam@example.com',
            'content' => 'Komentar spam.',
        ];

        foreach (range(1, 5) as $ignored) {
            $this->post(route('articles.comments.store', $article), $payload);
        }

        $this->post(route('articles.comments.store', $article), $payload)
            ->assertStatus(429);

        $this->assertSame(5, Comment::where('article_id', $article->id)->count());
    }
}
