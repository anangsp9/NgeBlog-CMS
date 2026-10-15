<?php

/**
 * Isi APP_KEY hanya bila masih kosong.
 *
 * Dipakai oleh `composer setup`. Kenapa tidak memakai `php artisan key:generate`
 * biasa: perintah itu memanggil confirmToProceed() ketika key sudah ada, dan
 * pada mode non-interaktif jawabannya otomatis "ya" - jadi menjalankan
 * `composer setup` untuk kedua kalinya akan memutar APP_KEY tanpa konfirmasi,
 * membatalkan seluruh sesi yang sedang aktif. (Terukur langsung: key berubah
 * tanpa peringatan apa pun.)
 *
 * Skrip ini murni PHP tanpa booting Laravel, sehingga bisa jalan lewat PHP host
 * sebelum container dijalankan.
 */
$envPath = dirname(__DIR__).'/.env';

if (! file_exists($envPath)) {
    fwrite(STDERR, "Gagal: berkas .env tidak ditemukan.\n");

    exit(1);
}

$contents = file_get_contents($envPath);

if ($contents === false || ! preg_match('/^APP_KEY=(.*)$/m', $contents, $match)) {
    fwrite(STDERR, "Gagal: tidak menemukan baris APP_KEY di .env.\n");

    exit(1);
}

if (trim($match[1]) !== '') {
    echo "APP_KEY sudah terisi, dilewati.\n";

    exit(0);
}

// Panjang 32 byte untuk cipher bawaan aes-256-cbc, sama dengan key:generate.
$newKey = 'base64:'.base64_encode(random_bytes(32));
$updated = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$newKey, $contents, 1);

if ($updated === null || file_put_contents($envPath, $updated) === false) {
    fwrite(STDERR, "Gagal: tidak dapat menulis .env.\n");

    exit(1);
}

echo "APP_KEY dibuat.\n";
