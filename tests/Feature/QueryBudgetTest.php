<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Jaring pengaman untuk regresi performa.
 *
 * Test N+1 yang berguna bukan membandingkan jumlah query terhadap angka tetap
 * (rentan berubah saat fitur ditambah), melainkan memeriksa apakah jumlah query
 * ikut MELONCAK seiring bertambahnya baris. Bila ada query per baris, pola ini
 * akan ketahuan otomatis.
 */
class QueryBudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_daftar_artikel_admin_tidak_berlaku_n1(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Article::factory()->count(3)->create();
        $sedikit = $this->countQueries(fn () => $this->actingAs($admin)->get(route('admin.articles.index'))->assertOk());

        Article::factory()->count(12)->create();
        $banyak = $this->countQueries(fn () => $this->actingAs($admin)->get(route('admin.articles.index'))->assertOk());

        $this->assertSame(
            $sedikit,
            $banyak,
            "Jumlah query naik dari {$sedikit} ke {$banyak} saat baris bertambah - kemungkinan ada N+1."
        );
    }

    public function test_daftar_komentar_admin_tidak_berlaku_n1(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Article::factory()->count(2)->create();
        $sedikit = $this->countQueries(fn () => $this->actingAs($admin)->get(route('admin.comments.index'))->assertOk());

        Article::factory()->count(10)->create();
        $banyak = $this->countQueries(fn () => $this->actingAs($admin)->get(route('admin.comments.index'))->assertOk());

        $this->assertSame(
            $sedikit,
            $banyak,
            "Jumlah query naik dari {$sedikit} ke {$banyak} saat baris bertambah - kemungkinan ada N+1."
        );
    }

    public function test_daftar_user_admin_tidak_berlaku_n1(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(3)->create(['role' => 'visitor']);
        $sedikit = $this->countQueries(fn () => $this->actingAs($admin)->get(route('admin.users.index'))->assertOk());

        User::factory()->count(15)->create(['role' => 'visitor']);
        $banyak = $this->countQueries(fn () => $this->actingAs($admin)->get(route('admin.users.index'))->assertOk());

        $this->assertSame($sedikit, $banyak, 'Jumlah query naik seiring bertambahnya user - kemungkinan N+1.');
    }

    public function test_dashboard_admin_menghitung_status_dalam_satuan_query(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Article::factory()->count(5)->create();

        $queries = $this->countQueries(
            fn () => $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()
        );

        // Satu GROUP BY per tabel, ditambah penghitungan kategori & user.
        $this->assertLessThanOrEqual(
            6,
            $queries,
            "Dashboard memakai {$queries} query; seharusnya cukup dengan 4 (2 GROUP BY + kategori + user)."
        );
    }

    public function test_beranda_dalam_batas_query_yang_wajar(): void
    {
        Article::factory()->count(9)->create();

        $queries = $this->countQueries(fn () => $this->get(route('home'))->assertOk());

        $this->assertLessThanOrEqual(
            8,
            $queries,
            "Beranda memakai {$queries} query; seharusnya cukup dengan 3 query utama + eager loading."
        );
    }

    public function test_halaman_detail_artikel_dalam_batas_query(): void
    {
        $article = Article::factory()->create();
        $article->comments()->saveMany(
            Comment::factory()->count(10)->make()
        );

        $queries = $this->countQueries(
            fn () => $this->get(route('articles.show', $article))->assertOk()
        );

        $this->assertLessThanOrEqual(
            8,
            $queries,
            "Detail artikel memakai {$queries} query; seharusnya tetap konstan tanpa terpengaruh jumlah komentar."
        );
    }

    public function test_kategori_dalam_batas_query(): void
    {
        $category = Category::factory()->create();
        Article::factory()->count(6)->for($category)->create();

        $queries = $this->countQueries(
            fn () => $this->get(route('categories.show', $category))->assertOk()
        );

        $this->assertLessThanOrEqual(8, $queries, "Halaman kategori memakai {$queries} query.");
    }

    /**
     * Jalankan request lalu hitung query yang benar-benar dieksekusi ke database.
     */
    private function countQueries(callable $request): int
    {
        DB::enableQueryLog();
        DB::flushQueryLog();

        $request();

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }
}
