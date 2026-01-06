<?php

namespace App\Filament\Resources\Materias\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MateriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codigo'),
                TextInput::make('nombre'),
                Select::make('departamento')
                    ->relationship('instance', 'nombre')
                    ->searchable()
                    ->preload(),
            ]);
    }
}
