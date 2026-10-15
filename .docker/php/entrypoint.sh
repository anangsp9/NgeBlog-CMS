#!/bin/sh
set -e

# Tunggu MySQL siap sebelum Apache start (hindari "Connection refused" saat boot)
DB_HOST_VAL=${DB_HOST:-db}
DB_PORT_VAL=${DB_PORT:-3306}

echo "Waiting for MySQL at ${DB_HOST_VAL}:${DB_PORT_VAL}..."
for i in $(seq 1 30); do
  if mysqladmin ping -h"$DB_HOST_VAL" -P"$DB_PORT_VAL" --silent 2>/dev/null; then
    echo "MySQL is up!"
    break
  fi
  echo "  ... attempt $i/30"
  sleep 2
  if [ "$i" -eq 30 ]; then
    echo "WARNING: MySQL not reachable, starting Apache anyway."
  fi
done

# Install composer deps otomatis bila fresh clone (belum ada vendor/)
if [ ! -f "vendor/autoload.php" ]; then
  echo "vendor/ not found, running composer install..."
  composer install --no-interaction --prefer-dist
fi

# Permission storage & cache (gagal di Windows bind-mount = abaikan)
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Pastikan storage link ada (skip kalau sudah ada, supaya tidak error ulang)
if [ ! -e public/storage ]; then
  php artisan storage:link || true
fi

# Optimasi runtime.
#
# - local: bersihkan cache supaya perubahan routes/, view, dan config tidak perlu
#   menunggu restart container.
# - production (APP_ENV != local): isi cache config/route/view/event sekali saat
#   boot, memotong pembacaan & parse berkas config serta kompilasi regex route di
#   tiap request.
#
# Catatan: merubah nilai di berkas .env TETAP memerlukan `docker compose up -d`,
# bukan sekadar optimize:clear. Alasannya compose memuat .env lewat env_file yang
# dikunci ke env proses di awal container, sedangkan dotenv Laravel bersifat
# immutable sehingga nilai proses selalu menang atas isi berkas.
if [ "${APP_ENV:-local}" = "local" ]; then
  php artisan optimize:clear >/dev/null 2>&1 || true
else
  if ! php artisan optimize >/dev/null 2>&1; then
    echo "WARNING: artisan optimize gagal, lanjut tanpa cache."
  fi
fi

exec "$@"
