<?php

namespace App\Filament\Resources\Bloques\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BloqueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('anio')
                    ->label('Año')
                    ->options(function ($record) {
                        $currentYear = (int) date('Y');

                        // 2. Generamos las opciones base (Año actual + 2 siguientes)
                        // Usamos claves STRING para evitar problemas de tipos
                        $options = [
                            (string) $currentYear       => $currentYear,
                            (string) ($currentYear + 1) => $currentYear + 1,
                            (string) ($currentYear + 2) => $currentYear + 2,
                        ];

                        // 3. TRUCO: Si estamos editando ($record existe) y tiene un año guardado...
                        if ($record && $record->anio) {
                            $anioGuardado = (string) $record->anio;

                            // ...y ese año NO está en la lista que acabamos de crear...
                            if (!array_key_exists($anioGuardado, $options)) {
                                // ...lo agregamos manualmente para que se pueda ver.
                                $options[$anioGuardado] = $record->anio;
                            }
                        }

                        // 4. (Opcional) Ordenamos para que no salga desordenado si agregamos uno viejo
                        ksort($options);

                        return $options;
                    })
                    ->default(date('Y'))
                    ->required(),

                Select::make('ciclo')->options([
                    'A' => 'A',
                    'B' => 'B'
                ])->required(),

                TextInput::make('inicio'),
                TextInput::make('fin'),

                TextInput::make('bloque')->required(),
            ]);
    }
}
