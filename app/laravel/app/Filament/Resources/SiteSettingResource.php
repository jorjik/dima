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
            \Filament\Forms\Components\Tabs::make('Settings')
                ->tabs([
                    \Filament\Forms\Components\Tabs\Tab::make('Основное')
                        ->schema([
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
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml']),

                            TextInput::make('header_background_opacity')
                                ->label('Непрозрачность фона шапки')
                                ->helperText('Процент чёрного фона (0-100). По умолчанию 25.')
                                ->numeric()
                                ->minValue(0)->maxValue(100)->default(25)->suffix('%'),
                        ]),

                    \Filament\Forms\Components\Tabs\Tab::make('Герой и Фон')
                        ->schema([
                            TextInput::make('home_hero_title')
                                ->label('Заголовок Hero-блока')
                                ->maxLength(200),

                            Textarea::make('home_hero_text')
                                ->label('Текст Hero-блока')
                                ->rows(3),

                            FileUpload::make('home_hero_background_path')
                                ->label('Фон Hero-блока')
                                ->image()
                                ->nullable()
                                ->disk('public')
                                ->directory('site/home-hero'),

                            TextInput::make('hero_background_opacity')
                                ->label('Непрозрачность фона Hero')
                                ->numeric()->minValue(0)->maxValue(100)->default(55)->suffix('%'),

                            FileUpload::make('site_background_path')
                                ->label('Фон всего сайта')
                                ->image()
                                ->nullable()
                                ->disk('public')
                                ->directory('site/background'),

                            TextInput::make('site_background_overlay_percent')
                                ->label('Затемнение фона сайта')
                                ->numeric()->minValue(0)->maxValue(100)->default(20)->suffix('%'),
                        ]),

                    \Filament\Forms\Components\Tabs\Tab::make('Социальные сети')
                        ->schema([
                            TextInput::make('social_telegram')
                                ->label('Telegram')
                                ->placeholder('https://t.me/...'),

                            TextInput::make('social_vk')
                                ->label('VK')
                                ->placeholder('https://vk.com/...'),

                            TextInput::make('social_instagram')
                                ->label('Instagram')
                                ->placeholder('https://www.instagram.com/...'),

                            TextInput::make('social_youtube')
                                ->label('YouTube')
                                ->placeholder('https://www.youtube.com/...'),

                            TextInput::make('social_whatsapp')
                                ->label('WhatsApp')
                                ->placeholder('https://wa.me/...'),
                        ]),

                    \Filament\Forms\Components\Tabs\Tab::make('SEO')
                        ->schema([
                            TextInput::make('home_meta_title')
                                ->label('SEO Title (Home)')
                                ->maxLength(200),

                            Textarea::make('home_meta_description')
                                ->label('SEO Description (Home)')
                                ->rows(3),
                        ]),
                ])->columnSpanFull(),
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

