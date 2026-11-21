<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarUsuarioRequest;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    /**
     * Listado de cuentas con su rol y número de movimientos,
     * con búsqueda por nombre o usuario.
     */
    public function index(Request $request): View
    {
        $texto = trim((string) $request->query('q', ''));

        $rol = is_numeric($request->query('rol'))
            ? (int) $request->query('rol')
            : null;

        $estado = in_array($request->query('estado'), ['activo', 'inactivo'], true)
            ? $request->query('estado')
            : null;

        $usuarios = User::query()
            ->with('rol:id_rol,nombre')
            ->withCount('movimientos')
            ->when($texto !== '', function ($query) use ($texto): void {
                $escapado = addcslashes($texto, '%_\\');
                $query->where(function ($sub) use ($escapado): void {
                    $sub->where('nombre', 'like', "%{$escapado}%")
                        ->orWhere('usuario', 'like', "%{$escapado}%");
                });
            })
            ->when($rol !== null, fn ($query) => $query->where('id_rol', $rol))
            ->when($estado !== null, fn ($query) => $query->where('estado', $estado))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'roles' => Rol::orderBy('nombre')->get(),
            'filtros' => [
                'q' => $texto,
                'rol' => $rol,
                'estado' => $estado,
            ],
        ]);
    }

    /**
     * Formulario para registrar una cuenta nueva.
     */
    public function crear(): View
    {
        return view('usuarios.crear', [
            'roles' => Rol::orderBy('nombre')->get(),
        ]);
    }

    /**
     * Valida y guarda una cuenta. El cast "hashed" del modelo
     * almacena la contraseña como hash bcrypt, nunca en texto plano.
     */
    public function guardar(GuardarUsuarioRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        User::create([
            'nombre' => $datos['nombre'],
            'usuario' => $datos['usuario'],
            'contrasena_hash' => $datos['contrasena'],
            'id_rol' => $datos['id_rol'],
            'estado' => $datos['estado'],
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('exito', 'Cuenta registrada correctamente.');
    }

    /**
     * Formulario para editar una cuenta existente.
     */
    public function editar(User $usuario): View
    {
        return view('usuarios.editar', [
            'usuario' => $usuario->load('rol'),
            'roles' => Rol::orderBy('nombre')->get(),
        ]);
    }

    /**
     * Actualiza los datos de la cuenta. La contraseña es opcional:
     * si se deja en blanco se conserva la actual. No se permite
     * desactivar un último administrador activo, para evitar
     * quedarse sin acceso administrativo.
     */
    public function actualizar(
        User $usuario,
        GuardarUsuarioRequest $request
    ): RedirectResponse {
        $datos = $request->validated();

        $rolNuevo = Rol::find($datos['id_rol']);

        $dejaDeSerAdministradorActivo = $datos['estado'] === 'inactivo'
            || $rolNuevo?->nombre !== 'Administrador';

        if ($dejaDeSerAdministradorActivo && $this->dejariaSinAdministradores($usuario)) {
            return back()->with(
                'error',
                'No se puede desactivar ni cambiar el rol: es el último administrador activo del sistema.'
            )->withInput();
        }

        $actualizacion = [
            'nombre' => $datos['nombre'],
            'usuario' => $datos['usuario'],
            'id_rol' => $datos['id_rol'],
            'estado' => $datos['estado'],
        ];

        if (! empty($datos['contrasena'])) {
            $actualizacion['contrasena_hash'] = $datos['contrasena'];
        }

        $usuario->update($actualizacion);

        return redirect()
            ->route('usuarios.index')
            ->with('exito', 'Cuenta actualizada correctamente.');
    }

    /**
     * Elimina una cuenta. Reglas de negocio:
     *  - no se puede eliminar la propia cuenta;
     *  - no se puede eliminar el último administrador activo;
     *  - la base de datos impide borrar cuentas con movimientos.
     */
    public function eliminar(User $usuario): RedirectResponse
    {
        if ($usuario->id_usuario === Auth::id()) {
            return back()->with(
                'error',
                'No puedes eliminar la cuenta con la que iniciaste sesión.'
            );
        }

        if ($this->dejariaSinAdministradores($usuario)) {
            return back()->with(
                'error',
                'No se puede eliminar: es el último administrador activo del sistema.'
            );
        }

        try {
            $usuario->delete();
        } catch (QueryException $exception) {
            $esRestriccionDeClaveForanea = ($exception->errorInfo[1] ?? null) === 1451;

            if (! $esRestriccionDeClaveForanea) {
                throw $exception;
            }

            return back()->with(
                'error',
                'No se puede eliminar: la cuenta tiene movimientos de inventario registrados.'
            );
        }

        return redirect()
            ->route('usuarios.index')
            ->with('exito', 'Cuenta eliminada correctamente.');
    }

    /**
     * Comprueba si la cuenta es un administrador activo y no queda
     * ningún otro administrador activo: eliminarla o desactivarla
     * dejaría el sistema sin acceso administrativo.
     */
    private function dejariaSinAdministradores(User $usuario): bool
    {
        if ($usuario->estado !== 'activo' || $usuario->rol?->nombre !== 'Administrador') {
            return false;
        }

        return ! User::query()
            ->whereKeyNot($usuario->id_usuario)
            ->where('estado', 'activo')
            ->whereHas('rol', function ($query): void {
                $query->where('nombre', 'Administrador');
            })
            ->exists();
    }
}
