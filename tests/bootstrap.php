<?php

/**
 * Bootstrap khusus test suite.
 *
 * Container menjalankan aplikasi dengan `env_file: .env`, sehingga nilai seperti
 * APP_ENV=local dan DB_CONNECTION=mysql sudah ada di $_SERVER sebelum PHPUnit
 * berjalan. PHPUnit hanya menulis ke $_ENV/getenv(), sehingga $_SERVER tetap
 * menang dan test bisa saja berjalan di database pengembangan.
 *
 * File ini menormalkan $_SERVER, $_ENV, dan getenv() sekaligus, lalu menolak
 * menjalankan test bila koneksinya bukan SQLite in-memory.
 */
$overrides = [
    'APP_ENV' => 'testing',
    'APP_URL' => 'http://localhost',
    // APP_DEBUG sengaja false. Saat true, setiap exception tak terduga membuat
    // renderer error debug merender konteks penuhnya - satu test yang memicu
    // kegagalan menyumbat suite (terukur 23 detik vs 7 detik). Dengan false,
    // response jatuh ke resources/views/errors/*.blade.php, sama seperti produksi.
    // Ubah jadi true sementara bila sedang mendiagnosis test yang gagal.
    'APP_DEBUG' => 'false',
    'APP_MAINTENANCE_DRIVER' => 'file',
    'BCRYPT_ROUNDS' => '4',
    'BROADCAST_CONNECTION' => 'null',
    'CACHE_STORE' => 'array',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'DB_URL' => '',
    'FILESYSTEM_DISK' => 'local',
    'MAIL_MAILER' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'SESSION_DRIVER' => 'array',
];

foreach ($overrides as $key => $value) {
    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

if ($overrides['DB_CONNECTION'] !== 'sqlite' || $overrides['DB_DATABASE'] !== ':memory:') {
    fwrite(STDERR, 'Test ditolak: konfigurasi database bukan SQLite in-memory.'.PHP_EOL);

    exit(1);
}

require __DIR__.'/../vendor/autoload.php';
