<?php

namespace App\Http\Controllers;

use App\Models\Instancias;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Registros;
use App\Models\Usuarios;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteAsistenciasController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'usuario' => ['required', 'integer'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
        ]);


        $usuario = Usuarios::where('usuario', $request->usuario)->get();
        if (!$usuario) {
            abort(404);
        }
        $query = Registros::with('user')->where('usuario', $request->usuario);

        $registrosRaw = Registros::with('user') // Eager load para rendimiento
            ->whereIn('usuario', [$request->usuario])
            ->whereBetween('fechahora', [$request->desde, $request->hasta]) // Asegúrate que $inicio/$fin estén en UTC si así guardas
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

                        // Calcular tiempo trabajado
                        $tiempo = Carbon::parse($entrada->fechahora)
                            ->diff(Carbon::parse($salida->fechahora))
                            ->format('%H:%I:%S');

                        return [
                            'fecha'        => $fecha,
                            'hora_entrada' => Carbon::parse($entrada->fechahora)->setTimezone('America/Mexico_City')->format('H:i:s'),
                            'tipo_entrada' => $entrada->tipo,
                            'hora_salida'  => Carbon::parse($salida->fechahora)->setTimezone('America/Mexico_City')->format('H:i:s'),
                            'tipo_salida'  => $salida->tipo,
                            'tiempo_total' => $tiempo,
                            'detalle_raw'  => $registrosDelDia->count() // Útil para auditoría
                        ];
                    });
            })
            ->toArray();

        $periodo = [$request->desde, $request->hasta];
        $html = view('reportes.asistencias', compact('registros', 'usuarios', 'periodo'));
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
}
