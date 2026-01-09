<?php

use App\Http\Controllers\ReporteAsistenciasController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/reportes/asistencias', [ReporteAsistenciasController::class, 'index'])
    ->name('reportes.asistencias')->middleware('auth');

    Route::get('/reportes/asistencias-departamento', [ReporteAsistenciasController::class, 'departamento'])
    ->name('reportes.asistencias-departamento')->middleware('auth');
