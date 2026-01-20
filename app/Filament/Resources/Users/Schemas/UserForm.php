<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nombre'),
                TextInput::make('email')->label('Email'),
                TextInput::make('password')->label('Contraseña')->password()->autocomplete(false)->revealable()
                ->required(fn(string $operation): bool => $operation === 'create')
                    ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                    ->dehydrated(fn(?string $state): bool => filled($state)),

            ]);
    }
}