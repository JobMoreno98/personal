<?php

namespace App\Filament\Resources\Justificacions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JustificacionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('folio')->searchable()->sortable(),
                TextColumn::make('fechayhora')->date()->label('Fecha y hora')->searchable()->sortable(),
                TextColumn::make('user.nombre')->label('Usuario')->searchable()->sortable(),
                TextColumn::make('tipo.nombre')->searchable()->sortable(),
                TextColumn::make('periodo.fecha_inicial')->label('Inicio')->date()->searchable()->sortable(),
                TextColumn::make('periodo.fecha_final')->label('Fin')->date()->searchable()->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->icon('heroicon-m-pencil-square')
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //DeleteBulkAction::make(),
                ]),
            ]);
    }
}
