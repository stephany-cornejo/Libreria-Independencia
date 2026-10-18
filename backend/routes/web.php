<?php

use App\Http\Controllers\Auth\LoginController;
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
});
