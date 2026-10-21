@extends('layouts.app')

@section('titulo', 'Proveedores')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Proveedores</h1>
            <p class="subtitulo">Directorio de proveedores de la librería. Solo un administrador puede modificarlo.</p>
        </div>

        <a href="{{ route('proveedores.crear') }}" class="boton">Registrar proveedor</a>
    </div>

    @if ($proveedores->count() === 0)
        <div class="estado-vacio">
            <span class="estado-vacio-icono" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13"/>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                    <circle cx="5.5" cy="18.5" r="2.5"/>
                    <circle cx="18.5" cy="18.5" r="2.5"/>
                </svg>
            </span>
            <h3>Todavía no hay proveedores</h3>
            <p>Registra el primer proveedor para completar el directorio.</p>
            <a href="{{ route('proveedores.crear') }}" class="boton">Registrar el primer proveedor</a>
        </div>
    @else
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th scope="col">Proveedor</th>
                        <th scope="col">Teléfono</th>
                        <th scope="col">Correo</th>
                        <th scope="col">Dirección</th>
                        <th scope="col">Estado</th>
                        <th scope="col" class="num">Libros</th>
                        <th scope="col" class="num">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($proveedores as $proveedor)
                        <tr>
                            <td><span class="libro-titulo">{{ $proveedor->nombre }}</span></td>
                            <td class="mono">{{ $proveedor->telefono ?? '—' }}</td>
                            <td class="mono">{{ $proveedor->email ?? '—' }}</td>
                            <td class="texto-secundario">{{ $proveedor->direccion ?? '—' }}</td>
                            <td>
                                <span class="insignia {{ $proveedor->estado === 'activo' ? 'insignia-disponible' : 'insignia-inactivo' }}">
                                    {{ $proveedor->estado === 'activo' ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="num">{{ $proveedor->productos_count }} unid.</td>
                            <td class="acciones">
                                <a
                                    href="{{ route('proveedores.editar', $proveedor) }}"
                                    class="boton boton-secundario boton-mini"
                                >Editar</a>

                                <button
                                    type="button"
                                    class="boton boton-peligro-tenue boton-mini"
                                    data-eliminar="{{ $proveedor->nombre }}"
                                    data-url="{{ route('proveedores.eliminar', $proveedor) }}"
                                >Eliminar</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $proveedores->links('paginacion.simple') }}
    @endif

    {{-- Confirmación de eliminación --}}
    <dialog class="dialogo" id="dialogo-eliminar">
        <div class="dialogo-cuerpo">
            <h2>Eliminar proveedor</h2>
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
