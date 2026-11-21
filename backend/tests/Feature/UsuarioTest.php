<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsuarioTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame(
            'mysql',
            DB::connection()->getDriverName()
        );

        $this->assertSame(
            'libreria_independencia_testing',
            DB::connection()->getDatabaseName()
        );
    }

    private function crearUsuario(string $rol): User
    {
        $modeloRol = Rol::firstOrCreate([
            'nombre' => $rol,
        ]);

        return User::factory()->create([
            'id_rol' => $modeloRol->id_rol,
            'contrasena_hash' => 'ClaveDePrueba123!',
        ]);
    }

    private function datosValidos(array $sobrescritura = []): array
    {
        return array_merge([
            'nombre' => 'Ana Martínez',
            'usuario' => 'ana.martinez',
            'id_rol' => Rol::firstOrCreate(['nombre' => 'Empleado'])->id_rol,
            'estado' => 'activo',
            'contrasena' => 'ClaveSegura12345',
        ], $sobrescritura);
    }

    // ------------------------------------------------------------------
    // Autorización
    // ------------------------------------------------------------------

    public function test_visitante_es_redirigido_al_login_desde_cuentas(): void
    {
        $this->get('/panel/usuarios')->assertRedirect(route('login'));
        $this->get('/panel/usuarios/crear')->assertRedirect(route('login'));
    }

    public function test_empleado_es_denegado_en_el_modulo_de_cuentas(): void
    {
        $empleado = $this->crearUsuario('Empleado');

        $this->actingAs($empleado)->get('/panel/usuarios')->assertStatus(403);
        $this->actingAs($empleado)->get('/panel/usuarios/crear')->assertStatus(403);
        $this->actingAs($empleado)->post('/panel/usuarios', $this->datosValidos())
            ->assertStatus(403);
    }

    public function test_administrador_accede_al_modulo_de_cuentas(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        $this->actingAs($administrador)
            ->get('/panel/usuarios')
            ->assertOk()
            ->assertSee('Cuentas de usuario');
    }

    // ------------------------------------------------------------------
    // Creación
    // ------------------------------------------------------------------

    public function test_administrador_crea_cuenta_valida_con_contrasena_en_hash(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        $this->actingAs($administrador)
            ->post('/panel/usuarios', $this->datosValidos())
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('exito');

        $creada = User::where('usuario', 'ana.martinez')->firstOrFail();

        $this->assertSame('Ana Martínez', $creada->nombre);
        $this->assertSame('activo', $creada->estado);

        // Nunca se guarda ni se expone la contraseña en texto plano.
        $this->assertNotSame('ClaveSegura12345', $creada->contrasena_hash);
        $this->assertTrue(Hash::check('ClaveSegura12345', $creada->contrasena_hash));
        $this->assertArrayNotHasKey('contrasena_hash', $creada->toArray());
    }

    public function test_cuenta_requiere_campos_validos(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        $this->actingAs($administrador)
            ->post('/panel/usuarios', [
                'nombre' => '',
                'usuario' => '',
                'id_rol' => '',
                'estado' => '',
                'contrasena' => '',
            ])
            ->assertSessionHasErrors(['nombre', 'usuario', 'id_rol', 'estado', 'contrasena']);

        $this->actingAs($administrador)
            ->post('/panel/usuarios', $this->datosValidos([
                'nombre' => str_repeat('x', 101),
                'usuario' => str_repeat('y', 51),
                'estado' => 'suspendido',
                'contrasena' => 'corta',
            ]))
            ->assertSessionHasErrors(['nombre', 'usuario', 'estado', 'contrasena']);
    }

    public function test_contrasena_de_al_menos_12_caracteres(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        $this->actingAs($administrador)
            ->post('/panel/usuarios', $this->datosValidos([
                'contrasena' => '12345678901',
            ]))
            ->assertSessionHasErrors('contrasena');
    }

    public function test_usuario_duplicado_es_rechazado(): void
    {
        $administrador = $this->crearUsuario('Administrador');
        $this->crearUsuario('Empleado');

        $duplicado = User::where('usuario', 'like', 'prueba_%')->firstOrFail();

        $this->actingAs($administrador)
            ->post('/panel/usuarios', $this->datosValidos([
                'usuario' => $duplicado->usuario,
            ]))
            ->assertSessionHasErrors('usuario');
    }

    public function test_rol_inexistente_es_rechazado(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        $this->actingAs($administrador)
            ->post('/panel/usuarios', $this->datosValidos([
                'id_rol' => 999999,
            ]))
            ->assertSessionHasErrors('id_rol');
    }

    // ------------------------------------------------------------------
    // Edición
    // ------------------------------------------------------------------

    public function test_administrador_edita_cuenta_sin_tocar_la_contrasena(): void
    {
        $administrador = $this->crearUsuario('Administrador');
        $cuenta = $this->crearUsuario('Empleado');
        $hashOriginal = $cuenta->contrasena_hash;

        $this->actingAs($administrador)
            ->put("/panel/usuarios/{$cuenta->id_usuario}", [
                'nombre' => 'Ana Martínez Actualizada',
                'usuario' => $cuenta->usuario,
                'id_rol' => $cuenta->id_rol,
                'estado' => 'inactivo',
                'contrasena' => null,
            ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('exito');

        $cuenta->refresh();

        $this->assertSame('Ana Martínez Actualizada', $cuenta->nombre);
        $this->assertSame('inactivo', $cuenta->estado);
        $this->assertSame($hashOriginal, $cuenta->contrasena_hash);
    }

    public function test_editar_con_contrasena_la_actualiza_como_hash(): void
    {
        $administrador = $this->crearUsuario('Administrador');
        $cuenta = $this->crearUsuario('Empleado');

        $this->actingAs($administrador)
            ->put("/panel/usuarios/{$cuenta->id_usuario}", [
                'nombre' => $cuenta->nombre,
                'usuario' => $cuenta->usuario,
                'id_rol' => $cuenta->id_rol,
                'estado' => $cuenta->estado,
                'contrasena' => 'NuevaClaveSegura123',
            ])
            ->assertRedirect(route('usuarios.index'));

        $this->assertTrue(
            Hash::check('NuevaClaveSegura123', $cuenta->fresh()->contrasena_hash)
        );
    }

    // ------------------------------------------------------------------
    // Eliminación y reglas de negocio
    // ------------------------------------------------------------------

    public function test_cuenta_sin_movimientos_se_puede_eliminar(): void
    {
        $administrador = $this->crearUsuario('Administrador');
        $cuenta = $this->crearUsuario('Empleado');

        $this->actingAs($administrador)
            ->delete("/panel/usuarios/{$cuenta->id_usuario}")
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('exito');

        $this->assertDatabaseMissing('usuarios', [
            'id_usuario' => $cuenta->id_usuario,
        ]);
    }

    public function test_no_se_puede_eliminar_la_propia_cuenta(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        $this->actingAs($administrador)
            ->from('/panel/usuarios')
            ->delete("/panel/usuarios/{$administrador->id_usuario}")
            ->assertRedirect('/panel/usuarios')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $administrador->id_usuario,
        ]);

        $this->assertStringContainsString(
            'cuenta con la que iniciaste sesión',
            session('error')
        );
    }

    public function test_no_se_puede_desactivar_el_ultimo_administrador_activo(): void
    {
        // Único administrador activo: intenta desactivar su propia cuenta.
        $administrador = $this->crearUsuario('Administrador');

        $this->actingAs($administrador)
            ->from('/panel/usuarios')
            ->put("/panel/usuarios/{$administrador->id_usuario}", [
                'nombre' => $administrador->nombre,
                'usuario' => $administrador->usuario,
                'id_rol' => $administrador->id_rol,
                'estado' => 'inactivo',
                'contrasena' => null,
            ])
            ->assertRedirect('/panel/usuarios')
            ->assertSessionHas('error');

        $this->assertSame('activo', $administrador->fresh()->estado);

        $this->assertStringContainsString(
            'último administrador activo',
            session('error')
        );
    }
    public function test_no_se_puede_quitar_el_rol_al_ultimo_administrador_activo(): void
    {
        $administrador = $this->crearUsuario('Administrador');
        $rolAdministrador = $administrador->id_rol;
        $rolEmpleado = Rol::firstOrCreate(['nombre' => 'Empleado']);

        $this->actingAs($administrador)
            ->from('/panel/usuarios')
            ->put("/panel/usuarios/{$administrador->id_usuario}", [
                'nombre' => $administrador->nombre,
                'usuario' => $administrador->usuario,
                'id_rol' => $rolEmpleado->id_rol,
                'estado' => 'activo',
                'contrasena' => null,
            ])
            ->assertRedirect('/panel/usuarios')
            ->assertSessionHas('error');

        $this->assertSame($rolAdministrador, $administrador->fresh()->id_rol);
    }

    public function test_con_dos_administradores_se_puede_cambiar_el_rol_de_uno(): void
    {
        $administrador = $this->crearUsuario('Administrador');
        $otroAdmin = $this->crearUsuario('Administrador');
        $rolEmpleado = Rol::firstOrCreate(['nombre' => 'Empleado']);

        $this->actingAs($administrador)
            ->put("/panel/usuarios/{$otroAdmin->id_usuario}", [
                'nombre' => $otroAdmin->nombre,
                'usuario' => $otroAdmin->usuario,
                'id_rol' => $rolEmpleado->id_rol,
                'estado' => 'activo',
                'contrasena' => null,
            ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('exito');

        $this->assertSame($rolEmpleado->id_rol, $otroAdmin->fresh()->id_rol);
    }
    public function test_con_dos_administradores_activos_se_puede_desactivar_y_eliminar(): void
    {
        $administrador = $this->crearUsuario('Administrador');
        $otroAdmin = $this->crearUsuario('Administrador');

        // Desactivar al segundo administrador está permitido.
        $this->actingAs($administrador)
            ->put("/panel/usuarios/{$otroAdmin->id_usuario}", [
                'nombre' => $otroAdmin->nombre,
                'usuario' => $otroAdmin->usuario,
                'id_rol' => $otroAdmin->id_rol,
                'estado' => 'inactivo',
                'contrasena' => null,
            ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('exito');

        $this->assertSame('inactivo', $otroAdmin->fresh()->estado);

        // Reactivarlo y eliminarlo también está permitido.
        $otroAdmin->fresh()->update(['estado' => 'activo']);

        $this->actingAs($administrador)
            ->delete("/panel/usuarios/{$otroAdmin->id_usuario}")
            ->assertRedirect(route('usuarios.index'));

        $this->assertDatabaseMissing('usuarios', [
            'id_usuario' => $otroAdmin->id_usuario,
        ]);
    }

    public function test_listado_filtra_por_rol_y_por_estado(): void
    {
        $administrador = $this->crearUsuario('Administrador');
        $empleado = $this->crearUsuario('Empleado');
        $inactivo = $this->crearUsuario('Empleado');
        $inactivo->update(['estado' => 'inactivo']);

        $this->actingAs($administrador)
            ->get('/panel/usuarios?rol='.$empleado->id_rol)
            ->assertOk()
            ->assertSee($empleado->usuario)
            ->assertDontSee($administrador->usuario);

        $this->actingAs($administrador)
            ->get('/panel/usuarios?estado=inactivo')
            ->assertOk()
            ->assertSee($inactivo->usuario)
            ->assertDontSee($empleado->usuario);
    }

    public function test_cuenta_inactiva_no_puede_iniciar_sesion(): void
    {
        $cuenta = $this->crearUsuario('Empleado');
        $cuenta->update(['estado' => 'inactivo']);

        $this->post('/login', [
            'usuario' => $cuenta->usuario,
            'password' => 'ClaveDePrueba123!',
        ])->assertSessionHasErrors('usuario');
    }
}
