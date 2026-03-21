<?php

namespace App\Filament\Resources\SiteSettingResource\Pages;

use App\Filament\Resources\SiteSettingResource;
use App\Models\SiteSetting;
use Filament\Resources\Pages\EditRecord;

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
}

