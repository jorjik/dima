<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages\CreatePost;
use App\Filament\Resources\PostResource\Pages\EditPost;
use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Filament\Resources\PostResource\RelationManagers\MediaRelationManager;
use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Posts';

    protected static ?int $navigationSort = 1;

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Post content')
                ->tabs([
                    Tab::make('Основное')
                        ->schema([
                            TextInput::make('title')
                                ->label('Заголовок')
                                ->required()
                                ->maxLength(255),

                            MarkdownEditor::make('body_markdown')
                                ->label('Текст (Markdown)')
                                ->placeholder('Списки, **жирный**, ссылки…')
                                ->toolbarButtons([
                                    'attachFiles',
                                    'bold',
                                    'italic',
                                    'strike',
                                    'link',
                                    'blockquote',
                                    'bulletList',
                                    'orderedList',
                                    'codeBlock',
                                    'table',
                                    'undo',
                                    'redo',
                                ])
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory('posts/markdown-attachments')
                                ->fileAttachmentsVisibility('public')
                                ->minHeight('16rem')
                                ->nullable(),

                            Select::make('folder_id')
                                ->label('Папка (альбом)')
                                ->relationship(name: 'folder', titleAttribute: 'title')
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->createOptionForm([
                                    TextInput::make('title')
                                        ->label('Название папки')
                                        ->required()
                                        ->maxLength(255),

                                    Select::make('background_media_id')
                                        ->label('Фон папки (только фото) - опционально')
                                        ->nullable()
                                        ->options(fn (): array => MediaFile::query()
                                            ->where('media_type', MediaFile::TYPE_IMAGE)
                                            ->latest()
                                            ->limit(200)
                                            ->get()
                                            ->mapWithKeys(fn (MediaFile $m): array => [
                                                (string) $m->id => $m->original_name ?: $m->path,
                                            ])
                                            ->toArray()
                                        ),
                                ])
                                ->createOptionUsing(function (array $data): int {
                                    $baseSlug = Str::slug($data['title'] ?? '');
                                    $slug = $baseSlug ?: ('folder-' . Str::random(8));

                                    $i = 1;
                                    while (Folder::query()->where('slug', $slug)->exists()) {
                                        $i++;
                                        $slug = $baseSlug . '-' . $i;
                                    }

                                    return Folder::query()->create([
                                        'title' => $data['title'],
                                        'slug' => $slug,
                                        'background_media_id' => $data['background_media_id'] ?? null,
                                    ])->getKey();
                                }),

                            Select::make('cover_media_id')
                                ->label('Обложка поста (только фото)')
                                ->nullable()
                                ->hiddenOn('create')
                                ->searchable()
                                ->options(fn (): array => MediaFile::query()
                                    ->where('media_type', MediaFile::TYPE_IMAGE)
                                    ->latest('created_at')
                                    ->limit(300)
                                    ->get()
                                    ->mapWithKeys(fn (MediaFile $m): array => [
                                        (string) $m->id => $m->original_name ?: $m->path,
                                    ])
                                    ->toArray()),
                        ]),

                    Tab::make('Медиа')
                        ->schema([
                            FileUpload::make('photos')
                                ->label('Фото (обложка берется с первого фото)')
                                ->image()
                                ->multiple()
                                ->reorderable()
                                ->hiddenOn('edit')
                                ->disk('public')
                                ->directory('posts/photos')
                                ->dehydrated(false)
                                ->acceptedFileTypes([
                                    'image/jpeg',
                                    'image/png',
                                    'image/webp',
                                    'image/gif',
                                    'image/svg+xml',
                                ]),

                            FileUpload::make('videos')
                                ->label('Видео (не используется для фона/обложки)')
                                ->multiple()
                                ->hiddenOn('edit')
                                ->disk('public')
                                ->directory('posts/videos')
                                ->dehydrated(false)
                                ->acceptedFileTypes([
                                    'video/mp4',
                                    'video/webm',
                                    'video/ogg',
                                    'video/quicktime',
                                    'video/x-matroska',
                                ]),

                            FileUpload::make('audios')
                                ->label('Аудио (не используется для фона/обложки)')
                                ->multiple()
                                ->hiddenOn('edit')
                                ->disk('public')
                                ->directory('posts/audios')
                                ->dehydrated(false)
                                ->acceptedFileTypes([
                                    'audio/mpeg',
                                    'audio/mp3',
                                    'audio/wav',
                                    'audio/x-wav',
                                    'audio/ogg',
                                    'audio/m4a',
                                    'audio/mp4',
                                ]),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make(),
            ])
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('folder.title')
                    ->label('Папка')
                    ->sortable()
                    ->toggleable(),

                ImageColumn::make('cover.path')
                    ->label('Обложка')
                    ->disk('public')
                    ->height(36)
                    ->width(48)
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MediaRelationManager::class,
        ];
    }

    // We don't define Laravel policies in this project.
    // Allow authenticated users to manage content so Filament shows "Create".
    public static function can(string $action, ?Model $record = null): bool
    {
        return true;
    }
}

