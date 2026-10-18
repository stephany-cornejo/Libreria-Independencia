<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->increments('id_movimiento');

            $table->enum('tipo', ['entrada', 'salida']);

            $table->enum('motivo', [
                'compra',
                'reposicion',
                'devolucion',
                'venta',
                'perdida',
                'dano',
                'ajuste',
            ]);

            $table->unsignedInteger('id_producto');
            $table->unsignedInteger('id_usuario');

            $table->unsignedInteger('cantidad');
            $table->unsignedInteger('existencia_resultante');

            $table->dateTime('fecha')->useCurrent();
            $table->string('observaciones', 255)->nullable();

            $table->timestamps();

            $table->index('fecha');
            $table->index(
                ['tipo', 'motivo', 'fecha'],
                'movimientos_tipo_motivo_fecha_index'
            );

            $table->foreign('id_producto')
                ->references('id_producto')
                ->on('productos')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });

        DB::statement('
            ALTER TABLE movimientos
            ADD CONSTRAINT movimientos_cantidad_positiva
                CHECK (cantidad > 0)
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};