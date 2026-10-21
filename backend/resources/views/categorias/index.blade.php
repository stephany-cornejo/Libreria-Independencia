@extends('layouts.app')

@section('titulo', 'Categorías')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Categorías</h1>
            <p class="subtitulo">Organiza los libros por género o línea. Solo un administrador puede modificarlas.</p>
        </div>

        <a href="{{ route('categorias.crear') }}" class="boton">Registrar categoría</a>
    </div>

    @if ($categorias->count() === 0)
        <div class="estado-vacio">
            <span class="estado-vacio-icono" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>
            </span>
            <h3>Todavía no hay categorías</h3>
            <p>Registra la primera categoría para empezar a organizar el catálogo.</p>
            <a href="{{ route('categorias.crear') }}" class="boton">Registrar la primera categoría</a>
        </div>
    @else
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th scope="col">Categoría</th>
                        <th scope="col">Descripción</th>
                        <th scope="col">Estado</th>
                        <th scope="col" class="num">Libros</th>
                        <th scope="col" class="num">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categorias as $categoria)
                        <tr>
                            <td><span class="libro-titulo">{{ $categoria->nombre }}</span></td>
                            <td class="texto-secundario">{{ $categoria->descripcion ?? '—' }}</td>
                            <td>
                                <span class="insignia {{ $categoria->estado === 'activo' ? 'insignia-disponible' : 'insignia-inactivo' }}">
                                    {{ $categoria->estado === 'activo' ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="num">{{ $categoria->productos_count }} unid.</td>
                            <td class="acciones">
                                <a
                                    href="{{ route('categorias.editar', $categoria) }}"
                                    class="boton boton-secundario boton-mini"
                                >Editar</a>

                                <button
                                    type="button"
                                    class="boton boton-peligro-tenue boton-mini"
                                    data-eliminar="{{ $categoria->nombre }}"
                                    data-url="{{ route('categorias.eliminar', $categoria) }}"
                                >Eliminar</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $categorias->links('paginacion.simple') }}
    @endif

    {{-- Confirmación de eliminación --}}
    <dialog class="dialogo" id="dialogo-eliminar">
        <div class="dialogo-cuerpo">
            <h2>Eliminar categoría</h2>
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
