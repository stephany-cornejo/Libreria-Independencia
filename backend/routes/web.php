<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\ProveedorController;
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

    Route::get('/panel/movimientos', [MovimientoController::class, 'index'])
        ->name('movimientos.index');

    Route::get('/panel/movimientos/crear', [MovimientoController::class, 'crear'])
        ->name('movimientos.crear');

    Route::post('/panel/movimientos', [MovimientoController::class, 'guardar'])
        ->name('movimientos.guardar');

    // Catálogos: reservados al rol Administrador (según ENTREGA).
    Route::middleware('rol:Administrador')->group(function () {
        Route::get('/panel/categorias', [CategoriaController::class, 'index'])
            ->name('categorias.index');

        Route::get('/panel/categorias/crear', [CategoriaController::class, 'crear'])
            ->name('categorias.crear');

        Route::post('/panel/categorias', [CategoriaController::class, 'guardar'])
            ->name('categorias.guardar');

        Route::get('/panel/categorias/{categoria}/editar', [CategoriaController::class, 'editar'])
            ->name('categorias.editar');

        Route::put('/panel/categorias/{categoria}', [CategoriaController::class, 'actualizar'])
            ->name('categorias.actualizar');

        Route::delete('/panel/categorias/{categoria}', [CategoriaController::class, 'eliminar'])
            ->name('categorias.eliminar');

        Route::get('/panel/proveedores', [ProveedorController::class, 'index'])
            ->name('proveedores.index');

        Route::get('/panel/proveedores/crear', [ProveedorController::class, 'crear'])
            ->name('proveedores.crear');

        Route::post('/panel/proveedores', [ProveedorController::class, 'guardar'])
            ->name('proveedores.guardar');

        Route::get('/panel/proveedores/{proveedor}/editar', [ProveedorController::class, 'editar'])
            ->name('proveedores.editar');

        Route::put('/panel/proveedores/{proveedor}', [ProveedorController::class, 'actualizar'])
            ->name('proveedores.actualizar');

        Route::delete('/panel/proveedores/{proveedor}', [ProveedorController::class, 'eliminar'])
            ->name('proveedores.eliminar');
    });
});
