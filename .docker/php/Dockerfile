FROM php:8.3-apache-bookworm

# Install system dependencies + mysql client (untuk wait-for-db di entrypoint)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions yang dibutuhkan Laravel 12
# (ctype, fileinfo, json, tokenizer, xml, openssl, pdo sudah built-in di PHP 8.3)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Apache modules:
# - rewrite : routing Laravel via public/.htaccess
# - expires : header Expires otomatis untuk aset statis
# - headers : set Cache-Control immutable untuk aset ber-nama content-hash (Vite)
RUN a2enmod rewrite expires headers

# Arahkan DocumentRoot ke public/ Laravel.
# PENTING: pakai path literal, bukan ${ENV_VAR}. Blok <Directory> di Apache
# hanya berlaku pada document root, sehingga symlink storage/app/public
# (target dari public/storage) akan kena 403 kalau grant-nya dipersempit.
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && printf 'ServerName localhost\n' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

# Grant eksplisit untuk folder aplikasi (ikut public/ dan storage/app/public)
# + AllowOverride All agar public/.htaccess benar-benar dipakai.
COPY .docker/php/apache-app.conf /etc/apache2/conf-available/app.conf
RUN a2enconf app

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# OPcache: file resmi dari docker-php-ext-install hanya berisi zend_extension,
# jadi seluruh tuning (memory, max_accelerated_files, revalidate_freq) harus
# ditambahkan lewat file conf.d terpisah.
COPY .docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

# Entrypoint: tunggu DB, bereskan permission, baru jalanin Apache
# (Code Laravel di-mount via bind volume, jadi TIDAK ada COPY . . / composer install di sini)
COPY .docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
