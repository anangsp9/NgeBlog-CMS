<?php

/**
 * Backfill satu kali: isi cover_image yang masih NULL/kosong dengan link gambar
 * acak. Baris yang sudah punya cover (file maupun link) tidak disentuh, jadi
 * tidak ada data yang hilang.
 *
 * Jalankan: docker compose exec -T app php scripts/backfill-cover-link.php
 * (aman dijalankan berulang - hanya mengisi yang masih kosong).
 */

use App\Models\Article;
use App\Support\CoverImage;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$pending = Article::query()
    ->whereNull('cover_image')
    ->orWhere('cover_image', '')
    ->orderBy('id');

// php scripts/backfill-cover-link.php --dry-run  -> hanya laporan, tidak menulis.
if (in_array('--dry-run', $argv, true)) {
    echo 'Artikel tanpa cover (belum diubah): '.$pending->count().PHP_EOL;
    echo 'Total artikel                     : '.Article::query()->count().PHP_EOL;

    return;
}

$terisi = 0;

$pending->each(function (Article $article) use (&$terisi) {
    $article->update(['cover_image' => CoverImage::randomUrl($article->slug)]);
    $terisi++;
});

$kosong = Article::query()->whereNull('cover_image')->orWhere('cover_image', '')->count();
$contoh = Article::query()->whereNotNull('cover_image')->orderBy('id')->first();

echo 'Artikel yang mendapat cover baru : '.$terisi.PHP_EOL;
echo 'Artikel tanpa cover setelahnya   : '.$kosong.PHP_EOL;
echo 'Contoh cover                     : '.($contoh->cover_image ?? '-').PHP_EOL;
