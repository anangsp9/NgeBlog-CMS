<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Support\CoverImage;
use Database\Factories\ArticleContentPool;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (! $admin) {
            $admin = User::factory()->create(['role' => 'admin']);
        }

        $categories = Category::all();
        $suffixes = ArticleContentPool::titleSuffixes();
        $counter = [];
        $usedTitles = [];
        $now = now();

        foreach (range(1, 50) as $i) {
            $isPublished = $i <= 40;
            $category = $categories->random();

            $counter[$category->id] = ($counter[$category->id] ?? -1) + 1;
            $n = $counter[$category->id];

            // Ambil konten sesuai kategori, dan judul dijamin unik.
            $content = ArticleContentPool::pickAt($category->slug, $n);
            $title = $this->judulUnik($content['title'], $suffixes, $n, $usedTitles);
            $usedTitles[] = $title;

            // Ambil tiga paragraf berurutan supaya isi tiap artikel berbeda
            // meskipun topiknya sama.
            $paragraphs = $content['paragraphs'];
            $offset = $n % 2;
            $body = array_slice($paragraphs, $offset, 3) ?: $paragraphs;

            $slug = Str::slug($title).'-'.$i;

            Article::factory()
                ->when(! $isPublished, fn ($factory) => $factory->draft())
                ->create([
                    'user_id' => $admin->id,
                    'category_id' => $category->id,
                    'title' => $title,
                    'slug' => $slug,
                    'excerpt' => $content['excerpt'],
                    'content' => implode("\n\n", $body),
                    // Cover berupa link, bukan berkas: browser yang mengunduhnya.
                    // Seed = slug supaya gambar tiap artikel stabil dan backfill
                    // data lama menghasilkan URL yang sama.
                    'cover_image' => CoverImage::randomUrl($slug),
                    'published_at' => $isPublished
                        ? $this->tanggalPublish($now, $i)
                        : null,
                ]);
        }
    }

    /**
     * Rangkai judul dasar dengan imbuhan, dan pastikan hasilnya belum dipakai.
     *
     * @param  list<string>  $suffixes
     * @param  list<string>  $usedTitles
     */
    private function judulUnik(string $dasar, array $suffixes, int $index, array $usedTitles): string
    {
        $total = count($suffixes);

        for ($k = 0; $k < $total; $k++) {
            $candidate = $dasar.$suffixes[($index + $k) % $total];

            if (! in_array($candidate, $usedTitles, true)) {
                return $candidate;
            }
        }

        // Seluruh imbuhan sudah terpakai untuk topik ini, tambahkan penomoran.
        return $dasar.' (Bagian '.($index + 1).')';
    }

    /**
     * Tanggal publish yang dijamin berbeda antar artikel.
     *
     * Jarak antar artikel selalu lebih besar daripada rentang acak yang
     * ditambahkan, sehingga tidak mungkin dua artikel mendapat detik yang sama.
     * Hasilnya urutan "Terbaru" dan "Terlama" selalu bermakna.
     */
    private function tanggalPublish($now, int $index): string
    {
        return $now->copy()
            ->subMinutes($index * 3000)
            ->subMinutes(random_int(0, 240))
            ->toDateTimeString();
    }
}
