<?php

namespace App\Filament\Resources\FolderResource\Pages;

use App\Filament\Resources\FolderResource;
use App\Models\Folder;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateFolder extends CreateRecord
{
    protected static string $resource = FolderResource::class;

    /**
     * Automatically generate unique slug from title (ЧПУ).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $title = trim((string) ($data['title'] ?? ''));
        $baseSlug = Str::slug($title);

        $data['slug'] = $this->ensureUniqueSlug($baseSlug);

        return $data;
    }

    private function ensureUniqueSlug(?string $baseSlug, ?int $ignoreId = null): string
    {
        $base = filled($baseSlug) ? $baseSlug : ('folder-' . Str::random(8));
        $slug = $base;

        $i = 1;
        while (
            Folder::query()
                ->where('slug', $slug)
                ->when(filled($ignoreId), fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $i++;
            $slug = $base . '-' . $i;
        }

        return $slug;
    }
}

