<?php

namespace App\Filament\Resources\Justificacions\Pages;

use App\Filament\Resources\Justificacions\JustificacionResource;
use App\Models\Registros;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\EditRecord;

class EditJustificacion extends EditRecord
{
    protected static string $resource = JustificacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(fn() => ! $this->record->aprobado),
        ];
    }

    protected function getActions(): array
    {
        return [
            EditAction::make()
                ->label('Guardar')
                ->hidden(fn() => $this->record->aprobado)
        ];
    }
    public function mount(int|string $record): void
    {
        parent::mount($record);
    }

    protected function beforeSave(): void
    {
        if ($this->record->aprobado) {
            abort(403); // bloqueo real al guardar
        }
        $justificante = $this->record;

        $usuario = $justificante->user->usuario;
        $periodo = $justificante->periodo;

        if (! $periodo) {
            return;
        }

        $inicio = Carbon::parse($periodo->fecha_inicial)->startOfDay();
        $fin    = Carbon::parse($periodo->fecha_final)->endOfDay();

        Registros::where('usuario', $usuario)
            ->where('tipo', 'justificado')
            ->whereBetween('fechahora', [$inicio, $fin])
            ->delete();
    }
    protected function afterSave(): void
    {
        $justificante = $this->record;

        $usuario = $justificante->user->usuario;
        $periodo = $justificante->periodo;
        $horario = $justificante->user->horario;

        if (! $periodo || ! $horario) {
            return;
        }

        $diasLaborales = $horario->dias;
        $horaEntrada   = $horario->hora_entrada;
        $horaSalida    = $horario->hora_salida;

        $inicio = Carbon::parse($periodo->fecha_inicial)->startOfDay();
        $fin    = Carbon::parse($periodo->fecha_final)->startOfDay();

        for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {

            $diaSemana = (string) $fecha->dayOfWeekIso;

            if (! in_array($diaSemana, $diasLaborales)) {
                continue;
            }

            // Entrada
            Registros::firstOrCreate([
                'usuario'   => $usuario,
                'fechahora' => Carbon::parse($fecha->format('Y-m-d') . ' ' . $horaEntrada),
                'tipo'      => 'justificado',
            ], [
                'equipo' => 'JUSTIFICANTE',
            ]);

            // Salida
            Registros::firstOrCreate([
                'usuario'   => $usuario,
                'fechahora' => Carbon::parse($fecha->format('Y-m-d') . ' ' . $horaSalida),
                'tipo'      => 'justificado',
            ], [
                'equipo' => 'JUSTIFICANTE',
            ]);
        }
    }
}
