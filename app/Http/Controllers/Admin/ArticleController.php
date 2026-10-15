<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Support\CoverImage;
use App\Support\UniqueSlug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArticleController extends Controller
{
    private const COVER_DIRECTORY = 'covers';

    /**
     * Batas percobaan menghindari bentrok slug yang sama.
     */
    private const SLUG_ATTEMPTS = 5;

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = $request->string('search')->trim()->value();

        $articles = Article::query()
            ->with(['category', 'user'])
            ->withCount([
                'comments as approved_comments_count' => fn ($query) => $query->approved(),
            ])
            ->when(in_array($status, ['draft', 'published'], true), fn ($query) => $query->where('status', $status))
            // Pakai scope Search model, bukan logika LIKE duplikat: scope sudah
            // meng-escape wildcard % dan _ (versi lama bisa dicari pakai '%' dan
            // mengembalikan semua baris), sekaligus menyatukan perilaku pencarian
            // admin dengan pencarian publik.
            ->search($search)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.articles.index', compact('articles', 'status', 'search'));
    }

    public function create(): View
    {
        return view('admin.articles.create', [
            'article' => new Article(['status' => 'draft']),
            'categories' => $this->categories(),
        ]);
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Upload sekali di luar loop, supaya percobaan ulang tidak menghasilkan
        // berkas cover ganda yang mengambang tanpa pemilik.
        $cover = $this->uploadCover($request) ?? $this->remoteCover($data);

        $article = $this->withSlugRetry(
            fn () => Article::create([
                ...$this->attributesFrom($data, null, $cover),
                'user_id' => $request->user()->getKey(),
            ]),
        );

        return redirect()
            ->route('admin.articles.index')
            ->with('status', "Artikel \"{$article->title}\" berhasil dibuat.");
    }

    public function edit(Article $article): View
    {
        return view('admin.articles.edit', [
            'article' => $article,
            'categories' => $this->categories(),
        ]);
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        $data = $request->validated();

        $previousCover = $article->cover_image;
        $cover = $this->uploadCover($request) ?? $this->remoteCover($data) ?? $previousCover;

        $this->withSlugRetry(
            fn () => $article->update($this->attributesFrom($data, $article, $cover)),
        );

        // Berkas cover lama dihapus hanya setelah baris database sudah menunjuk ke
        // yang baru. Versi sebelumnya menghapusnya lebih dulu, sehingga bila update
        // gagal berkasnya hilang sementara baris masih menunjuk ke sana - gambar
        // jadi rusak.
        if ($cover !== $previousCover && $previousCover !== null) {
            $this->deleteCover($previousCover);
        }

        return redirect()
            ->route('admin.articles.index')
            ->with('status', "Artikel \"{$article->title}\" berhasil diperbarui.");
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->deleteCover($article->cover_image);

        $title = $article->title;
        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('status', "Artikel \"{$title}\" berhasil dihapus.");
    }

    /**
     * Jalankan pembuatan/pembaruan yang slug-nya diturunkan dari judul.
     *
     * UniqueSlug memakai pola cek-lalu-tulis: ia menanyakan apakah slug sudah
     * dipakai, lalu menulisnya nanti. Dua permintaan serentak dengan judul sama
     * bisa sama-sama memilih slug yang sama sebelum salah satu commit. Indeks
     * unique di database tetap menahan datanya (tidak ada duplikat yang lolos);
     * yang dilakukan di sini hanya mengulang dengan slug yang kini sudah terlihat
     * bebas, sehingga pengguna mendapat artikel sukses, bukan layar 500.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $write
     * @return TReturn
     */
    private function withSlugRetry(callable $write)
    {
        $attempt = 0;

        while (true) {
            $attempt++;

            try {
                return $write();
            } catch (QueryException $exception) {
                if (! $this->isSlugCollision($exception) || $attempt >= self::SLUG_ATTEMPTS) {
                    throw $exception;
                }
            }
        }
    }

    private function isSlugCollision(QueryException $exception): bool
    {
        // SQLSTATE 23000 = integrity constraint violation, berlaku sama di MySQL
        // (kode 1062) dan SQLite (kode 19). Penyaringan kata 'slug' memastikan
        // pelanggaran unique lain (mis. email) tetap dilempar apa adanya.
        return $exception->getCode() === '23000'
            && str_contains($exception->getMessage(), 'slug');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributesFrom(array $data, ?Article $article, ?string $cover): array
    {
        return [
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'slug' => $this->resolveSlug($article, $data['title']),
            'excerpt' => $data['excerpt'],
            'content' => $data['content'],
            'cover_image' => $cover,
            'status' => $data['status'],
            'published_at' => $data['status'] === 'published'
                ? ($data['published_at'] ?? $article?->published_at ?? now())
                : null,
        ];
    }

    private function resolveSlug(?Article $article, string $title): string
    {
        // getOriginal(), bukan $article->title: setelah sebuah update gagal,
        // Model::update() sudah sempat mengisi atributnya sebelum query dilempar.
        // Memakai nilai berisi akan membuat percobaan ulang menghitung slug yang
        // sama persis dan terus-menerus bentrok.
        if ($article && $article->getOriginal('title') === $title) {
            return $article->slug;
        }

        return UniqueSlug::for($article ?? new Article, $title, $article?->getKey());
    }

    private function uploadCover(Request $request): ?string
    {
        if (! $request->hasFile('cover_image')) {
            return null;
        }

        return $request->file('cover_image')->store(self::COVER_DIRECTORY, 'public');
    }

    /**
     * Cover dari link gambar, dipakai bila admin tidak mengunggah berkas.
     *
     * Nilainya URL absolut, jadi disimpan apa adanya dan tidak pernah menyentuh
     * disk - validasi formatnya sudah ditangani ArticleRequest.
     *
     * @param  array<string, mixed>  $data
     */
    private function remoteCover(array $data): ?string
    {
        $url = $data['cover_url'] ?? null;

        return filled($url) ? (string) $url : null;
    }

    private function deleteCover(?string $path): void
    {
        // Cover berupa link tidak dimiliki storage, tidak ada berkas untuk
        // dihapus. Melewatkan nilai ini ke Storage berarti menanyakan keberadaan
        // berkas dengan nama 'https://...' yang mustahil ada.
        if (blank($path) || CoverImage::isRemote($path)) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * @return Collection<int, Category>
     */
    private function categories()
    {
        return Category::orderBy('name')->get();
    }
}
