<?php

namespace App\Filament\Resources\Registros\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;

class RegistrosForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('user.nombre')
                    ->label('Usuario')
                    ->content(fn($record) => $record?->user?->nombre),
            ]);
    }
}
