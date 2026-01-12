<?php

use App\Http\Controllers\ReporteAsistenciasController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Filament\Http\Middleware\Authenticate;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['web', Authenticate::class])
    ->prefix('admin')
    ->group(function () {
        Route::get('/reportes/asistencias', [ReporteAsistenciasController::class, 'index'])
            ->name('reportes.asistencias');

        Route::get('/reportes/faltas', [ReporteAsistenciasController::class, 'faltas'])
            ->name('reportes.faltas');

        Route::get('/reportes/asistencias-departamento', [ReporteAsistenciasController::class, 'departamento'])
            ->name('reportes.asistencias-departamento');


        Route::get('/fotos-perfil/{filename}', function ($filename) {
            $disk = Storage::disk('servidor_fotos');
            if (! $disk->exists($filename)) {
                abort(404);
            }

            $fileContent = $disk->get($filename);
            $type = $disk->mimeType($filename) ?? 'image/jpg';

            return Response::make($fileContent, 200, [
                'Content-Type' => $type,
                'Cache-Control' => 'public, max-age=86400', // Cache por 1 día
            ]);
        })->name('storage.proxy');
    });
