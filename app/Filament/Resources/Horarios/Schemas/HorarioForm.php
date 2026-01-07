<?php

namespace App\Filament\Resources\Horarios\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule; // Importa esto al inicio de tu archivo
use Filament\Forms\Get;

class HorarioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('usuario')
                    ->relationship('profesor', 'nombre')
                    ->searchable()->live()
                    ->preload(),
                Select::make('materia')
                    ->relationship('asignatura', 'nombre')->required()
                    ->searchable()->live()
                    ->preload(),
                TextInput::make('crn')->label('CRN')->rule(function ($get, $record) {
                    // Creamos una regla de unicidad compuesta
                    return Rule::unique('crn', 'crn') // Tabla 'crn', columna 'crn'
                        ->where('usuario', $get('usuario'))
                        ->where('materia', $get('materia'))
                        ->where('anio', $get('anio'))
                        ->where('ciclo', $get('ciclo'))
                        ->ignore($record?->id);
                })
                    // Mensaje personalizado para que el usuario entienda qué pasó
                    ->validationAttribute('CRN') // Para que diga "El campo CRN..."
                    ->validationMessages([
                        'unique' => 'Ya existe un registro con este CRN, Profesor, Materia y Ciclo idénticos.',
                    ]),
                Select::make('anio')
                    ->label('Año')
                    ->options(function () {
                        $year = (int) date('Y');

                        return [
                            $year => $year,
                            $year + 1 => $year + 1,
                            $year + 2 => $year + 2,
                        ];
                    })
                    ->default(date('Y'))
                    ->required(),

                Select::make('ciclo')->options([
                    'A' => 'A',
                    'B' => 'B',
                ])->required(),

                Repeater::make('horarios') // El nombre de la relación definida en el Modelo
                    ->relationship('horarios')
                    ->label('Horarios de Clase')
                    ->schema([
                        Hidden::make('anio')->default(fn($get) => $get('../../anio')),
                        Hidden::make('ciclo')->default(fn($get) => $get('../../ciclo')),
                        \Filament\Forms\Components\Hidden::make('crn')
                            ->default(fn($get) => $get('../../crn')),

                        Select::make('bloque')->options([
                            '1' => '1',
                            '2' => '2',
                            '0' => '1 y 2'
                        ])->required()->live(),
                        Select::make('dia')
                            ->label('Día')
                            ->options([
                                'LUNES' => 'LUNES',
                                'MARTES' => 'MARTES',
                                'MIERCOLES' => 'MIERCOLES',
                                'JUEVES' => 'JUEVES',
                                'VIERNES' => 'VIERNES',
                                'SABADO' => 'SABADO',
                            ])
                            ->required()->live(),
                        TextInput::make('aula')->required()->rule(function ($get, $record) {
                            // Validamos que el aula no esté ocupada ese día a esa hora
                            // EN CUALQUIER OTRO CURSO
                            return Rule::unique('horarioscrn', 'aula') // Buscamos en la tabla de horarios
                                ->where('bloque', $get('bloque'))
                                ->where('hora', $get('hora'))
                                ->where('dia', $get('dia'))

                                ->where('anio', $get('../../anio'))
                                ->where('ciclo', $get('../../ciclo'))

                                ->ignore($record?->id);
                        })
                            ->validationAttribute('Aula')
                            ->validationMessages([
                                'unique' => 'El aula está ocupada en este horario y bloque (incluso en otro curso).',
                            ]),

                        TimePicker::make('hora')
                            ->label('Inicia')
                            ->seconds(false) // Ocultar segundos si no son necesarios
                            ->required()->live(), // Validación: Fin debe ser después de Inicio
                    ])
                    ->columns(4) // Para que los 3 campos salgan en una sola fila
                    ->addActionLabel('Agregar horario')
                    ->defaultItems(1)
                    ->grid(1)->columnSpanFull(),
            ]);
    }
}
