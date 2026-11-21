<?php

use App\Http\Controllers\Api\EstadoController;
use App\Http\Controllers\Api\SesionController;
use App\Http\Controllers\Api\CategoriaApiController;
use Illuminate\Support\Facades\Route;

// Existing project routes
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

// Category API routes - Integrante 2
Route::middleware([
    'web',
    'auth',
    'cuenta.activa',
    'rol:Administrador',
])->group(function () {

    Route::get('/categorias', [
        CategoriaApiController::class, 'index'
    ])->name('api.categorias.index');

    Route::get('/categorias/{categoria}', [
        CategoriaApiController::class, 'show'
    ])->name('api.categorias.show');

});
