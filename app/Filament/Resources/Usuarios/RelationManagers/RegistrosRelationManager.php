<?php

namespace App\Filament\Resources\Usuarios\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;

class RegistrosRelationManager extends RelationManager
{
    protected static string $relationship = 'registros';

    protected static ?string $title = 'Historial de asistencias';

    protected static bool $isReadOnly = true;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('exportar')
                    ->label('Generar reporte')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(function ($livewire) {
                        $filtro = $livewire->getTableFilterState('rango_fechas');

                        return route('reportes.asistencias', [
                            'usuario' => $livewire->getOwnerRecord()->usuario,
                            'desde' => $filtro['desde'] ?? null,
                            'hasta' => $filtro['hasta'] ?? null,
                        ]);
                    })
                    ->openUrlInNewTab()
                    ->visible(function ($livewire) {
                        $filtro = $livewire->getTableFilterState('rango_fechas');

                        return filled($filtro['desde'] ?? null)
                            && filled($filtro['hasta'] ?? null);
                    }),
            ])
            ->deferLoading()
            ->emptyStateHeading('Selecciona un rango de fechas')
            ->filters([
                Filter::make('rango_fechas')
                    ->default([
                        'desde' => now()->subDays(7)->toDateString(),
                        'hasta' => now()->toDateString(),
                    ])
                    ->form([
                        DatePicker::make('desde')->label('Desde'),
                        DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['desde'] ?? null,
                                fn($q, $date) =>
                                $q->where('fechahora', '>=', \Carbon\Carbon::parse($date)->startOfDay())
                            )
                            ->when(
                                $data['hasta'] ?? null,
                                fn($q, $date) =>
                                $q->where('fechahora', '<=', \Carbon\Carbon::parse($date)->endOfDay())
                            );
                    }),
            ])
            ->columns([
                TextColumn::make('fechahora')->date('M j, Y H:i:s')
                    ->label('Fecha y Hora')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('equipo')
                    ->label('Equipo')
                    ->searchable(),

                TextColumn::make('tipo')
                    ->label('Método')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'teclado' => 'warning',
                        'huella' => 'success',
                        'justificante' => 'info',
                        default => 'gray',
                    }),


                ImageColumn::make('evidencia')->toggleable(isToggledHiddenByDefault: true)
                    ->label('Foto')
                    ->disk('servidor_capturas')

                    ->getStateUsing(function ($record) {
                        // Regla de negocio
                        if ($record->tipo !== 'teclado') {
                            return null;
                        }
                        $fecha = \Carbon\Carbon::parse($record->fechahora)
                            ->timezone('America/Mexico_City') 
                            ->format('Y-m-d_H-i-s');

                        $path = $record->usuario . '_' . $fecha . '.jpg';

                        // Verificamos existencia REAL
                        if (! Storage::disk('servidor_capturas')->exists($path)) {
                            return null;
                        }

                        return $path;
                    })
                    ->square()
                    ->size(60)
                    ->openUrlInNewTab(),

            ])
            ->defaultSort('fechahora', 'desc')
            ->paginated([5, 10, 25, 50]);
    }
}
