<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->increments('id_producto');
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();

            $table->unsignedInteger('id_categoria');
            $table->unsignedInteger('id_proveedor');

            $table->decimal('precio_compra', 10, 2);
            $table->decimal('precio_venta', 10, 2);

            $table->unsignedInteger('cantidad_disponible')
                ->default(0);
            $table->unsignedInteger('existencia_minima')
                ->default(0);

            $table->date('fecha_ingreso');

            $table->enum('estado', ['activo', 'descontinuado'])
                ->default('activo');

            $table->timestamps();

            $table->index('nombre');

            $table->foreign('id_categoria')
                ->references('id_categoria')
                ->on('categorias')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_proveedor')
                ->references('id_proveedor')
                ->on('proveedores')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });

        DB::statement('
            ALTER TABLE productos
            ADD CONSTRAINT productos_precio_compra_no_negativo
                CHECK (precio_compra >= 0),
            ADD CONSTRAINT productos_precio_venta_no_negativo
                CHECK (precio_venta >= 0)
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};