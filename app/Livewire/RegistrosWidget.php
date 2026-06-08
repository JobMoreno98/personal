<?php

namespace App\Livewire;

use App\Models\Registros;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;

class RegistrosWidget extends TableWidget
{
    public ?string $usuario = null;
    public ?array $rangoFechas = [];

    #[On('usuario-seleccionado')]
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
        $this->dispatch('$refresh');
        //dd($usuario);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => $this->getQuery())
            ->columns([
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('equipo')
                    ->label('Equipo'),

                TextColumn::make('fechahora')
                    ->label('Fecha')
                    ->dateTime(),

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
            ])->defaultSort('fechahora', 'desc')
            ->recordActions([])->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options(function () {
                        return Registros::query()
                            ->select('tipo')
                            ->distinct()
                            ->pluck('tipo', 'tipo')
                            ->toArray();
                    }),
                Filter::make('rango_fechas')
                    ->form([
                        DatePicker::make('desde'),
                        DatePicker::make('hasta'),
                    ])
                    ->query(function ($query, array $data) {

                        $this->rangoFechas = $data;

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
                    })
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    protected function getQuery(): Builder
    {
        $desde = $this->rangoFechas['desde']
            ?? now()->subDays(5)->startOfDay();

        $hasta = $this->rangoFechas['hasta']
            ?? now()->endOfDay();

        return Registros::query()
            ->when(
                $this->usuario,
                fn($q) => $q->where('usuario', $this->usuario),
                fn($q) => $q->whereRaw('1 = 0')
            )
            ->whereBetween('fechahora', [
                \Carbon\Carbon::parse($desde)->startOfDay(),
                \Carbon\Carbon::parse($hasta)->endOfDay(),
            ]);
    }
}
