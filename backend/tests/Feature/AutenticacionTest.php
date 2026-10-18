<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AutenticacionTest extends TestCase
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

    private function crearUsuario(string $rol, array $atributos = []): User
    {
        $modeloRol = Rol::firstOrCreate([
            'nombre' => $rol,
        ]);

        return User::factory()->create(array_merge([
            'id_rol' => $modeloRol->id_rol,
            'contrasena_hash' => 'ClaveDePrueba123!',
        ], $atributos));
    }

    private function credencialesDe(User $usuario): array
    {
        return [
            'usuario' => $usuario->usuario,
            'password' => 'ClaveDePrueba123!',
        ];
    }

    public function test_login_con_credenciales_validas_redirige_al_panel(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        $respuesta = $this
            ->post('/login', $this->credencialesDe($administrador))
            ->assertRedirect(route('panel'));

        $this->assertAuthenticatedAs($administrador);

        $this->get('/panel')
            ->assertOk()
            ->assertSee($administrador->nombre)
            ->assertSee('Administrador');
    }

    public function test_login_de_empleado_accede_al_panel(): void
    {
        $empleado = $this->crearUsuario('Empleado');

        $this
            ->post('/login', $this->credencialesDe($empleado))
            ->assertRedirect(route('panel'));

        $this->assertAuthenticatedAs($empleado);

        $this->get('/panel')
            ->assertOk()
            ->assertSee('Empleado');
    }

    public function test_login_con_contrasena_incorrecta_rechaza_con_mensaje_generico(): void
    {
        $usuario = $this->crearUsuario('Empleado');

        $respuesta = $this
            ->from('/login')
            ->post('/login', [
                'usuario' => $usuario->usuario,
                'password' => 'ContrasenaIncorrecta!',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('usuario');

        $this->assertGuest();

        $this->assertSame(
            'Usuario o contraseña incorrectos.',
            session('errors')->first('usuario')
        );
    }

    public function test_login_con_usuario_inexistente_usa_el_mismo_mensaje_generico(): void
    {
        $usuario = $this->crearUsuario('Empleado');

        $this
            ->from('/login')
            ->post('/login', [
                'usuario' => $usuario->usuario,
                'password' => 'ContrasenaIncorrecta!',
            ])
            ->assertSessionHasErrors('usuario');

        $mensajeContrasenaIncorrecta = session('errors')->first('usuario');

        $this->flushSession();

        $this
            ->from('/login')
            ->post('/login', [
                'usuario' => 'usuario.que.no.existe',
                'password' => 'ContrasenaIncorrecta!',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('usuario');

        $this->assertGuest();

        $this->assertSame(
            $mensajeContrasenaIncorrecta,
            session('errors')->first('usuario')
        );
    }

    public function test_login_de_cuenta_inactiva_muestra_mensaje_de_cuenta_inactiva(): void
    {
        $usuario = $this->crearUsuario('Empleado', [
            'estado' => 'inactivo',
        ]);

        $this
            ->from('/login')
            ->post('/login', $this->credencialesDe($usuario))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('usuario');

        $this->assertGuest();

        $this->assertSame(
            'La cuenta está inactiva. Contacta al administrador.',
            session('errors')->first('usuario')
        );
    }

    public function test_login_valida_los_campos_obligatorios(): void
    {
        $this
            ->from('/login')
            ->post('/login', [
                'usuario' => '',
                'password' => '',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['usuario', 'password']);

        $this->assertGuest();
    }

    public function test_login_regenera_el_id_de_sesion(): void
    {
        $usuario = $this->crearUsuario('Empleado');

        $this->withSession([]);
        $idPrevio = session()->getId();

        $this
            ->post('/login', $this->credencialesDe($usuario))
            ->assertRedirect(route('panel'));

        $this->assertAuthenticatedAs($usuario);
        $this->assertNotSame($idPrevio, session()->getId());
    }

    public function test_logout_invalida_la_sesion_en_el_servidor(): void
    {
        $usuario = $this->crearUsuario('Empleado');

        $this->post('/login', $this->credencialesDe($usuario));
        $this->assertAuthenticatedAs($usuario);

        $this
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();

        $this->get('/panel')
            ->assertRedirect(route('login'));
    }

    public function test_visitante_es_redirigido_al_login_desde_ruta_protegida(): void
    {
        $this
            ->get('/panel')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_visitante_recibe_401_en_api_sin_sesion(): void
    {
        $this
            ->getJson('/api/sesion')
            ->assertStatus(401);
    }

    public function test_empleado_accede_al_panel_permitido(): void
    {
        $empleado = $this->crearUsuario('Empleado');

        $this->actingAs($empleado)
            ->get('/panel')
            ->assertOk()
            ->assertSee($empleado->nombre);
    }

    public function test_empleado_es_denegado_en_configuracion_web(): void
    {
        $empleado = $this->crearUsuario('Empleado');

        $this->actingAs($empleado)
            ->get('/panel/configuracion')
            ->assertStatus(403);
    }

    public function test_administrador_accede_a_configuracion_web(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        $this->actingAs($administrador)
            ->get('/panel/configuracion')
            ->assertOk()
            ->assertSee('Configuración del sistema');
    }

    public function test_empleado_es_denegado_en_configuracion_api(): void
    {
        $empleado = $this->crearUsuario('Empleado');

        $this->actingAs($empleado)
            ->getJson('/api/configuracion')
            ->assertStatus(403);
    }

    public function test_administrador_accede_a_configuracion_api(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        $this->actingAs($administrador)
            ->getJson('/api/configuracion')
            ->assertStatus(200)
            ->assertJsonStructure(['mensaje']);
    }

    public function test_sesion_api_devuelve_datos_del_usuario_sin_contrasena(): void
    {
        $administrador = $this->crearUsuario('Administrador');

        $respuesta = $this->actingAs($administrador)
            ->getJson('/api/sesion')
            ->assertStatus(200)
            ->assertJson([
                'id_usuario' => $administrador->id_usuario,
                'usuario' => $administrador->usuario,
                'rol' => 'Administrador',
                'estado' => 'activo',
            ])
            ->assertJsonMissingPath('contrasena_hash')
            ->assertJsonMissingPath('password');

        $this->assertStringNotContainsString(
            'ClaveDePrueba123!',
            $respuesta->getContent()
        );
    }

    public function test_cuenta_inactiva_con_sesion_activa_es_desconectada(): void
    {
        $usuario = $this->crearUsuario('Empleado', [
            'estado' => 'inactivo',
        ]);

        $this->actingAs($usuario)
            ->get('/panel')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_cuenta_inactiva_en_api_recibe_401_y_se_desconecta(): void
    {
        $usuario = $this->crearUsuario('Empleado', [
            'estado' => 'inactivo',
        ]);

        $this->actingAs($usuario)
            ->getJson('/api/sesion')
            ->assertStatus(401);

        $this->assertGuest();
    }

    public function test_contrasena_se_almacena_como_hash_y_no_en_texto_plano(): void
    {
        $usuario = $this->crearUsuario('Empleado');

        $guardadoEnBase = DB::table('usuarios')
            ->where('id_usuario', $usuario->id_usuario)
            ->value('contrasena_hash');

        $this->assertNotSame('ClaveDePrueba123!', $guardadoEnBase);
        $this->assertTrue(
            Hash::check('ClaveDePrueba123!', $guardadoEnBase)
        );
        $this->assertStringStartsWith('$2y$', $guardadoEnBase);
    }

    public function test_configuracion_de_cookies_de_sesion_es_segura(): void
    {
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));
    }
}
