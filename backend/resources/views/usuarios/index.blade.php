@extends('layouts.app')

@section('titulo', 'Cuentas de usuario')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Cuentas de usuario</h1>
            <p class="subtitulo">Administración de accesos y roles. Solo un administrador puede modificarlas.</p>
        </div>

        <a href="{{ route('usuarios.crear') }}" class="boton">Registrar cuenta</a>
    </div>

    {{-- Búsqueda --}}
    <form class="filtros" method="GET" action="{{ route('usuarios.index') }}">
        <div class="campo-filtro campo-busqueda">
            <label for="q">Buscar</label>
            <input
                id="q"
                type="text"
                name="q"
                value="{{ $filtros['q'] }}"
                placeholder="Nombre o usuario…"
                maxlength="100"
            >
        </div>
        <div class="campo-filtro">
            <label for="rol">Rol</label>
            <select id="rol" name="rol">
                <option value="">Todos los roles</option>
                @foreach ($roles as $rolOpcion)
                    <option
                        value="{{ $rolOpcion->id_rol }}"
                        {{ (int) $filtros['rol'] === (int) $rolOpcion->id_rol ? 'selected' : '' }}
                    >{{ $rolOpcion->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="campo-filtro">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="">Cualquier estado</option>
                <option value="activo" {{ $filtros['estado'] === 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="inactivo" {{ $filtros['estado'] === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
        
        <div class="filtros-acciones">
            <button type="submit" class="boton">Buscar</button>

            @if ($filtros['q'] !== '' || $filtros['rol'] !== null || $filtros['estado'] !== null)
                <a href="{{ route('usuarios.index') }}" class="boton boton-secundario">Limpiar</a>
            @endif
        </div>
    </form>

    @if ($usuarios->count() === 0)
        <div class="estado-vacio">
            <span class="estado-vacio-icono" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </span>
            <h3>Todavía no hay cuentas</h3>
            <p>Registra la primera cuenta para dar acceso al sistema.</p>
            <a href="{{ route('usuarios.crear') }}" class="boton">Registrar la primera cuenta</a>
        </div>
    @else
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th scope="col">Nombre</th>
                        <th scope="col">Usuario</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Estado</th>
                        <th scope="col" class="num">Movimientos</th>
                        <th scope="col" class="num">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $cuenta)
                        <tr>
                            <td><span class="libro-titulo">{{ $cuenta->nombre }}</span></td>
                            <td class="texto-secundario">{{ $cuenta->usuario }}</td>
                            <td>{{ $cuenta->rol->nombre }}</td>
                            <td>
                                <span class="insignia {{ $cuenta->estado === 'activo' ? 'insignia-disponible' : 'insignia-inactivo' }}">
                                    {{ $cuenta->estado === 'activo' ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="num">{{ $cuenta->movimientos_count }}</td>
                            <td class="acciones">
                                <a
                                    href="{{ route('usuarios.editar', $cuenta) }}"
                                    class="boton boton-secundario boton-mini"
                                >Editar</a>

                                @if ($cuenta->id_usuario !== Auth::id())
                                    <button
                                        type="button"
                                        class="boton boton-peligro-tenue boton-mini"
                                        data-eliminar="{{ $cuenta->nombre }}"
                                        data-url="{{ route('usuarios.eliminar', $cuenta) }}"
                                    >Eliminar</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $usuarios->links('paginacion.simple') }}
    @endif

    {{-- Confirmación de eliminación --}}
    <dialog class="dialogo" id="dialogo-eliminar">
        <div class="dialogo-cuerpo">
            <h2>Eliminar cuenta</h2>
            <p>
                ¿Seguro que deseas eliminar <strong id="nombre-eliminar"></strong>?
                Esta acción no se puede deshacer.
            </p>
        </div>

        <div class="dialogo-acciones">
            <button type="button" class="boton boton-secundario" id="cancelar-eliminar">
                Cancelar
            </button>

            <form method="POST" id="form-eliminar" action="">
                @csrf
                @method('DELETE')
                <button type="submit" class="boton boton-peligro">Eliminar</button>
            </form>
        </div>
    </dialog>

    <script>
        const dialogo = document.getElementById('dialogo-eliminar');

        document.querySelectorAll('[data-eliminar]').forEach((boton) => {
            boton.addEventListener('click', () => {
                document.getElementById('nombre-eliminar').textContent = boton.dataset.eliminar;
                document.getElementById('form-eliminar').action = boton.dataset.url;
                dialogo.showModal();
            });
        });

        document.getElementById('cancelar-eliminar')
            .addEventListener('click', () => dialogo.close());

        dialogo.addEventListener('click', (evento) => {
            if (evento.target === dialogo) {
                dialogo.close();
            }
        });
    </script>
@endsection
