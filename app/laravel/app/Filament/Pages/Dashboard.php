<?php

namespace App\Filament\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\Action;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?int $navigationSort = 2;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addAdmin')
                ->label('Добавить админа')
                ->icon('heroicon-o-user-plus')
                ->url(UserResource::getUrl('create')),
        ];
    }
}

