<?php

use App\Http\Controllers\Api\EstadoController;
use App\Http\Controllers\Api\SesionController;
use App\Http\Controllers\Api\CategoriaApiController;
use App\Http\Controllers\Api\ProveedorApiController;
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


Route::middleware([
    'web',
    'auth',
    'cuenta.activa',
    'rol:Administrador',
])->group(function () {

    // Consultar todas las categorías
    Route::get('/categorias', [
        CategoriaApiController::class, 'index'
    ])->name('api.categorias.index');

    // Consultar una categoría
    Route::get('/categorias/{categoria}', [
        CategoriaApiController::class, 'show'
    ])->name('api.categorias.show');

    // Registrar una categoría
    Route::post('/categorias', [
        CategoriaApiController::class, 'store'
    ])->name('api.categorias.store');

    // Editar una categoría
    Route::put('/categorias/{categoria}', [
        CategoriaApiController::class, 'update'
    ])->name('api.categorias.update');

    // Activar o desactivar una categoría
    Route::patch('/categorias/{categoria}/estado', [
        CategoriaApiController::class, 'updateStatus'
    ])->name('api.categorias.estado');

    // Eliminar una categoría
    Route::delete('/categorias/{categoria}', [
        CategoriaApiController::class, 'destroy'
    ])->name('api.categorias.destroy');

    // Consultar proveedores
    Route::get('/proveedores', [
        ProveedorApiController::class, 'index'
    ])->name('api.proveedores.index');

    Route::get('/proveedores/{proveedor}', [
        ProveedorApiController::class, 'show'
    ])->name('api.proveedores.show');

});
