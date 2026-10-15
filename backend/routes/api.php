<?php

use App\Http\Controllers\Api\EstadoController;
use Illuminate\Support\Facades\Route;

Route::get('/estado', EstadoController::class)
    ->name('api.estado');
    