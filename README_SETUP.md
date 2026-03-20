# dima — фото/видео альбом (Laravel + Filament)

## 1) Документ-корень Laragon
Рекомендуется указать Document Root на:

`c:/server/www/dima/app/public`

Тогда проект будет доступен как `http://dima.test`.

## 2) Установка Laravel в папку `app`
Откройте Laragon Terminal и выполните:

```powershell
cd "C:\server\www\dima\app"
composer create-project laravel/laravel . --prefer-dist
php artisan key:generate
```

## 3) Настройка БД MySQL
Создайте `.env` из `.env.example` и укажите:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=YOUR_DB
DB_USERNAME=YOUR_USER
DB_PASSWORD=YOUR_PASS

APP_URL=http://dima.test
```

Далее:

```powershell
php artisan migrate
```

## 4) Установка Filament + пакетов
Затем выполните:

```powershell
composer require filament/filament:"^3.0"
php artisan filament:install --no-interaction
```

После этого я продолжу: создадим модели `ContentItem` (post/folder), `Media`, настроим форму поста (медиа внутри поста), сделаем фронт (лента “новые сверху”, галерея папок с фоном авто-fallback).

## Вопрос (чтобы не переделывать)
На этом этапе подтвердите, пожалуйста:
1) вы готовы поставить Document Root на `.../app/public`?
2) MySQL у вас доступен из VPS/локально как `127.0.0.1` (как обычно в Laragon)?

