<?php

namespace Tests\Feature;

use App\Http\Requests\CategoriaRequest;
use App\Http\Requests\ProveedorRequest;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\CategoriaService;
use App\Services\ProveedorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CategoriaProveedorTest extends TestCase
{
    use DatabaseTransactions;

    public function test_crea_y_edita_una_categoria(): void
    {
        $servicio = app(CategoriaService::class);
        $nombre = 'Test '.uniqid();

        $categoria = $servicio->crear(['nombre' => $nombre, 'descripcion' => 'Prueba']);
        $this->assertDatabaseHas('categorias', ['nombre' => $nombre, 'estado' => 'activo']);

        $servicio->actualizar($categoria, ['nombre' => $nombre.' B', 'descripcion' => null]);
        $this->assertDatabaseHas('categorias', ['id_categoria' => $categoria->getKey(), 'nombre' => $nombre.' B']);
    }

    public function test_desactivar_proveedor_con_productos_conserva_el_historial(): void
    {
        $categoria = Categoria::create(['nombre' => 'Test '.uniqid()]);
        $proveedor = Proveedor::create(['nombre' => 'Proveedor '.uniqid()]);
        $producto = Producto::create([
            'codigo' => 'T'.substr(uniqid(), -8),
            'nombre' => 'Producto de prueba',
            'id_categoria' => $categoria->getKey(),
            'id_proveedor' => $proveedor->getKey(),
            'precio_compra' => 1, 'precio_venta' => 2,
            'cantidad_disponible' => 5, 'existencia_minima' => 2,
            'fecha_ingreso' => now()->toDateString(), 'estado' => 'activo',
        ]);

        $servicio = app(ProveedorService::class);
        $servicio->alternarEstado($proveedor);

        $this->assertDatabaseHas('proveedores', ['id_proveedor' => $proveedor->getKey(), 'estado' => 'inactivo']);
        $this->assertDatabaseHas('productos', ['id_producto' => $producto->getKey()]);

        $servicio->alternarEstado($proveedor->fresh());
        $this->assertDatabaseHas('proveedores', ['id_proveedor' => $proveedor->getKey(), 'estado' => 'activo']);
    }

    public function test_validacion_de_categoria_rechaza_nombre_vacio_y_demasiado_largo(): void
    {
        $reglas = (new CategoriaRequest())->rules();

        $this->assertTrue(Validator::make(['nombre' => ''], $reglas)->fails());
        $this->assertTrue(Validator::make(['nombre' => str_repeat('a', 51)], $reglas)->fails());
        $this->assertFalse(Validator::make(['nombre' => 'Papelería '.uniqid()], $reglas)->fails());
    }

    public function test_validacion_de_proveedor_rechaza_correo_y_telefono_invalidos(): void
    {
        $reglas = (new ProveedorRequest())->rules();

        $this->assertTrue(Validator::make(['nombre' => 'X', 'email' => 'no-es-correo'], $reglas)->fails());
        $this->assertTrue(Validator::make(['nombre' => 'X', 'telefono' => 'abc'], $reglas)->fails());
        $this->assertFalse(Validator::make(['nombre' => 'X', 'email' => 'a@b.com', 'telefono' => '+503 2222-3333'], $reglas)->fails());
    }
}
