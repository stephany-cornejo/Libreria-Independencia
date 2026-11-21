<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

/*
 * Rutas del Integrante 4. Se cargan desde routes/api.php con:
 *     require __DIR__.'/integrante4.php';
 * (ya dentro del grupo auth:sanctum, así que heredan el prefijo /api)
 */

// Dashboard: administrador y empleado
Route::get('dashboard', [DashboardController::class, 'index']);

// Documentos: todos pueden ver, subir y descargar; solo el administrador elimina
Route::get('documentos', [DocumentoController::class, 'index']);
Route::post('documentos', [DocumentoController::class, 'store']);
Route::get('documentos/{documento}/descargar', [DocumentoController::class, 'descargar']);

// Reportes: solo administrador
Route::middleware('rol:administrador')->group(function () {
    Route::get('reportes/inventario', [ReporteController::class, 'inventario']);
    Route::get('reportes/inventario/pdf', [ReporteController::class, 'inventarioPdf']);
    Route::get('reportes/inventario/excel', [ReporteController::class, 'inventarioExcel']);

    Route::get('reportes/movimientos', [ReporteController::class, 'movimientos']);
    Route::get('reportes/movimientos/pdf', [ReporteController::class, 'movimientosPdf']);
    Route::get('reportes/movimientos/excel', [ReporteController::class, 'movimientosExcel']);

    Route::delete('documentos/{documento}', [DocumentoController::class, 'destroy']);
});
