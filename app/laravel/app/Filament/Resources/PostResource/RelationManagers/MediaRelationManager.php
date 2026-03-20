<?php

namespace App\Filament\Resources\PostResource\RelationManagers;

use App\Models\MediaFile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $title = 'Медиафайлы поста';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            FileUpload::make('path')
                ->label('Файл')
                ->required(fn (string $operation): bool => $operation === 'create')
                ->disk('public')
                ->directory('posts/uploads')
                ->acceptedFileTypes([
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'image/gif',
                    'image/svg+xml',
                    'video/mp4',
                    'video/webm',
                    'video/ogg',
                    'video/quicktime',
                    'video/x-matroska',
                    'audio/mpeg',
                    'audio/mp3',
                    'audio/wav',
                    'audio/x-wav',
                    'audio/ogg',
                    'audio/m4a',
                    'audio/mp4',
                ]),

            TextInput::make('original_name')
                ->label('Имя файла')
                ->maxLength(255)
                ->helperText('Тип определяется автоматически по файлу. Имя можно оставить пустым.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->reorderRecordsTriggerAction(
                fn (Action $action): Action => $action
                    ->label('Изменить порядок')
                    ->icon('heroicon-m-arrows-up-down')
            )
            ->paginated(false)
            ->recordTitleAttribute('original_name')
            ->columns([
                ImageColumn::make('path')
                    ->label('Превью')
                    ->state(function (MediaFile $record): ?string {
                        if ($record->media_type !== MediaFile::TYPE_IMAGE) {
                            return null;
                        }

                        $disk = Storage::disk('public');
                        if (! filled($record->path) || ! $disk->exists((string) $record->path)) {
                            return null;
                        }

                        return (string) $record->path;
                    })
                    ->disk('public')
                    ->height(42)
                    ->width(60)
                    ->square()
                    ->defaultImageUrl(function (MediaFile $record): string {
                        if ($record->media_type === MediaFile::TYPE_VIDEO) {
                            return "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='60' height='42'><rect width='60' height='42' rx='6' fill='%23111'/><text x='30' y='27' text-anchor='middle' font-size='18' fill='%23fff'>V</text></svg>";
                        }

                        if ($record->media_type === MediaFile::TYPE_AUDIO) {
                            return "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='60' height='42'><rect width='60' height='42' rx='6' fill='%23111'/><text x='30' y='27' text-anchor='middle' font-size='18' fill='%23fff'>A</text></svg>";
                        }

                        return "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='60' height='42'><rect width='60' height='42' rx='6' fill='%23111'/><text x='30' y='27' text-anchor='middle' font-size='16' fill='%23fff'>IMG</text></svg>";
                    }),

                TextColumn::make('media_type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        MediaFile::TYPE_IMAGE => 'Фото',
                        MediaFile::TYPE_VIDEO => 'Видео',
                        MediaFile::TYPE_AUDIO => 'Аудио',
                        default => $state,
                    }),

                TextColumn::make('original_name')
                    ->label('Файл')
                    ->searchable(),

                TextColumn::make('sort')
                    ->label('Порядок')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Добавить медиа')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data = $this->hydrateFileMeta($data);
                        $owner = $this->getOwnerRecord();
                        $data['sort'] = ((int) ($owner->media()->max('sort') ?? -1)) + 1;

                        return $data;
                    }),
            ])
            ->actions([
                Action::make('setCover')
                    ->label('Сделать обложкой')
                    ->icon('heroicon-m-photo')
                    ->visible(fn (MediaFile $record): bool => $record->media_type === MediaFile::TYPE_IMAGE)
                    ->action(function (MediaFile $record): void {
                        $post = $this->getOwnerRecord();
                        $post->cover_media_id = $record->id;
                        $post->save();

                        Notification::make()
                            ->title('Обложка обновлена')
                            ->success()
                            ->send();
                    }),

                EditAction::make()
                    ->mutateFormDataUsing(function (array $data, MediaFile $record): array {
                        $oldPath = $record->path;
                        $data = $this->hydrateFileMeta($data);

                        if (filled($oldPath) && $oldPath !== ($data['path'] ?? null)) {
                            Storage::disk('public')->delete((string) $oldPath);
                        }

                        return $data;
                    }),

                DeleteAction::make()
                    ->before(function (MediaFile $record): void {
                        $post = $this->getOwnerRecord();
                        if ((int) $post->cover_media_id === (int) $record->id) {
                            $post->cover_media_id = null;
                            $post->save();
                        }
                    })
                    ->after(function (MediaFile $record): void {
                        if (filled($record->path)) {
                            Storage::disk('public')->delete((string) $record->path);
                        }

                        $post = $this->getOwnerRecord()->fresh();
                        if (blank($post?->cover_media_id)) {
                            $firstImage = $post?->images()->orderBy('sort')->orderBy('created_at')->orderBy('id')->first();
                            if ($firstImage) {
                                $post->cover_media_id = $firstImage->id;
                                $post->save();
                            }
                        }
                    }),
            ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function hydrateFileMeta(array $data): array
    {
        $path = (string) ($data['path'] ?? '');
        if ($path === '') {
            return $data;
        }

        $fullPath = Storage::disk('public')->path($path);

        $mime = is_file($fullPath) ? (@mime_content_type($fullPath) ?: null) : null;
        $sizeBytes = is_file($fullPath) ? (@filesize($fullPath) ?: null) : null;
        $mediaType = $this->detectMediaType($path, $mime, (string) ($data['media_type'] ?? ''));

        $width = null;
        $height = null;
        if ($mediaType === MediaFile::TYPE_IMAGE && is_file($fullPath)) {
            $dims = @getimagesize($fullPath);
            if (is_array($dims)) {
                $width = $dims[0] ?? null;
                $height = $dims[1] ?? null;
            }
        }

        $data['media_type'] = $mediaType;
        $data['mime'] = $mime;
        $data['size_bytes'] = $sizeBytes;
        $data['width'] = $width;
        $data['height'] = $height;
        $data['original_name'] = filled($data['original_name'] ?? null) ? $data['original_name'] : basename($path);

        return $data;
    }

    private function detectMediaType(string $path, ?string $mime, string $fallback = ''): string
    {
        $mime = strtolower((string) $mime);
        if (str_starts_with($mime, 'image/')) {
            return MediaFile::TYPE_IMAGE;
        }

        if (str_starts_with($mime, 'video/')) {
            return MediaFile::TYPE_VIDEO;
        }

        if (str_starts_with($mime, 'audio/')) {
            return MediaFile::TYPE_AUDIO;
        }

        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true)) {
            return MediaFile::TYPE_IMAGE;
        }
        if (in_array($ext, ['mp4', 'webm', 'ogv', 'mov', 'mkv'], true)) {
            return MediaFile::TYPE_VIDEO;
        }
        if (in_array($ext, ['mp3', 'wav', 'ogg', 'm4a'], true)) {
            return MediaFile::TYPE_AUDIO;
        }

        return $fallback !== '' ? $fallback : MediaFile::TYPE_IMAGE;
    }
}

