<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengunjung_tanpa_login_diarahkan_ke_halaman_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_pengunjung_dengan_peran_biasa_menerima_403(): void
    {
        $visitor = User::factory()->create(['role' => 'visitor']);

        $this->actingAs($visitor)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_setiap_halaman_admin_menolak_pengunjung_biasa(): void
    {
        $visitor = User::factory()->create(['role' => 'visitor']);
        $category = Category::factory()->create();
        $article = Article::factory()->create();

        $this->actingAs($visitor)->get(route('admin.articles.index'))->assertForbidden();
        $this->actingAs($visitor)->get(route('admin.articles.create'))->assertForbidden();
        $this->actingAs($visitor)->get(route('admin.articles.edit', $article))->assertForbidden();
        $this->actingAs($visitor)->get(route('admin.categories.index'))->assertForbidden();
        $this->actingAs($visitor)->get(route('admin.categories.edit', $category))->assertForbidden();
        $this->actingAs($visitor)->get(route('admin.comments.index'))->assertForbidden();
        $this->actingAs($visitor)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_permintaan_tulis_menolak_pengunjung_biasa(): void
    {
        $visitor = User::factory()->create(['role' => 'visitor']);
        $article = Article::factory()->create();

        $this->actingAs($visitor)
            ->delete(route('admin.articles.destroy', $article))
            ->assertForbidden();

        $this->assertDatabaseHas('articles', ['id' => $article->id]);
    }

    public function test_admin_boleh_mengakses_semua_halaman_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        $article = Article::factory()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.articles.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.articles.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.articles.edit', $article))->assertOk();
        $this->actingAs($admin)->get(route('admin.categories.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.categories.edit', $category))->assertOk();
        $this->actingAs($admin)->get(route('admin.comments.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
    }

    public function test_halaman_403_menampilkan_tampilan_kustom(): void
    {
        $visitor = User::factory()->create(['role' => 'visitor']);

        $this->actingAs($visitor)
            ->get(route('admin.dashboard'))
            ->assertForbidden()
            ->assertSee('Akses Ditolak');
    }
}
