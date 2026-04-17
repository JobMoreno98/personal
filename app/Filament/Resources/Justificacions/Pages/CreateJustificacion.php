<?php

namespace App\Filament\Resources\Justificacions\Pages;

use App\Filament\Resources\Justificacions\JustificacionResource;
use App\Models\Registros;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateJustificacion extends CreateRecord
{
    protected static string $resource = JustificacionResource::class;

    protected function afterCreate(): void
    {
        $justificante = $this->record->refresh();
        $usuarioModel = $justificante->user;

        // 1. Obtener todos los horarios (es una colección por el Repeater)
        $todosLosHorarios = $usuarioModel->horarios;

        if ($todosLosHorarios->isEmpty()) {
            return;
        }

        $periodo = $justificante->periodo;
        $inicio = Carbon::parse($periodo->fecha_inicial)->startOfDay();
        $fin = Carbon::parse($periodo->fecha_final)->startOfDay();

        // 2. Crear un mapa para búsqueda rápida: [día_semana => ['entrada' => X, 'salida' => Y]]
        $mapaHorarios = [];
        foreach ($todosLosHorarios as $h) {
            // 'dias' puede ser array (si Laravel lo castea) o string "2345"
            $diasArr = is_array($h->dias) ? $h->dias : str_split((string) $h->dias);

            foreach ($diasArr as $dia) {
                $mapaHorarios[(string) $dia] = [
                    'entrada' => $h->entrada,
                    'salida' => $h->salida,
                ];
            }
        }

        for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
            //dd((string) $fecha->dayOfWeekIso + 1);
            $diaSemana = (string) $fecha->dayOfWeekIso + 1;

            // ¿El empleado trabaja este día según su mapa de horarios?
            if (!isset($mapaHorarios[$diaSemana])) {
                continue;
            }

            $horarioDelDia = $mapaHorarios[$diaSemana];
            //dd($mapaHorarios, $diaSemana);

            // Crear Entrada
            $entrada = Registros::updateOrCreate(
                [
                    'usuario' => $usuarioModel->usuario,
                    'fechahora' => $fecha->copy()->setTimeFromTimeString($horarioDelDia['entrada']),
                    'tipo' => 'justificado',
                ],
                [
                    'equipo' => 'JUSTIFICANTE',
                ],
            );

            // Crear Salida
            $salida = Registros::updateOrCreate(
                [
                    'usuario' => $usuarioModel->usuario,
                    'fechahora' => $fecha->copy()->setTimeFromTimeString($horarioDelDia['salida']),
                    'tipo' => 'justificado',
                ],
                [
                    'equipo' => 'JUSTIFICANTE',
                ],
            );
        }
    }
}
