<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteSettingResource\Pages\ListSiteSettings;
use App\Filament\Resources\SiteSettingResource\Pages\EditSiteSetting;
use App\Models\SiteSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Настройки сайта';

    protected static ?string $navigationGroup = 'Настройки';

    protected static ?int $navigationSort = 90;

    public static function getPages(): array
    {
        return [
            'index' => ListSiteSettings::route('/'),
            'edit' => EditSiteSetting::route('/{record}/edit'),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('header_title')
                ->label('Заголовок шапки')
                ->required()
                ->maxLength(200),

            Textarea::make('header_tagline')
                ->label('Подзаголовок шапки')
                ->rows(3)
                ->nullable(),

            FileUpload::make('header_background_path')
                ->label('Фон шапки (картинка)')
                ->image()
                ->nullable()
                ->disk('public')
                ->directory('site/header')
                ->acceptedFileTypes([
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'image/gif',
                    'image/svg+xml',
                ]),

            FileUpload::make('site_background_path')
                ->label('Фон всего сайта (картинка)')
                ->image()
                ->nullable()
                ->disk('public')
                ->directory('site/background')
                ->acceptedFileTypes([
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'image/gif',
                    'image/svg+xml',
                ]),

            TextInput::make('site_background_overlay_percent')
                ->label('Затемнение фона сайта')
                ->helperText('Процент чёрной «вуали» поверх фоновой картинки. 0 — без затемнения, 20 — как раньше по умолчанию.')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->default(20)
                ->suffix('%'),

            TextInput::make('home_hero_title')
                ->label('Заголовок блока на главной')
                ->maxLength(200)
                ->placeholder('Фото-видео альбом жизни'),

            Textarea::make('home_hero_text')
                ->label('Текст блока на главной')
                ->rows(3)
                ->placeholder('Посты внутри папок. Обложки и фон берутся только из фото.'),

            TextInput::make('home_meta_title')
                ->label('SEO title главной')
                ->maxLength(200)
                ->placeholder('Альбом жизни'),

            Textarea::make('home_meta_description')
                ->label('SEO description главной')
                ->rows(3)
                ->placeholder('Фото и видео из семейного архива.'),

            FileUpload::make('home_hero_background_path')
                ->label('Фон блока на главной (картинка)')
                ->image()
                ->nullable()
                ->disk('public')
                ->directory('site/home-hero')
                ->acceptedFileTypes([
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'image/gif',
                    'image/svg+xml',
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('header_title')
                    ->label('Заголовок шапки')
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

