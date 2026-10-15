<?php

namespace App\Models;

use App\Support\CoverImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * URL absolut untuk gambar cover. Dikembalikan absolute agar bisa dipakai
     * pada atribut src maupun tag Open Graph / Twitter Card.
     *
     * Kolom cover_image menyimpan dua bentuk: path berkas pada disk public,
     * atau URL gambar eksternal. URL sudah absolut sehingga dipakai apa adanya -
     * membungkusnya dengan Storage::url() justru menghasilkan '/storage/https://...'
     * yang rusak.
     */
    public function coverUrl(): ?string
    {
        if (blank($this->cover_image)) {
            return null;
        }

        if (CoverImage::isRemote($this->cover_image)) {
            return $this->cover_image;
        }

        return url(Storage::disk('public')->url($this->cover_image));
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at');
    }

    /**
     * Pencarian substring di judul, ringkasan, dan isi artikel.
     *
     * Bentuk LIKE berawalan wildcard ('%x%') tidak bisa memakai index B-tree mana
     * pun, sehingga selalu memindai seluruh tabel - termasuk kolom content
     * (longText) yang menjadi kolom terbesar. Biaya itu dibatasi dua cara:
     * wildcard di dalam kata kunci di-escape, dan kata kunci yang terlalu pendek
     * diabaikan (pencarian 1 karakter tidak bermakna tapi tetap memicu penuh scan).
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if (mb_strlen($term) < 2) {
            return $query;
        }

        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return $query->where(function (Builder $query) use ($like) {
            $query->where('title', 'like', $like)
                ->orWhere('excerpt', 'like', $like)
                ->orWhere('content', 'like', $like);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments()
    {
        return $this->comments()->where('status', 'approved');
    }
}
