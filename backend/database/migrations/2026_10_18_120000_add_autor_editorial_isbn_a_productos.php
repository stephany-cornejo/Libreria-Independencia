<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade los campos propios de un libro (autor, editorial e ISBN)
     * y permite productos sin proveedor, ya que los títulos del
     * inventario no siempre proceden de un distribuidor concreto.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table): void {
            $table->string('autor', 100)->nullable()->after('nombre');
            $table->string('editorial', 100)->nullable()->after('autor');
            $table->string('isbn', 20)->nullable()->after('editorial');
        });

        Schema::table('productos', function (Blueprint $table): void {
            $table->unsignedInteger('id_proveedor')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table): void {
            $table->dropColumn(['autor', 'editorial', 'isbn']);
        });

        Schema::table('productos', function (Blueprint $table): void {
            $table->unsignedInteger('id_proveedor')->nullable(false)->change();
        });
    }
};
