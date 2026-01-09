<?php

namespace App\Http\Controllers;

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

        $query = Registros::where('usuario', $request->usuario);
        $usuario = Usuarios::where('usuario', $request->usuario)->first();
        if (!$usuario) {
            abort(404);
        }

        if ($request->desde) {
            $query->where(
                'fechahora',
                '>=',
                Carbon::parse($request->desde)->startOfDay()->timezone('UTC')
            );
        }

        if ($request->hasta) {
            $query->where(
                'fechahora',
                '<=',
                Carbon::parse($request->hasta)->endOfDay()->timezone('UTC')
            );
        }

        $registros = $query->orderBy('fechahora')->get();

        // Aquí decides el formato
        // return Excel::download(...)
        // return PDF::loadView(...)

        $periodo = [$request->desde, $request->hasta];
        $html = view('reportes.asistencias', compact('registros', 'usuario', 'periodo'));
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


        $inicio = Carbon::parse($fecha)->startOfDay();
        $fin    = Carbon::parse($fecha)->endOfDay();

        // Ejemplo
        $usuarios = Usuarios::select('usuario')->whereHas(
            'instance',
            fn($q) =>
            $q->where('codigo', $departamento)
        )->get();


        $registros = Registros::whereIn('usuario', $usuarios)
            ->whereBetween('fechahora', [$inicio, $fin])
            ->orderBy('fechahora')
            ->get();
        return $registros;
    }
}
