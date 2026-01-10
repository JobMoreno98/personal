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
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
        ]);


        $usuario = Usuarios::where('usuario', $request->usuario)->first();
        if (!$usuario) {
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


        $horario_usuario = Horario::where('usuario', $request->usuario)->first();
        // dd("Entarda " . $horario_usuario->entrada, "Salida " . $horario_usuario->salida, "Días de trabajo " , ($horario_usuario->dias));


        $periodo = CarbonPeriod::create($inicio, $fin);
        $calendario = [];

        // Aseguramos que los horarios vengan limpios (ej: "08:00:00")
        $horarioEntradaStr = $horario_usuario->entrada;
        $horarioSalidaStr  = $horario_usuario->salida;
        $diasLaborales     = $horario_usuario->dias; // Array ej: ["2", "3", "4" ...]
        $minutosTolerancia = 30;

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

            $nombreMes = ucfirst($fechaActual->locale('es')->monthName) . ' ' . $fechaActual->year;
            $datosDia  = $registros->get( $fechaStr );

            $esDiaLaboral = $this->esDiaLaboral($fechaActual, $diasLaborales);

            $tipoEvento = $this->obtenerEventoDelDia($fechaActual, $eventosPorFecha);
            
            $esFestivo      = $this->esFestivo($tipoEvento);

            $esJustificado  = $this->esJustificado($datosDia);

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
            ];
        }

        $periodo = [$request->desde, $request->hasta];
        $html = view('reportes.asistencias-usuario', compact('calendario', 'usuario', 'periodo', 'registros'));
        //return $html;
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
        $x_center = ($width / 2) - 50; // Ajusta según el ancho del texto

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
        $fecha        = $request->fecha;


        $inicio = Carbon::parse($fecha)->startOfDay()->timezone('America/Mexico_City')->format('Y-m-d H:i:s');
        $fin    = Carbon::parse($fecha)->endOfDay()->timezone('America/Mexico_City')->format('Y-m-d H:i:s');

        // Ejemplo
        $idsUsuarios = Usuarios::select('usuario')->whereHas(
            'instance',
            fn($q) =>
            $q->where('codigo', $departamento)
        )->get()->pluck('usuario');
        //return $users;

        $registrosRaw = Registros::with('user') // Eager load para rendimiento
            ->whereIn('usuario', $idsUsuarios)
            ->whereBetween('fechahora', [$inicio, $fin]) // Asegúrate que $inicio/$fin estén en UTC si así guardas
            ->orderBy('fechahora')
            ->get();

        $usuarios = $registrosRaw
            // A. Agrupamos primero por Nombre del Usuario (como lo tenías)
            ->groupBy(fn($registro) => $registro->user->nombre ?? 'Sin Nombre')

            // B. Iteramos sobre cada Usuario
            ->map(function ($registrosDelUsuario) {

                // C. Agrupamos los registros de ese usuario POR DÍA
                return $registrosDelUsuario->groupBy(function ($registro) {

                    return Carbon::parse($registro->fechahora)
                        ->setTimezone('America/Mexico_City') // Importante para no mezclar días
                        ->format('Y-m-d');
                })

                    // D. Procesamos cada día para dejar solo entrada/salida
                    ->map(function ($registrosDelDia, $fecha) {

                        $entrada = $registrosDelDia->first();
                        $salida  = $registrosDelDia->last();
                        if (count($registrosDelDia) == 1) {
                            $hora_salida = 'Sin Registro';
                            $tipo_salida = 'Sin Registro';
                            // Calcular tiempo trabajado
                            $tiempo = 'Sin Registro';
                        } else {
                            $hora_salida = Carbon::parse($salida->fechahora)->setTimezone('America/Mexico_City')->format('H:i:s');
                            $tipo_salida = $salida->tipo;
                            // Calcular tiempo trabajado
                            $tiempo = Carbon::parse($entrada->fechahora)
                                ->diff(Carbon::parse($salida->fechahora))
                                ->format('%H:%I:%S');
                        }



                        return [
                            'fecha'        => $fecha,
                            'hora_entrada' => Carbon::parse($entrada->fechahora)->setTimezone('America/Mexico_City')->format('H:i:s'),
                            'tipo_entrada' => $entrada->tipo,
                            'hora_salida'  =>  $hora_salida,
                            'tipo_salida'  =>  $tipo_salida,
                            'tiempo_total' => $tiempo,
                            'detalle_raw'  => $registrosDelDia->count(), // Útil para auditoría
                            'codigo' => $registrosDelDia->first()->usuario
                        ];
                    });
            })
            ->toArray();

        $periodo = [$inicio, $fin];
        $departamento = Instancias::select('nombre')->where('codigo', $departamento)->first();
        $html = view('reportes.asistencias', compact('usuarios', 'periodo', 'departamento'));
        //return $html;
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
        $x_center = ($width / 2) - 50; // Ajusta según el ancho del texto

        $canvas->page_text($x_center, 750, "Parres Arias No. 150 Los Belenes C.P. 45132.", null, 8, [0, 0, 0]);
        $canvas->page_text(100, 760, "www.cucsh.udg.mx", null, 11, "#7D91BE");
        $canvas->page_text($x_center, 760, "Zapopan, Jalisco, México.   Tel. +52 (33) 38193300 Ext. 23700", null, 8, [0, 0, 0]);
        $canvas->page_text($x_center, 770, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 8, [0, 0, 0]);

        return $pdf->stream();
        return view('reportes.asistencias', compact('registros'));
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
        string $horarioEntradaStr,
        string $horarioSalidaStr,
        int $minutosTolerancia,
        bool $esFestivo
    ): array {
        // 1. FESTIVO / ESPECIAL
        if ($esFestivo) {
            return ['FESTIVO', '#6f42c1', null];
        }

        // 2. JUSTIFICADO (sin asistencia)
        if ($this->esJustificado($datosDia)) {
            return ['JUSTIFICADO', '#0dcaf0', null];
        }

        if ($datosDia) {
            return $this->evaluarAsistencia(
                $fecha,
                $datosDia,
                $horarioEntradaStr,
                $horarioSalidaStr,
                $minutosTolerancia
            );
        }
        if ($datosDia && $datosDia->count() === 1) {
            return ['ERROR', '#fd7e14', null];
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
            $estado = 'RETARDO';
            $color  = '#e0a800';
        } elseif ($salidaReal->lt($salidaIdeal) && !$entradaReal->eq($salidaReal)) {
            $estado = 'SALIDA <br/> ANTICIPADA';
            $color  = '#17a2b8';
        } else {
            $estado = 'ASISTENCIA';
            $color  = '#28a745';
        }
        return [
            $estado,
            $color,
            [
                'entrada' => $entradaReal->format('H:i'),
                'salida'  => $salidaReal->format('H:i'),
                'tiempo'  => $entradaReal->diff($salidaReal)->format('%H:%I'),
            ]
        ];
    }

    private function evaluarDiaSinRegistro(Carbon $fecha, bool $esDiaLaboral): array
    {
        if (!$esDiaLaboral) {
            return ['DESCANSO', '#eeeeee', null];
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

    private function esFestivo(string $tipo = null): bool
    {
        
        $tipos = TipoEvento::select('nombre')->get()->pluck('nombre')->toArray();
        return in_array($tipo, $tipos, true);
    }
    private function esJustificado($datosDia): bool
    {
        return $datosDia && $datosDia->contains(fn($r) => $r->tipo === 'justificado');
    }

    private function tieneError($datosDia): bool
    {
        return $datosDia && $datosDia->count() === 1;
    }
}
