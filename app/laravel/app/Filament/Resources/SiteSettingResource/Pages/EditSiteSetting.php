<?php

namespace App\Filament\Resources\SiteSettingResource\Pages;

use App\Filament\Resources\SiteSettingResource;
use App\Helpers\ImageHelper;
use App\Models\SiteSetting;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditSiteSetting extends EditRecord
{
    protected static string $resource = SiteSettingResource::class;

    protected function resolveRecord($key): SiteSetting
    {
        /** @var SiteSetting $record */
        $record = SiteSetting::query()->find($key);

        if (! $record) {
            $record = SiteSetting::query()->create([
                'id' => (int) $key,
                'header_title' => 'Альбом жизни',
                'home_meta_title' => 'Альбом жизни',
                'home_meta_description' => 'Фото и видео из семейного архива.',
                'site_background_overlay_percent' => 20,
            ]);
        }

        return $record;
    }

    protected function afterSave(): void
    {
        $settings = $this->record;
        $disk = Storage::disk('public');

        $imageFields = [
            'header_background_path',
            'home_hero_background_path',
            'site_background_path',
        ];

        foreach ($imageFields as $field) {
            $path = $settings->{$field} ?? null;
            if (filled($path)) {
                $fullPath = $disk->path((string) $path);
                ImageHelper::resizeToMaxWidth($fullPath);
            }
        }
    }
}

