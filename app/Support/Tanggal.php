<?php

namespace App\Support;

use Carbon\CarbonInterface;

class Tanggal
{
    /**
     * @var array<int, string>
     */
    private const BULAN_PANJANG = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Contoh: 5 Oktober 2026
     */
    public static function panjang(?CarbonInterface $tanggal): string
    {
        if (! $tanggal) {
            return '-';
        }

        return sprintf(
            '%d %s %d',
            $tanggal->day,
            self::BULAN_PANJANG[$tanggal->month],
            $tanggal->year
        );
    }

    /**
     * Contoh: 5 Okt 2026, 21:05
     */
    public static function pendek(?CarbonInterface $tanggal, bool $denganWaktu = false): string
    {
        if (! $tanggal) {
            return '-';
        }

        $nama = self::BULAN_PANJANG[$tanggal->month];
        $bulan = mb_substr($nama, 0, 3);

        $hasil = sprintf('%d %s %d', $tanggal->day, $bulan, $tanggal->year);

        if ($denganWaktu) {
            $hasil .= ' '.$tanggal->format('H:i');
        }

        return $hasil;
    }

    /**
     * Nilai untuk atribut datetime-local pada input form.
     */
    public static function input(?CarbonInterface $tanggal): string
    {
        return $tanggal?->format('Y-m-d\TH:i') ?? '';
    }
}
