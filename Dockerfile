# Force redeploy - fix composer flag
# Laravel app lives in app/laravel — build context = repo root
# syntax=docker/dockerfile:1

# --- Frontend (Vite) ---
FROM node:22-bookworm-slim AS frontend
WORKDIR /app
COPY app/laravel/package.json app/laravel/package-lock.json* ./
RUN npm ci
COPY app/laravel .
RUN npm run build

# --- Composer dependencies (vendor/node_modules исключены в .dockerignore) ---
FROM composer:2 AS vendor
WORKDIR /app
COPY app/laravel .
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# --- Runtime ---
FROM php:8.3-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libpq-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Исходники без vendor / node_modules (см. .dockerignore)
COPY app/laravel .

COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

# Временный ключ, чтобы отработали discover и Filament при сборке
ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_KEY=base64:2fl+JaPfLHVfw84J8fHVj/WBQ+VOE/0z7fTYf4mD3sE=

RUN composer dump-autoload --optimize --classmap-authoritative \
    && php artisan package:discover --ansi \
    && php artisan filament:assets --ansi --no-interaction \
    && rm -f bootstrap/cache/*.php

# Права под www-data (serve можно оставить от root на Railway — так проще с volume)
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENV PORT=8000
EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
