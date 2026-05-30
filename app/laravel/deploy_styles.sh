#!/bin/bash
REMOTE="root@185.237.207.32"
REMOTE_PATH="/var/www/html/app/laravel"

FILES=(
    "database/migrations/2026_05_30_071839_add_opacity_fields_to_site_settings_table.php"
    "app/Models/SiteSetting.php"
    "app/Filament/Resources/SiteSettingResource.php"
    "resources/views/public/layouts/app.blade.php"
    "resources/views/public/home.blade.php"
    "app/Http/Controllers/Public/HomeController.php"
    "app/Http/Controllers/Public/FolderController.php"
    "resources/views/public/folder.blade.php"
)

for FILE in "${FILES[@]}"; do
    echo "Uploading $FILE..."
    scp -o StrictHostKeyChecking=no "$FILE" "$REMOTE:$REMOTE_PATH/$FILE"
done

echo "Running migrations..."
ssh -o StrictHostKeyChecking=no "$REMOTE" "cd $REMOTE_PATH && php artisan migrate --force && php artisan view:clear && php artisan cache:clear"
