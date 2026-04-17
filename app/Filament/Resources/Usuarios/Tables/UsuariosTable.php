<?php

namespace App\Filament\Resources\Usuarios\Tables;

use App\Models\Instancias;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;


use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Infolist;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

class UsuariosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('exportar')
                    ->label('Generar reporte dia')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(function ($livewire) {

                        $departamento = $livewire->getTableFilterState('departamento');
                        $fecha        = $livewire->getTableFilterState('fecha_reporte')['fecha'] ?? null;

                        return route('reportes.asistencias-departamento', [
                            'departamento' => $departamento,
                            'fecha'        => $fecha,
                        ]);
                    })
                    ->openUrlInNewTab()
                    ->visible(
                        fn($livewire) =>
                        filled($livewire->getTableFilterState('departamento'))
                    ),

                Action::make('exportar')
                    ->label('Generar reporte periodo')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(function ($livewire) {

                        $departamento = $livewire->getTableFilterState('departamento');
                        $fecha_inicio        = $livewire->getTableFilterState('fecha_reporte')['fecha'] ?? null;
                        $fecha_fin        = $livewire->getTableFilterState('fecha_fin')['fecha'] ?? null;

                        return route('reportes.asistencias-departamento-periodo', [
                            'departamento' => $departamento,
                            'desde' => $fecha_inicio,
                            'hasta' => $fecha_fin
                        ]);
                    })
                    ->openUrlInNewTab()
                    ->visible(
                        fn($livewire) =>
                        filled($livewire->getTableFilterState('departamento'))
                    ),
                Action::make('exportar')
                    ->label('Generar reporte faltas')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(function ($livewire) {

                        $departamento = $livewire->getTableFilterState('departamento');
                        $fecha        = $livewire->getTableFilterState('fecha_reporte')['fecha'] ?? null;

                        return route('reportes.faltas-departamento', [
                            'departamento' => $departamento,
                            'fecha'        => $fecha,
                        ]);
                    })
                    ->openUrlInNewTab()
                    ->visible(
                        fn($livewire) =>
                        filled($livewire->getTableFilterState('departamento'))
                    ),

            ])
            ->openRecordUrlInNewTab()
            ->columns([
                TextColumn::make('usuario')->label('Código')->searchable()->sortable(),
                TextColumn::make('nombre')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('userType.descripcion')->label('Tipo')->searchable()->sortable(),
                TextColumn::make('instance.nombre')->label('Instancia')->searchable()->sortable()
            ])
            ->filters([
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

                Filter::make('fecha_reporte')
                    ->default([
                        'fecha' => now()->toDateString(),
                    ])
                    ->form([
                        DatePicker::make('fecha')
                            ->label('Fecha del reporte')
                            ->required(),
                    ]),
                Filter::make('fecha_fin')
                    ->default([
                        'fecha' => now()->toDateString(),
                    ])
                    ->form([
                        DatePicker::make('fecha')
                            ->label('Fecha del reporte')
                            ->required(),
                    ])
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
