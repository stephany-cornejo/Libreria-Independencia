<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\PanelController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'aplicacion' => 'Librería Independencia',
        'estado' => 'Servidor funcionando',
    ]);
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'cuenta.activa'])->group(function () {
    Route::get('/panel', [PanelController::class, 'index'])
        ->name('panel');

    Route::get('/panel/configuracion', [PanelController::class, 'configuracion'])
        ->middleware('rol:Administrador')
        ->name('panel.configuracion');

    Route::get('/panel/inventario', [InventarioController::class, 'index'])
        ->name('inventario.index');

    Route::get('/panel/inventario/crear', [InventarioController::class, 'crear'])
        ->name('inventario.crear');

    Route::post('/panel/inventario', [InventarioController::class, 'guardar'])
        ->name('inventario.guardar');

    Route::get('/panel/inventario/{producto}/editar', [InventarioController::class, 'editar'])
        ->name('inventario.editar');

    Route::put('/panel/inventario/{producto}', [InventarioController::class, 'actualizar'])
        ->name('inventario.actualizar');

    Route::delete('/panel/inventario/{producto}', [InventarioController::class, 'eliminar'])
        ->name('inventario.eliminar');
});
