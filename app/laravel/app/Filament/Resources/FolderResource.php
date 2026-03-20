<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FolderResource\Pages\CreateFolder;
use App\Filament\Resources\FolderResource\Pages\EditFolder;
use App\Filament\Resources\FolderResource\Pages\ListFolders;
use App\Models\Folder;
use App\Models\MediaFile;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class FolderResource extends Resource
{
    protected static ?string $model = Folder::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationLabel = 'Folders';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return false; // основной пункт меню будет "Posts"
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFolders::route('/'),
            'create' => CreateFolder::route('/create'),
            'edit' => EditFolder::route('/{record}/edit'),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Folder')
                ->tabs([
                    Tab::make('Основное')
                        ->schema([
                            TextInput::make('title')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('sort')
                                ->numeric()
                                ->default(0),
                        ]),

                    Tab::make('Фон папки')
                        ->schema([
                            Select::make('background_media_id')
                                ->label('Фон (только фото)')
                                ->nullable()
                                ->searchable()
                                ->options(fn (): array => MediaFile::query()
                                    ->where('media_type', MediaFile::TYPE_IMAGE)
                                    ->latest()
                                    ->limit(200)
                                    ->get()
                                    ->mapWithKeys(fn (MediaFile $m): array => [
                                        (string) $m->id => $m->original_name ?: $m->path,
                                    ])
                                    ->toArray()),
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
                TextColumn::make('title')->sortable()->searchable(),
                TextColumn::make('slug')->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('backgroundMedia.path')
                    ->label('Фон')
                    ->disk('public')
                    ->height(36)
                    ->width(48),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ]);
    }

    public static function can(string $action, ?Model $record = null): bool
    {
        return true;
    }
}

