<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'cuenta.activa'])->group(function () {
    Route::get('reportes/existencias', [ReporteController::class, 'existencias'])->name('reportes.existencias');
    Route::get('alertas/stock-bajo', [ReporteController::class, 'stockBajo'])->name('reportes.stock-bajo');
    Route::get('historial', [HistorialController::class, 'index'])->name('historial.index');

    Route::middleware('rol:Administrador')->group(function () {
        Route::resource('categorias', CategoriaController::class)->except(['show', 'destroy']);
        Route::patch('categorias/{categoria}/estado', [CategoriaController::class, 'estado'])->name('categorias.estado');

        Route::resource('proveedores', ProveedorController::class)
            ->except(['show', 'destroy'])
            ->parameters(['proveedores' => 'proveedor']);
        Route::patch('proveedores/{proveedor}/estado', [ProveedorController::class, 'estado'])->name('proveedores.estado');

        Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('reportes/agotados', [ReporteController::class, 'agotados'])->name('reportes.agotados');
        Route::get('reportes/mas-vendidos', [ReporteController::class, 'masVendidos'])->name('reportes.mas-vendidos');
        Route::get('reportes/poca-rotacion', [ReporteController::class, 'pocaRotacion'])->name('reportes.poca-rotacion');
        Route::get('reportes/movimientos', [ReporteController::class, 'movimientos'])->name('reportes.movimientos');
    });
});
