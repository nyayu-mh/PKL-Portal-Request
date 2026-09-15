# Dockerfile untuk deploy Portal Request BTC ke Render.com (free tier).
# Base image resmi PHP CLI — dipakai bersama server bawaan Laravel (php artisan serve),
# cukup untuk aplikasi internal skala kecil/testing seperti ini.

FROM php:8.3-cli

# Ekstensi sistem & PHP yang dibutuhkan Laravel + koneksi Postgres
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip curl ca-certificates libpq-dev libzip-dev libpng-dev libonig-dev libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_pgsql zip gd mbstring curl \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node.js (untuk build asset Tailwind/Vite saat image dibuat)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm install \
    && npm run build \
    && rm -rf node_modules \
    && mkdir -p storage/framework/{cache,sessions,testing,views} storage/logs bootstrap/cache \
    && php artisan storage:link || true

EXPOSE 8080

# Saat container start: jalankan migration (aman diulang tiap deploy), cache config,
# lalu jalankan server. Render mengisi $PORT secara otomatis.
CMD php artisan migrate --force \
    && php artisan app:seed-if-empty \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
