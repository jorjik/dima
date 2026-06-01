# Deployment via SSH

При деплое Laravel-приложения на сервер используй **SSH** с паролем из `.env`.

## SSH доступы

Читать из `app/laravel/.env` — там лежат:

```
DEPLOY_HOST=185.237.207.32
DEPLOY_USER=root
DEPLOY_PASSWORD=<пароль>
```

Пароль читать командой: `grep DEPLOY_PASSWORD app/laravel/.env`

## Полный деплой

```bash
# 1. Запушить изменения в main (если не запушил)
git add -A
git commit -m "описание изменений"
git push origin main

# 2. SSH на сервер
sshpass -p '<пароль>' ssh -o StrictHostKeyChecking=no root@185.237.207.32

# 3. На сервере — стянуть код
cd /var/www/html
git pull origin main

# 4. Пересобрать Vite (JS/CSS)
cd app/laravel
npm ci
npm run build

# 5. Обновить Laravel
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link --force || true

# 6. Выйти
exit
```

## Если git pull конфликтует

На сервере могут быть локальные изменения (stash от предыдущего деплоя или недодеплоенные правки).

### Вариант А — принять версию из репозитория (рекомендуется)

```bash
cd /var/www/html

# Сбросить конфликтные файлы до версии из репозитория
git checkout HEAD -- app/laravel/app/Filament/Resources/SiteSettingResource.php
git checkout HEAD -- app/laravel/app/Models/SiteSetting.php
git checkout HEAD -- app/laravel/resources/views/public/layouts/app.blade.php
# ... добавить другие конфликтные файлы

# Удалить untracked файлы, которые мешают pull
rm -f app/laravel/database/migrations/2026_05_30_071839_add_opacity_fields_to_site_settings_table.php
rm -f app/laravel/config/filament.php

git add -A
git stash drop
git pull origin main
```

### Вариант Б — stash + pop (если локальные правки важны)

```bash
cd /var/www/html
rm -f app/laravel/database/migrations/2026_05_30_071839_add_opacity_fields_to_site_settings_table.php
rm -f app/laravel/config/filament.php
git stash
git pull origin main
git stash pop
```

## Важно

- `sshpass` должен быть установлен. Если нет: `apt install sshpass`
- Не менять SSH-доступы вручную — они хранятся в `.env`
- После деплоя всегда проверять, что `php artisan migrate --force` выполнился
- `npm ci && npm run build` на сервере пересобирает фронт (JS/CSS). `public/build` в `.gitignore`, поэтому это **обязательный** шаг
- Node.js v22 + npm уже установлены на сервере
