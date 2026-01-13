<?php

namespace App\Filament\Resources\Registros\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RegistrosForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('usuario')->readOnly()
            ]);
    }
}
