<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;

class EditProfile extends \Filament\Pages\Auth\EditProfile
{
    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label('Имя')
            ->required()
            ->maxLength(255)
            ->autofocus();
    }
}

