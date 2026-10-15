<?php

namespace App\Support;

use Illuminate\Support\Str;

class CoverImage
{
    /**
     * Layanan gambar acak. Dipakai sebagai pengganti upload berkas: gambar
     * dibaca langsung oleh browser dari link, tidak pernah diunduh server.
     */
    private const RANDOM_IMAGE_HOST = 'https://picsum.photos';

    private const RANDOM_IMAGE_WIDTH = 1200;

    private const RANDOM_IMAGE_HEIGHT = 675;

    /**
     * Apakah nilai adalah URL absolut (http/https), bukan path berkas storage.
     *
     * Nilai di kolom cover_image berbentuk salah satu dari dua: path relatif
     * pada disk public ('covers/abc.jpg') atau URL gambar eksternal. Bentuknya
     * harus bisa dibedakan sebelum nilai diproses, karena keduanya ditangani
     * berbeda - URL tidak boleh dilewatkan ke Storage, dan path tidak boleh
     * ditampilkan apa adanya sebagai src.
     */
    public static function isRemote(?string $value): bool
    {
        return $value !== null && Str::startsWith(Str::lower($value), ['http://', 'https://']);
    }

    /**
     * URL gambar acak yang stabil untuk satu seed.
     *
     * picsum.photos menentukan gambar dari seed, sehingga seed yang sama selalu
     * menghasilkan gambar yang sama - artikel tidak berganti gambar setiap
     * halaman dimuat. Seed memakai slug artikel supaya seeder dan backfill data
     * lama menghasilkan URL yang identik.
     *
     * Rasio 1200x675 (16:9) mengikuti kotak gambar pada kartu artikel.
     */
    public static function randomUrl(string $seed): string
    {
        return sprintf(
            '%s/seed/%s/%d/%d',
            self::RANDOM_IMAGE_HOST,
            rawurlencode($seed),
            self::RANDOM_IMAGE_WIDTH,
            self::RANDOM_IMAGE_HEIGHT,
        );
    }
}
