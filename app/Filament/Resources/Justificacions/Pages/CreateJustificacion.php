<?php

namespace App\Filament\Resources\Justificacions\Pages;

use App\Filament\Resources\Justificacions\JustificacionResource;
use App\Models\Registros;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateJustificacion extends CreateRecord
{
    protected static string $resource = JustificacionResource::class;


    protected function afterCreate(): void
    {
        $justificante = $this->record;

        $usuario = $justificante->user->usuario;

        $periodo = $justificante->periodo;
        if (! $periodo) {
            return;
        }

        // Horario del usuario
        $horario = $justificante->user->horario;
        if (! $horario) {
            return;
        }

        $diasLaborales = $horario->dias; // ej. ['1','2','3','4','5']
        $horaEntrada   = $horario->entrada; // '08:00'
        $horaSalida    = $horario->salida;  // '16:00'

        $inicio = Carbon::parse($periodo->fecha_inicial)->startOfDay();
        $fin    = Carbon::parse($periodo->fecha_final)->startOfDay();

        for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {

            // ISO: lunes=1, domingo=7
            $diaSemana = (string) $fecha->dayOfWeekIso;

            // ¿Es día laboral?
            if (! in_array($diaSemana, $diasLaborales)) {
                continue;
            }

            // 🔹 Entrada
            Registros::firstOrCreate([
                'usuario'   => $usuario,
                'fechahora' => Carbon::parse(
                    $fecha->format('Y-m-d') . ' ' . $horaEntrada
                ),
                'tipo'      => 'justificado',
            ], [
                'equipo' => 'JUSTIFICANTE',
            ]);

            // 🔹 Salida
            Registros::firstOrCreate([
                'usuario'   => $usuario,
                'fechahora' => Carbon::parse(
                    $fecha->format('Y-m-d') . ' ' . $horaSalida
                ),
                'tipo'      => 'justificado',
            ], [
                'equipo' => 'JUSTIFICANTE',
            ]);
        }
    }
}
