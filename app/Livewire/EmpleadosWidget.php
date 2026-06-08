<?php

namespace App\Livewire;

use App\Models\Instancias;
use App\Models\Usuarios;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class EmpleadosWidget extends TableWidget
{
    protected function getTableQuery(): Builder
    {
        return Usuarios::query();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('nombre')
                    ->label('Empleado')
                    ->searchable(),

                TextColumn::make('usuario')
                    ->label('Código')
                    ->searchable(),
            ])
            ->recordActions([
                Action::make('ver')
                    ->label('Ver')
                    ->action(function ($record) {
                        $this->dispatch('usuario-seleccionado', $record->usuario);
                    }),
            ])->filters([
                SelectFilter::make('departamento')
                    ->label('Instancia')
                    ->options(function () {
                        return Instancias::query()
                            ->distinct()
                            ->pluck('nombre', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->native(false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
