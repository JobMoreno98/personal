<?php

namespace App\Filament\Resources\Horarios\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class HorarioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('usuario')
                    ->relationship('profesor', 'nombre')
                    ->searchable()
                    ->preload(),
                Select::make('materia')
                    ->relationship('asignatura', 'nombre')
                    ->searchable()
                    ->preload(),
                TextInput::make('crn')->label('CRN')->unique(),
                Select::make('anio')
                    ->label('Año')
                    ->options(function () {
                        $year = (int) date('Y');
                        return [
                            $year => $year,           // 2026
                            $year + 1 => $year + 1,   // 2027
                            $year + 2 => $year + 2,   // 2028
                        ];
                    })
                    ->default(date('Y'))
                    ->required(),

                Select::make('ciclo')->options([
                    'A' => 'A',
                    'B' => 'B'
                ])->required(),
                Repeater::make('horarios') // El nombre de la relación definida en el Modelo
                    ->relationship('horarios')
                    ->label('Horarios de Clase')
                    ->schema([
                        Select::make('dia')
                            ->label('Día')
                            ->options([
                                'LUNES' => 'LUNES',
                                'MARTES' => 'Martes',
                                'MIERCOLES' => 'Miércoles',
                                'JUEVES' => 'Jueves',
                                'VIERNES' => 'VIERNES',
                                'SABADO' => 'Sábado',
                            ])
                            ->required(),
                        TextInput::make('aula')->required(),

                        TimePicker::make('hora')
                            ->label('Inicia')
                            ->seconds(false) // Ocultar segundos si no son necesarios
                            ->required(), // Validación: Fin debe ser después de Inicio
                    ])
                    ->columns(3) // Para que los 3 campos salgan en una sola fila
                    ->addActionLabel('Agregar horario')
                    ->defaultItems(1)
                    ->grid(1)->columnSpanFull(),
            ]);
    }
}
