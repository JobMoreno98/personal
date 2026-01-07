<?php

namespace App\Filament\Resources\Mensajes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MensajeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('usuario')->label('Usuario')
                    ->relationship('user', 'nombre', modifyQueryUsing: function ($query, $record) {
                        // 1. Iniciamos la consulta a la tabla de Mensajes
                        $queryMensajes = \App\Models\Mensaje::query();
                        if ($record) {
                            $queryMensajes->where('usuario', '!=', $record->usuario);
                        }
                        $usuariosOcupados = $queryMensajes->pluck('usuario')->toArray();
                        return $query->whereNotIn('usuario', $usuariosOcupados);
                    })
                    ->searchable()
                    ->preload()->required(),
                TextInput::make('veces')->numeric()->required(),
                Textarea::make('mensaje')->required()->autosize()->columnSpanFull()
            ]);
    }
}
