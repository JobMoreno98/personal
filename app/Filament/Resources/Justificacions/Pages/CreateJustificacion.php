<?php

namespace App\Filament\Resources\Justificacions\Pages;

use App\Filament\Resources\Justificacions\JustificacionResource;
use App\Models\Registros;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Log;

class CreateJustificacion extends CreateRecord
{
    protected static string $resource = JustificacionResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Crear Justificante';
    }

    protected function afterCreate(): void
    {
        $justificante = $this->record->refresh();
        $usuarioModel = $justificante->user;

        $todosLosHorarios = $usuarioModel->horarios;
        if ($todosLosHorarios->isEmpty()) {
            return;
        }

        $periodo = $justificante->periodo;

        // FIX #3: validar que el periodo y sus fechas existan antes de parsear
        if (!$periodo || !$periodo->fecha_inicial || !$periodo->fecha_final) {
            return; // O lanza una excepción/log si esto no debería pasar nunca
        }

        $inicio = Carbon::parse($periodo->fecha_inicial)->startOfDay();
        $fin = Carbon::parse($periodo->fecha_final)->startOfDay();

        // FIX #2: parseo robusto de "dias" sin depender de str_split sobre strings con comas
        $mapaHorarios = [];
        foreach ($todosLosHorarios as $h) {
            if (is_array($h->dias)) {
                $diasArr = $h->dias;
            } elseif (str_contains((string) $h->dias, ',')) {
                $diasArr = explode(',', (string) $h->dias);
            } else {
                $diasArr = str_split((string) $h->dias);
            }

            foreach ($diasArr as $dia) {
                $dia = trim((string) $dia);
                if ($dia === '') {
                    continue;
                }
                $mapaHorarios[$dia] = [
                    'entrada' => $h->entrada,
                    'salida' => $h->salida,
                ];
            }
        }

        DB::transaction(function () use ($inicio, $fin, $mapaHorarios, $usuarioModel) {

            // FIX #7: evitar N+1 — traemos todos los registros del periodo de una sola vez
            $registrosPorFecha = Registros::where('usuario', $usuarioModel->usuario)
                ->whereBetween('fechahora', [$inicio, $fin->copy()->endOfDay()])
                ->orderBy('fechahora', 'asc')
                ->get()
                ->groupBy(fn($r) => Carbon::parse($r->fechahora)->toDateString());

            for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {

                // FIX #1: CONFIRMAR esta convención contra cómo guardas "dias".
                // dayOfWeek de Carbon: Domingo=0 ... Sábado=6.
                // Aquí se asume que "dias" usa Domingo=1 ... Sábado=7.
                // Si tu convención es Lunes=1 ... Domingo=7 (ISO-8601), usa:
                //   $diaSemana = (string) $fecha->isoWeekday();
                $diaSemana = (string) ($fecha->dayOfWeek + 1);

                if (!isset($mapaHorarios[$diaSemana])) {
                    continue; // No trabaja este día
                }

                $horarioDelDia = $mapaHorarios[$diaSemana];

                // FIX #6: formato de hora inválido no debe tumbar toda la transacción
                try {
                    $horaEntradaTeorica = $fecha->copy()->setTimeFromTimeString($horarioDelDia['entrada']);
                    $horaSalidaTeorica = $fecha->copy()->setTimeFromTimeString($horarioDelDia['salida']);
                } catch (\Throwable $e) {
                    report($e); // o Log::warning(...)
                    continue;
                }

                $registrosExistentes = $registrosPorFecha->get($fecha->toDateString(), collect());

                $justificarEntrada = true;
                $justificarSalida = true;
                $registroEntradaReal = null;

                if ($registrosExistentes->isNotEmpty()) {
                    $primerRegistro = Carbon::parse($registrosExistentes->first()->fechahora);
                    $ultimoRegistro = Carbon::parse($registrosExistentes->last()->fechahora);

                    if ($primerRegistro->lte($horaEntradaTeorica->copy()->addMinutes(30))) {
                        $justificarEntrada = false;
                    } else {
                        $justificarEntrada = false;
                        $registroEntradaReal = $registrosExistentes->first();
                    }

                    // ¿Salió a su hora? (tolerancia 15 min antes)
                    if ($ultimoRegistro->gte($horaSalidaTeorica->copy()->subMinutes(30))) {
                        $justificarSalida = false;
                    }
                }

                if ($justificarEntrada) {
                    Registros::updateOrCreate(
                        ['usuario' => $usuarioModel->usuario, 'fechahora' => $horaEntradaTeorica],
                        ['tipo' => 'justificado', 'equipo' => 'JUSTIFICANTE_ENTRADA']
                    );
                } elseif ($registroEntradaReal) {
                    // Solo aplica si activaste la opción (b) de arriba
                    $registroEntradaReal->update([
                        'tipo' => 'justificado',
                        'equipo' => 'JUSTIFICANTE_ENTRADA',
                    ]);
                }

                if ($justificarSalida) {
                    Registros::updateOrCreate(
                        ['usuario' => $usuarioModel->usuario, 'fechahora' => $horaSalidaTeorica],
                        ['tipo' => 'justificado', 'equipo' => 'JUSTIFICANTE_SALIDA']
                    );
                }
            }
        });
    }
}
