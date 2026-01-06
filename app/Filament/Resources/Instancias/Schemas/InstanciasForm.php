<?php

namespace App\Filament\Resources\Instancias\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InstanciasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codigo')->required()->unique(),
                TextInput::make('nombre'),
                Select::make('jefe')
                    ->relationship('responsable', 'nombre')
                    ->searchable()
                    ->preload(),
            ]);
    }
}
