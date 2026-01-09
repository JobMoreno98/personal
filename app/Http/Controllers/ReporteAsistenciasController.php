<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Registros;

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

        dd($registros);
        // Aquí decides el formato
        // return Excel::download(...)
        // return PDF::loadView(...)
        return view('reportes.asistencias', compact('registros'));
    }
}
