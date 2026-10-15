<?php

namespace Tests\Unit;

use App\Support\Tanggal;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TanggalTest extends TestCase
{
    public function test_tanggal_kosong_dikembalikan_sebagai_penanda(): void
    {
        $this->assertSame('-', Tanggal::panjang(null));
        $this->assertSame('-', Tanggal::pendek(null));
        $this->assertSame('', Tanggal::input(null));
    }

    public function test_tanggal_panjang_menggunakan_nama_bulan_penuh(): void
    {
        $tanggal = Carbon::create(2026, 10, 5);

        $this->assertSame('5 Oktober 2026', Tanggal::panjang($tanggal));
    }

    public function test_tanggal_pendek_memotong_nama_bulan(): void
    {
        $tanggal = Carbon::create(2026, 10, 5, 21, 5);

        $this->assertSame('5 Okt 2026', Tanggal::pendek($tanggal));
        $this->assertSame('5 Okt 2026 21:05', Tanggal::pendek($tanggal, denganWaktu: true));
    }

    public function test_nilai_input_cocok_dengan_format_datetime_local(): void
    {
        $tanggal = Carbon::create(2026, 1, 9, 8, 30);

        $this->assertSame('2026-01-09T08:30', Tanggal::input($tanggal));
    }

    #[DataProvider('bulanProvider')]
    public function test_seluruh_bulan_bersedia_dalam_bahasa_indonesia(int $bulan, string $harapan): void
    {
        $this->assertSame($harapan, Tanggal::panjang(Carbon::create(2026, $bulan, 1)));
    }

    /**
     * @return array<int, array{0: int, 1: string}>
     */
    public static function bulanProvider(): array
    {
        return [
            [1, '1 Januari 2026'],
            [2, '1 Februari 2026'],
            [3, '1 Maret 2026'],
            [4, '1 April 2026'],
            [5, '1 Mei 2026'],
            [6, '1 Juni 2026'],
            [7, '1 Juli 2026'],
            [8, '1 Agustus 2026'],
            [9, '1 September 2026'],
            [10, '1 Oktober 2026'],
            [11, '1 November 2026'],
            [12, '1 Desember 2026'],
        ];
    }
}
