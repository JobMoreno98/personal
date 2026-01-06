<?php

namespace App\Filament\Resources\Bloques\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BloquesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bloque'),
                TextColumn::make('inicio')->date('M j, Y'),
                TextColumn::make('fin')->date('M j, Y'),
                TextColumn::make('anio')->label('Año')->sortable()->searchable(),
                TextColumn::make('ciclo')->sortable()->searchable(),
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
