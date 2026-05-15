<?php

namespace App\Filament\Resources\Eventos\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class EventoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tipo')->relationship('tipo_evento', 'nombre'),
                DatePicker::make('inicio'),
                DatePicker::make('fin'),
            ])->columns(3);
    }
}
