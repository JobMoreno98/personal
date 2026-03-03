<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Horario;
use App\Models\Instancias;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Registros;
use App\Models\TipoEvento;
use App\Models\Usuarios;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonPeriod;

class ReporteAsistenciasController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'usuario' => ['required', 'integer'],
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date'],
        ]);

        $usuario = Usuarios::with(['horarios'])->where('usuario', $request->usuario)->first();

        if (!$usuario) {
            abort(404);
        }

        // 1. Obtenemos TODOS los bloques de horarios del usuario
        $horarios_usuario = Horario::where('usuario', $request->usuario)->get();

        if ($horarios_usuario->isEmpty()) {
            abort(404);
        }

        $inicio = $request->desde ? Carbon::parse($request->desde) : Carbon::now()->startOfMonth();
        $fin    = $request->hasta ? Carbon::parse($request->hasta) : Carbon::now()->endOfMonth();

        $registros = Registros::where('usuario', $request->usuario)
            ->whereBetween('fechahora', [
                $inicio->copy()->startOfDay()->setTimezone('UTC'),
                $fin->copy()->endOfDay()->setTimezone('UTC')
            ])
            ->orderBy('fechahora')
            ->get()
            ->groupBy(function ($registro) {
                return Carbon::parse($registro->fechahora)
                    ->setTimezone('America/Mexico_City')
                    ->format('Y-m-d');
            });

        // 2. Creamos un mapa de horarios. 
        // Llave = Día de la semana (1 a 7), Valor = Arreglo con entrada y salida
        $mapaHorarios = [];
        $todosLosDiasLaborales = [];

        foreach ($horarios_usuario as $bloque) {
            // Como tu mutador devuelve un array o un string, aseguramos que sea un array iterable
            $dias = is_array($bloque->dias) ? $bloque->dias : str_split($bloque->dias);

            foreach ($dias as $dia) {
                $mapaHorarios[$dia] = [
                    'entrada' => $bloque->entrada,
                    'salida'  => $bloque->salida
                ];
                $todosLosDiasLaborales[] = $dia;
            }
        }

        // Limpiamos duplicados por si acaso
        $todosLosDiasLaborales = array_unique($todosLosDiasLaborales);

        $periodo = CarbonPeriod::create($inicio, $fin);
        $calendario = [];
        $minutosTolerancia = 40;

        $eventos = Evento::whereDate('inicio', '<=', $periodo->last())
            ->whereDate('fin', '>=', $periodo->first())
            ->get();

        $eventosPorFecha = $eventos->flatMap(function ($evento) {
            $inicio = Carbon::parse($evento->inicio)->startOfDay();
            $fin    = Carbon::parse($evento->fin)->startOfDay();

            return collect(CarbonPeriod::create($inicio, $fin))->map(function ($fecha) use ($evento) {
                return [
                    'fecha' => $fecha->format('Y-m-d'),
                    'tipo'  => $evento->tipo_evento->nombre,
                ];
            });
        })->groupBy('fecha');

        foreach ($periodo as $fecha) {
            $fechaActual = $fecha->copy();
            $fechaStr    = $fechaActual->format('Y-m-d');
            $nombreMes   = ucfirst($fechaActual->locale('es')->monthName) . ' ' . $fechaActual->year;
            $datosDia    = $registros->get($fechaStr);

            // 3. Determinar el día de la semana actual 
            // Carbon dayOfWeek: 0 (Dom) a 6 (Sab). Tu formato: 1 (Dom) a 7 (Sab).
            // Sumamos 1 para que empate con las llaves de tu CheckboxList
            $numeroDiaSemana = (string) ($fechaActual->dayOfWeek + 1);

            // 4. Extraer la entrada y salida Específica para este día
            $horarioEntradaStr = $mapaHorarios[$numeroDiaSemana]['entrada'] ?? null;
            $horarioSalidaStr  = $mapaHorarios[$numeroDiaSemana]['salida'] ?? null;

            // Mantenemos tu función original pasándole todos los días que trabaja en la semana
            $esDiaLaboral = $this->esDiaLaboral($fechaActual, $todosLosDiasLaborales);

            $tipoEvento    = $this->obtenerEventoDelDia($fechaActual, $eventosPorFecha);
            $esFestivo     = $this->esFestivo($tipoEvento);
            $esJustificado = $this->esJustificado($datosDia);

            // 5. Pasamos las horas específicas al resolverEstadoDia
            [$estado, $color, $detalle] = $this->resolverEstadoDia(
                $fechaActual,
                $datosDia,
                $esDiaLaboral,
                $horarioEntradaStr,
                $horarioSalidaStr,
                $minutosTolerancia,
                $esFestivo
            );

            $calendario[$nombreMes][] = [
                'fecha'      => $fechaActual,
                'estado'     => $estado,
                'color'      => $color,
                'detalle'    => $detalle,
                'es_laboral' => $esDiaLaboral,
                'festivo'    => $esFestivo[0] ?? false
            ];
        }

        $periodo = [$request->desde, $request->hasta];
        $html = view('reportes.asistencias-usuario', compact('calendario', 'usuario', 'periodo', 'registros'));

        $pdf = Pdf::loadHtml($html->render())->setPaper('letter', 'portrait')
            ->setOptions([
                'defaultFont' => 'Montserrat',
                'isRemoteEnabled' => true,
                'isFontSubsettingEnabled' => true,
            ]);

        $pdf->output();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->get_canvas();
        $width = $canvas->get_width();
        $x_center = ($width / 2) - 50;

        $canvas->page_text($x_center, 750, "Parres Arias No. 150 Los Belenes C.P. 45132.", null, 8, [0, 0, 0]);
        $canvas->page_text(100, 760, "www.cucsh.udg.mx", null, 11, "#7D91BE");
        $canvas->page_text($x_center, 760, "Zapopan, Jalisco, México.   Tel. +52 (33) 38193300 Ext. 23700", null, 8, [0, 0, 0]);
        $canvas->page_text($x_center, 770, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 8, [0, 0, 0]);

        return $pdf->stream();
    }
    public function departamento(Request $request)
    {
        $request->validate([
            'departamento' => ['required'],
            'fecha' => ['required', 'date']
        ]);

        $departamento = $request->departamento;

        $fechaConsulta = Carbon::parse($request->fecha)->timezone('America/Mexico_City');

        // CORRECCIÓN: Quitamos el timezone('UTC') para buscar exactamente por la hora local
        $inicio = $fechaConsulta->copy()->startOfDay()->format('Y-m-d H:i:s');
        $fin    = $fechaConsulta->copy()->endOfDay()->format('Y-m-d H:i:s');

        // Día de la semana (1 = Domingo, ..., 7 = Sábado) para cruzarlo con el horario
        $numeroDiaSemana = (string) ($fechaConsulta->dayOfWeek + 1);

        // Obtenemos solo los IDs de los usuarios de ese departamento
        $idsUsuarios = Usuarios::select('usuario')->whereHas(
            'instance',
            fn($q) => $q->where('codigo', $departamento)
        )->pluck('usuario');

        // Traemos a los usuarios con sus horarios y SUS REGISTROS FILTRADOS en una sola consulta
        $registrosRaw = Usuarios::with([
            'horarios',
            'registros' => function ($query) use ($inicio, $fin) {
                $query->whereBetween('fechahora', [$inicio, $fin])
                    ->orderBy('fechahora');
            }
        ])
            ->whereIn('usuario', $idsUsuarios)
            ->get();

        // Mapeamos y estructuramos la información
        $usuarios = $registrosRaw->map(function ($user) use ($numeroDiaSemana, $fechaConsulta) {

            // 1. Verificamos si hoy le tocaba trabajar revisando sus bloques de horarios
            $esDiaLaboral = false;
            foreach ($user->horarios as $bloque) {
                $diasArray = is_array($bloque->dias) ? $bloque->dias : str_split($bloque->dias ?? '');
                if (in_array($numeroDiaSemana, $diasArray)) {
                    $esDiaLaboral = true;
                    break;
                }
            }

            // 2. Si no checó tarjeta en todo el día
            if ($user->registros->isEmpty()) {
                return [
                    'nombre'        => $user->nombre,
                    'codigo'        => $user->usuario,
                    'sin_registros' => true,
                    'es_laboral'    => $esDiaLaboral, // Puedes usar esto en tu vista para pintar rojo (falta) o gris (descanso)
                    'dias'          => [],
                ];
            }

            // 3. Si sí tiene registros, sacamos el primero y el último
            $entrada = $user->registros->first();
            $salida  = $user->registros->last();
            $cantidadRegistros = $user->registros->count();

            $hora_entrada = Carbon::parse($entrada->fechahora)->setTimezone('America/Mexico_City');

            // Si solo checó 1 vez, la salida es nula. Si checó 2 o más, tomamos la última.
            $hora_salida = ($cantidadRegistros > 1)
                ? Carbon::parse($salida->fechahora)->setTimezone('America/Mexico_City')
                : null;

            $tiempo_total = '00:00:00';
            if ($hora_salida) {
                $tiempo_total = $hora_entrada->diff($hora_salida)->format('%H:%I:%S');
            }

            // Mantenemos la estructura 'dias' para no romper tu vista blade actual
            $fechaString = $fechaConsulta->format('Y-m-d');

            return [
                'nombre'        => $user->nombre,
                'codigo'        => $user->usuario,
                'sin_registros' => false,
                'es_laboral'    => $esDiaLaboral,
                'dias'          => [
                    $fechaString => [
                        'fecha'        => $fechaString,
                        'hora_entrada' => $hora_entrada->format('H:i:s'),
                        'tipo_entrada' => $entrada->tipo,
                        'hora_salida'  => $hora_salida ? $hora_salida->format('H:i:s') : 'SIN CHECAR',
                        'tipo_salida'  => $hora_salida ? $salida->tipo : null,
                        'tiempo_total' => $tiempo_total,
                        'detalle_raw'  => $cantidadRegistros,
                    ]
                ]
            ];
        });

        $periodo = [$fechaConsulta->format('Y-m-d'), $fechaConsulta->format('Y-m-d')];
        $departamentoInfo = Instancias::select('nombre')->where('codigo', $departamento)->first();

        $html = view('reportes.asistencias', [
            'usuarios'     => $usuarios,
            'periodo'      => $periodo,
            'departamento' => $departamentoInfo
        ]);

        $pdf = Pdf::loadHtml($html->render())->setPaper('letter', 'portrait')
            ->setOptions([
                'defaultFont' => 'Montserrat',
                'isRemoteEnabled' => true,
                'isFontSubsettingEnabled' => true,
            ]);

        $pdf->output();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->get_canvas();
        $width = $canvas->get_width();
        $x_center = ($width / 2) - 50;

        $canvas->page_text($x_center, 750, "Parres Arias No. 150 Los Belenes C.P. 45132.", null, 8, [0, 0, 0]);
        $canvas->page_text(100, 760, "www.cucsh.udg.mx", null, 11, "#7D91BE");
        $canvas->page_text($x_center, 760, "Zapopan, Jalisco, México.   Tel. +52 (33) 38193300", null, 8, [0, 0, 0]);
        $canvas->page_text($x_center, 770, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 8, [0, 0, 0]);

        return $pdf->stream();
    }


    private function esDiaLaboral(Carbon $fecha, array $diasLaborales): bool
    {
        $claveDiaUsuario = $fecha->dayOfWeek + 1;

        return in_array((string) $claveDiaUsuario, $diasLaborales, true);
    }

    private function resolverEstadoDia(
        Carbon $fecha,
        $datosDia,
        bool $esDiaLaboral,
        ?string $horarioEntradaStr, // <-- Añadimos el "?" para aceptar null
        ?string $horarioSalidaStr,  // <-- Añadimos el "?" para aceptar null
        int $minutosTolerancia,
        array $esFestivo
    ): array {

        if ($esFestivo[0]) {
            return [$esFestivo[1], '#6f42c1', null];
        }

        if ($this->esJustificado($datosDia)) {
            return ['Justificado', '#0dcaf0', null];
        }

        // Si tiene registros (checó tarjeta)
        if ($datosDia) {
            // VALIDACIÓN NUEVA: Si checó pero es su día de descanso (no hay horario)
            if (!$esDiaLaboral || !$horarioEntradaStr || !$horarioSalidaStr) {
                return ['Día Libre Trabajado', '#198754', 'Registro en día de descanso'];
            }

            // Si checó y sí es día laboral, evaluamos normal
            return $this->evaluarAsistencia(
                $fecha,
                $datosDia,
                $horarioEntradaStr, // Ya estamos seguros de que no es null aquí
                $horarioSalidaStr,  // Ya estamos seguros de que no es null aquí
                $minutosTolerancia
            );
        }

        if ($datosDia && $datosDia->count() === 1) {
            return ['Error', '#fd7e14', null];
        }

        // 5. SIN REGISTRO
        return $this->evaluarDiaSinRegistro($fecha, $esDiaLaboral);
    }

    private function evaluarAsistencia(
        Carbon $fecha,
        $datosDia,
        string $horarioEntradaStr,
        string $horarioSalidaStr,
        int $minutosTolerancia
    ): array {


        $entrada = $datosDia->first();
        $salida  = $datosDia->last();

        $entradaReal = Carbon::parse($entrada->fechahora)->timezone('America/Mexico_City');
        $salidaReal  = Carbon::parse($salida->fechahora)->timezone('America/Mexico_City');

        $entradaIdeal = Carbon::parse($fecha->format('Y-m-d') . ' ' . $horarioEntradaStr);
        $salidaIdeal  = Carbon::parse($fecha->format('Y-m-d') . ' ' . $horarioSalidaStr);

        if ($entradaReal->gt($entradaIdeal->copy()->addMinutes($minutosTolerancia))) {
            $estado = 'Retardo';
            $color  = '#e0a800';
        } elseif ($salidaReal->lt($salidaIdeal) && !$entradaReal->eq($salidaReal)) {
            $estado = 'Salida Anticipada';
            $color  = '#17a2b8';
        } else {
            $estado = 'Asistencia';
            $color  = '#28a745';
        }
        return [
            $estado,
            $color,
            [
                'entrada' => $entradaReal->format('H:i:s'),
                'salida'  => $salidaReal->format('H:i:s'),
                'tiempo'  => $entradaReal->diff($salidaReal)->format('%H:%I:%S'),
            ]
        ];
    }

    private function evaluarDiaSinRegistro(Carbon $fecha, bool $esDiaLaboral): array
    {
        if (!$esDiaLaboral) {
            return ['DESCANSO', '#000', null];
        }

        if ($fecha->lt(Carbon::today())) {
            return ['FALTA', '#dc3545', null];
        }

        if ($fecha->isToday()) {
            return ['EN CURSO', '#000', null];
        }

        return ['', '#ffffff', null]; // Futuro
    }


    private function obtenerEventoDelDia(Carbon $fecha, $eventos): ?string
    {

        return $eventos->get($fecha->format('Y-m-d'))?->first()['tipo'] ?? null;
    }

    private function esFestivo(string $tipo = null)
    {

        $tipos = TipoEvento::select('nombre')->get()->pluck('nombre');
        return [$tipos->contains($tipo), $tipo];
    }
    private function esJustificado($datosDia): bool
    {
        return $datosDia && $datosDia->contains(fn($r) => $r->tipo === 'justificado');
    }
}
