<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoriaApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Evita ejecutar pruebas en la base principal.
        $this->assertSame(
            'libreria_independencia_testing',
            DB::connection()->getDatabaseName()
        );
    }

    private function usuarioConRol(string $nombre): User
    {
        $rol = Rol::firstOrCreate(['nombre' => $nombre]);

        return User::factory()->create([
            'id_rol' => $rol->id_rol,
        ]);
    }

    public function test_solo_administradores_pueden_consultar(): void
    {
        $this->getJson('/api/categorias')
            ->assertStatus(401);

        $empleado = $this->usuarioConRol('Empleado');

        $this->actingAs($empleado)
            ->getJson('/api/categorias')
            ->assertStatus(403);

        $admin = $this->usuarioConRol('Administrador');

        $this->actingAs($admin)
            ->getJson('/api/categorias')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_busqueda_y_filtro_funcionan(): void
    {
        $identificador = Str::random(10);

        $activa = Categoria::create([
            'nombre' => "Prueba {$identificador} Activa",
            'estado' => 'activo',
        ]);

        $inactiva = Categoria::create([
            'nombre' => "Prueba {$identificador} Inactiva",
            'estado' => 'inactivo',
        ]);

        $admin = $this->usuarioConRol('Administrador');

        $this->actingAs($admin)
            ->getJson('/api/categorias?'.http_build_query([
                'q' => $identificador,
                'estado' => 'inactivo',
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id_categoria' => $inactiva->id_categoria,
            ])
            ->assertJsonMissing([
                'id_categoria' => $activa->id_categoria,
            ]);
    }

    public function test_detalle_y_categoria_inexistente(): void
    {
        $categoria = Categoria::create([
            'nombre' => 'Detalle '.Str::random(10),
            'estado' => 'activo',
        ]);

        $admin = $this->usuarioConRol('Administrador');

        $this->actingAs($admin)
            ->getJson("/api/categorias/{$categoria->id_categoria}")
            ->assertOk()
            ->assertJsonPath(
                'id_categoria',
                $categoria->id_categoria
            )
            ->assertJsonPath('productos_count', 0);

        $this->getJson('/api/categorias/999999')
            ->assertNotFound();
    }

    public function test_rechaza_filtros_invalidos(): void
    {
        $admin = $this->usuarioConRol('Administrador');

        $this->actingAs($admin)
            ->getJson('/api/categorias?estado=pendiente')
            ->assertStatus(422)
            ->assertJsonValidationErrors('estado');
    }
}
