<?php

namespace App\Filament\Resources\FolderResource\Pages;

use App\Filament\Resources\FolderResource;
use App\Models\Folder;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditFolder extends EditRecord
{
    protected static string $resource = FolderResource::class;

    /**
     * Automatically regenerate unique slug based on the current title (ЧПУ).
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $title = trim((string) ($data['title'] ?? ''));
        $baseSlug = Str::slug($title);

        $ignoreId = $this->record?->id;

        $data['slug'] = $this->ensureUniqueSlug($baseSlug, $ignoreId);

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

