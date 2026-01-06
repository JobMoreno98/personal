<?php

namespace App\Filament\Resources\Horarios\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
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
                //
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
