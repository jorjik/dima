#!/bin/sh
set -e
cd /var/www/html

# Том Railway монтируется на ./storage — при пустом томе создаём структуру Laravel
mkdir -p \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/logs \
    storage/app/public \
    bootstrap/cache

# Права (volume может быть root-owned)
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

# Автоматически запускаем миграции
php artisan migrate --force

# Симлинк public/storage → storage/app/public (если ещё нет)
if [ ! -e public/storage ]; then
    php artisan storage:link --force 2>/dev/null || true
fi

exec "$@"
