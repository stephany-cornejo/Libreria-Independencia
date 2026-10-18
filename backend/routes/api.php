<?php

use App\Http\Controllers\Api\EstadoController;
use App\Http\Controllers\Api\SesionController;
use Illuminate\Support\Facades\Route;

Route::get('/estado', EstadoController::class)
    ->name('api.estado');

Route::middleware(['auth', 'cuenta.activa'])->group(function () {
    Route::get('/sesion', SesionController::class)
        ->name('api.sesion');

    Route::get('/configuracion', function () {
        return response()->json([
            'mensaje' => 'Configuración del sistema disponible para administradores.',
        ]);
    })->middleware('rol:Administrador')
        ->name('api.configuracion');
});
