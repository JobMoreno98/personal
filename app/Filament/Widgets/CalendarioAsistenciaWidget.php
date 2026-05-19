<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use App\Models\Usuarios;
use App\Models\Registros;
use App\Models\Evento;
use App\Models\TipoEvento;
use App\Models\Justificacion;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Schemas\Components\Grid as ComponentsGrid;
// NUEVOS IMPORTS PARA LAS ACCIONES
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Illuminate\Support\Facades\DB;

class CalendarioAsistenciaWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    public ?Model $record = null;
    protected static bool $isDiscovered = false;
    protected int | string | array $columnSpan = 'full';
    protected string $view = 'filament.widgets.calendario-asistencia-widget';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'fechaInicio' => Carbon::now()->setTimezone('America/Mexico_City')->startOfMonth()->format('Y-m-d'),
            'fechaFin'    => Carbon::now()->setTimezone('America/Mexico_City')->endOfMonth()->format('Y-m-d'),
        ]);
    }

    public function exportarAsistenciasAction(): Action
    {
        return Action::make('exportarAsistencias')
            ->label('Reporte por periodo')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->url(fn() => route('reportes.asistencias', [
                // Tomamos al usuario directamente del récord actual
                'usuario' => $this->record->usuario,
                // Tomamos las fechas del formulario del widget
                'desde' => $this->data['fechaInicio'] ?? null,
                'hasta' => $this->data['fechaFin'] ?? null,
            ]))
            ->openUrlInNewTab()
            ->visible(fn() => filled($this->data['fechaInicio'] ?? null) && filled($this->data['fechaFin'] ?? null));
    }

    public function exportarFaltasAction(): Action
    {
        return Action::make('exportarFaltas')
            ->label('Faltas por periodo')
            ->icon('heroicon-o-arrow-down-tray')
            // Le pongo color danger (rojo) para diferenciarlo de las asistencias, pero puedes regresarlo a 'success' si prefieres
            ->color('danger')
            ->url(fn() => route('reportes.faltas', [
                'usuario' => $this->record->usuario,
                'desde' => $this->data['fechaInicio'] ?? null,
                'hasta' => $this->data['fechaFin'] ?? null,
            ]))
            ->openUrlInNewTab()
            ->visible(fn() => filled($this->data['fechaInicio'] ?? null) && filled($this->data['fechaFin'] ?? null));
    }

    public function form($form)
    {
        return $form
            ->schema([
                ComponentsGrid::make(2)->schema([
                    DatePicker::make('fechaInicio')
                        ->label('Desde')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->live(),
                    DatePicker::make('fechaFin')
                        ->label('Hasta')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->live(),
                ])
            ])
            ->statePath('data');
    }

    protected function getViewData(): array
    {
        $usuario = $this->record;

        if (!$usuario) return ['error' => 'Widget solo para perfiles.'];

        $usuario->loadMissing('horarios');
        if ($usuario->horarios->isEmpty()) return ['error' => 'Sin horarios asignados.'];

        $fechaInicioStr = $this->data['fechaInicio'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFinStr    = $this->data['fechaFin'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $inicio = Carbon::parse($fechaInicioStr)->setTimezone('America/Mexico_City')->startOfDay();
        $fin    = Carbon::parse($fechaFinStr)->setTimezone('America/Mexico_City')->endOfDay();

        if ($inicio->gt($fin)) return ['error' => 'La fecha inicial no puede ser mayor a la final.'];
        if ($inicio->diffInDays($fin) > 60) return ['error' => 'Rango máximo permitido de 60 días.'];

        $usuarioId = $usuario->usuario;

        // 1. EXTRAER REGISTROS
        $registros = Registros::where('usuario', $usuarioId)
            ->whereBetween('fechahora', [
                $inicio->copy()->setTimezone('UTC')->format('Y-m-d H:i:s'),
                $fin->copy()->setTimezone('UTC')->format('Y-m-d H:i:s')
            ])
            ->orderBy('fechahora')
            ->get()
            ->groupBy(function ($registro) {
                return Carbon::parse($registro->fechahora)->setTimezone('America/Mexico_City')->format('Y-m-d');
            });

        // 2. MAPEO DE HORARIOS
        $mapaHorarios = [];
        $todosLosDiasLaborales = [];
        foreach ($usuario->horarios as $bloque) {
            $dias = is_array($bloque->dias) ? $bloque->dias : str_split($bloque->dias);
            foreach ($dias as $dia) {
                $mapaHorarios[$dia] = ['entrada' => $bloque->entrada, 'salida' => $bloque->salida];
                $todosLosDiasLaborales[] = $dia;
            }
        }
        $todosLosDiasLaborales = array_unique($todosLosDiasLaborales);

        // =========================================================================
        // AQUI ESTA LA MAGIA: CARGAMOS TODO A MEMORIA RAM PARA EVITAR N+1
        // =========================================================================
        $periodo = CarbonPeriod::create($inicio, $fin);

        // A) Eventos precargados
        $eventos = Evento::with('tipo_evento')
            ->whereDate('inicio', '<=', $periodo->last())
            ->whereDate('fin', '>=', $periodo->first())
            ->get();

        $eventosPorFecha = $eventos->flatMap(function ($evento) {
            $evInicio = Carbon::parse($evento->inicio)->startOfDay();
            $evFin    = Carbon::parse($evento->fin)->startOfDay();
            return collect(CarbonPeriod::create($evInicio, $evFin))->map(function ($fecha) use ($evento) {
                return ['fecha' => $fecha->format('Y-m-d'), 'tipo' => $evento->tipo_evento->nombre ?? 'Festivo'];
            });
        })->groupBy('fecha');

        // B) Tipos de eventos (para buscar festivos en memoria)
        $tiposEventosFestivos = TipoEvento::pluck('nombre');

        // C) Justificaciones precargadas (Adios al WhereHas)
        $justificacionesUsuario = Justificacion::with(['tipo', 'periodo'])
            ->where('usuario', $usuarioId)
            ->get();

        $calendario = [];
        $minutosTolerancia = 30;

        foreach ($periodo as $fecha) {
            $fechaActual = $fecha->copy();
            $fechaStr    = $fechaActual->format('Y-m-d');
            $datosDia    = $registros->get($fechaStr);
            $numeroDiaSemana = (string) ($fechaActual->dayOfWeek + 1);

            $horarioEntradaStr = $mapaHorarios[$numeroDiaSemana]['entrada'] ?? null;
            $horarioSalidaStr  = $mapaHorarios[$numeroDiaSemana]['salida'] ?? null;

            $esDiaLaboral = $this->esDiaLaboral($fechaActual, $todosLosDiasLaborales);
            $tipoEvento = $eventosPorFecha->get($fechaStr)?->first()['tipo'] ?? null;

            // Validar festivo sin ir a BD
            $esFestivo = [$tiposEventosFestivos->contains($tipoEvento), $tipoEvento];

            [$estado, $color, $detalle] = $this->resolverEstadoDia(
                $fechaActual,
                $datosDia,
                $esDiaLaboral,
                $horarioEntradaStr,
                $horarioSalidaStr,
                $minutosTolerancia,
                $esFestivo,
                $justificacionesUsuario // Pasamos la colección a memoria
            );

            $calendario[] = [
                'dia'        => $fechaActual->day,
                'mes_nombre' => substr($fechaActual->locale('es')->monthName, 0, 3),
                'fecha_str'  => $fechaStr,
                'estado'     => str_replace('<br/>', ' - ', $estado),
                'color'      => $color,
                'detalle'    => $detalle,
                'es_laboral' => $esDiaLaboral,
            ];
        }

        return ['calendario' => $calendario, 'usuario' => $usuario];
    }

    // =========================================================================
    // MÉTODOS AUXILIARES OPTIMIZADOS
    // =========================================================================
    private function esDiaLaboral(Carbon $fecha, array $diasLaborales): bool
    {
        return in_array((string) ($fecha->dayOfWeek + 1), $diasLaborales, true);
    }

    // Recibe $justificacionesPrecargadas en vez de hacer queries
    private function resolverEstadoDia(
        Carbon $fecha,
        $datosDia,
        bool $esDiaLaboral,
        ?string $horarioEntradaStr,
        ?string $horarioSalidaStr,
        int $minutosTolerancia,
        array $esFestivo,
        $justificacionesPrecargadas
    ): array {
        if ($esFestivo[0]) return [$esFestivo[1], '#6f42c1', null];

        if ($this->esJustificado($datosDia, $fecha, $justificacionesPrecargadas)) {

            $nombreJustificacion = 'Justificado';

            $justificacionMatch = $justificacionesPrecargadas->first(function ($just) use ($fecha) {
                if (!$just->periodo) return false;
                $fInicio = Carbon::parse($just->periodo->fecha_inicial)->startOfDay();
                $fFin = Carbon::parse($just->periodo->fecha_final)->endOfDay();
                return $fecha->between($fInicio, $fFin);
            });

            if ($justificacionMatch && $justificacionMatch->tipo) {
                $nombreJustificacion = $justificacionMatch->tipo->nombre;
            }

            return ['Justificado: ' . $nombreJustificacion, '#0dcaf0', null];
        }

        $fueraTiempo = null;
        if ($datosDia) {
            if (is_null($horarioEntradaStr) || is_null($horarioSalidaStr)) {
                $horarioEntradaStr = explode(" ", $datosDia->first()->fechahora)[1];
                $horarioSalidaStr = explode(" ", $datosDia->last()->fechahora)[1];
                $fueraTiempo = true;
            }
            return $this->evaluarAsistencia($fecha, $datosDia, $horarioEntradaStr, $horarioSalidaStr, $minutosTolerancia, $fueraTiempo);
        }

        if ($datosDia && $datosDia->count() === 1) return ['Error', '#fd7e14', null];
        return $this->evaluarDiaSinRegistro($fecha, $esDiaLaboral);
    }

    private function evaluarAsistencia(Carbon $fecha, $datosDia, string $horarioEntradaStr, string $horarioSalidaStr, int $minutosTolerancia, $fueraTiempo): array
    {
        $entradaReal = Carbon::parse($datosDia->first()->fechahora)->timezone('America/Mexico_City');
        $salidaReal = Carbon::parse($datosDia->last()->fechahora)->timezone('America/Mexico_City');

        $entradaIdeal = Carbon::parse($fecha->format('Y-m-d') . ' ' . $horarioEntradaStr);
        $salidaIdeal = Carbon::parse($fecha->format('Y-m-d') . ' ' . $horarioSalidaStr);

        // Calculamos la diferencia en segundos entre los dos registros
        $diferenciaSegundos = $entradaReal->diffInSeconds($salidaReal);

        $salidaParaMostrar = $salidaReal->format('H:i:s');

        // Si la diferencia es de 5 minutos (300 segundos) o menos
        if ($diferenciaSegundos <= 300) {
            $salidaParaMostrar = '--:--:--';
        }

        // Lógica original de estados (Retardo, Salida Anticipada, Asistencia)
        if ($entradaReal->gt($entradaIdeal->copy()->addMinutes($minutosTolerancia))) {
            $estado = 'Retardo';
            $color = '#e0a800';
        } elseif ($salidaParaMostrar !== '--:--:--' && $salidaReal->lt($salidaIdeal)) {
            $estado = 'Salida Anticipada';
            $color = '#17a2b8';
        } else {
            $estado = 'Asistencia';
            $color = '#28a745';
        }

        // Si la salida se marcó como inválida, el estado debería reflejar que falta la salida real
        if ($salidaParaMostrar === '--:--:--' && !$fueraTiempo) {
            $estado = 'Falta Salida';
            $color = '#fd7e14'; // Naranja para advertencia
        }

        if ($fueraTiempo) {
            $estado = 'Registro en día de descanso';
            $color = '#198754';
        }

        return [
            $estado,
            $color,
            [
                'entrada' => $entradaReal->format('H:i:s'),
                'salida' => $salidaParaMostrar, // Aquí pasamos el valor validado
                'tiempo' => $entradaReal->diff($salidaReal)->format('%H:%I:%S')
            ]
        ];
    }

    private function evaluarDiaSinRegistro(Carbon $fecha, bool $esDiaLaboral): array
    {
        if (!$esDiaLaboral) return ['DESCANSO', '#f3f4f6', null];
        if ($fecha->lt(Carbon::today())) return ['FALTA', '#dc3545', null];
        if ($fecha->isToday()) return ['EN CURSO', '#6c757d', null];
        return ['PENDIENTE', 'transparent', null];
    }

    private function esJustificado($datosDia, $fecha, $justificacionesPrecargadas): bool
    {
        $fecha = $fecha->format('Y-m-d');

        //$justificante = DB::table('justificaciones')->where('fecha_inicial', '>=', $fecha)->where('fecha_final', '<=', $fecha)->exists();

        //dd( $justificacionesPrecargadas);

        $justificante = collect($justificacionesPrecargadas)->contains(function ($j) use ($fecha) {
            //dd($j->periodo);
            return $fecha >= $j->periodo->fecha_inicial && $fecha <= $j->periodo->fecha_final;
        });

        return ($datosDia && $datosDia->contains(fn($r) => $r->tipo === 'justificado')) || $justificante;
    }
}
