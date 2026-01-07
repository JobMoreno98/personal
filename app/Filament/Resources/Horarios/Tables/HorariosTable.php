<?php

namespace App\Filament\Resources\Horarios\Tables;

use App\Models\HorarioMateria;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HorariosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('profesor.nombre'),
                TextColumn::make('materia'),
                TextColumn::make('crn')->label('CRN'),
                TextColumn::make('anio')->label('Año'),
                TextColumn::make('ciclo')->label('Ciclo'),
            ])
            ->filters([
                SelectFilter::make('anio')
                    ->label('Año')
                    ->options(function () {
                        // Busca todos los años únicos que existen en la tabla 'crn'
                        // Ajusta '\App\Models\Crn' si tu modelo se llama diferente
                        return HorarioMateria::query()
                            ->distinct()
                            ->pluck('anio', 'anio')
                            ->toArray();
                    })
                    ->searchable() // Permite escribir el año si hay muchos
                    ->native(false), // Mejora el diseño visual del select

                // FILTRO POR CICLO
                SelectFilter::make('ciclo')
                    ->label('Ciclo')
                    ->options([
                        'A' => 'A',
                        'B' => 'B',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
