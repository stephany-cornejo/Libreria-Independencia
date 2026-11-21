<?php

namespace App\Providers;

use App\Events\StockBajo;
use App\Listeners\RegistrarAlertaStockBajo;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Alerta de existencias bajas: listener en cola (asíncrono).
        Event::listen(
            StockBajo::class,
            RegistrarAlertaStockBajo::class
        );
    }
}
