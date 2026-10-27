# Dockerfile untuk cabang "deploy" (SnapDeploy). Asset Tailwind/Vite sudah di-build
# dan di-commit di public/build, jadi tidak perlu Node.js di image.
FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip curl ca-certificates libpq-dev libzip-dev libpng-dev libonig-dev libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_pgsql zip gd mbstring curl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/testing storage/framework/views storage/logs bootstrap/cache \
    && php artisan storage:link || true

EXPOSE 8080

CMD ["sh", "start.sh"]
