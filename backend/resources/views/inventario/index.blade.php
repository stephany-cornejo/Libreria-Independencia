@extends('layouts.app')

@section('titulo', 'Inventario de libros')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Inventario de libros</h1>
            <p class="subtitulo">Consulta, registra y mantiene al día las existencias de la librería.</p>
        </div>

        <a href="{{ route('inventario.crear') }}" class="boton">Registrar libro</a>
    </div>

    {{-- Resumen global de existencias y alertas de stock --}}
    <section class="resumen" aria-label="Resumen del inventario">
        <div class="resumen-tarjeta">
            <span class="resumen-icono resumen-icono-indigo" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                </svg>
            </span>
            <div class="resumen-texto">
                <span class="valor">{{ $resumen->titulos }}</span>
                <span class="etiqueta">Títulos registrados</span>
            </div>
        </div>

        <div class="resumen-tarjeta">
            <span class="resumen-icono resumen-icono-violeta" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </span>
            <div class="resumen-texto">
                <span class="valor">{{ number_format((int) $resumen->unidades) }}</span>
                <span class="etiqueta">Unidades en existencia</span>
            </div>
        </div>

        <div class="resumen-tarjeta">
            <span class="resumen-icono resumen-icono-esmeralda" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </span>
            <div class="resumen-texto">
                <span class="valor">${{ number_format((float) $resumen->valor, 2) }}</span>
                <span class="etiqueta">Valor del inventario</span>
            </div>
        </div>

        <div class="resumen-tarjeta {{ $resumen->bajos > 0 ? 'alerta-baja' : '' }}">
            <span class="resumen-icono resumen-icono-ambar" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </span>
            <div class="resumen-texto">
                <span class="valor">{{ $resumen->bajos }}</span>
                <span class="etiqueta">Con stock bajo</span>
            </div>
        </div>

        <div class="resumen-tarjeta {{ $resumen->agotados > 0 ? 'alerta-agotada' : '' }}">
            <span class="resumen-icono resumen-icono-rosa" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </span>
            <div class="resumen-texto">
                <span class="valor">{{ $resumen->agotados }}</span>
                <span class="etiqueta">Agotados</span>
            </div>
        </div>
    </section>

    {{-- Búsqueda y filtros --}}
    <form class="filtros" method="GET" action="{{ route('inventario.index') }}">
        <div class="campo-filtro campo-busqueda">
            <label for="q">Buscar</label>
            <input
                id="q"
                type="text"
                name="q"
                value="{{ $filtros['q'] }}"
                placeholder="Título o autor…"
                maxlength="100"
            >
        </div>

        <div class="campo-filtro">
            <label for="categoria">Categoría</label>
            <select id="categoria" name="categoria">
                <option value="">Todas las categorías</option>
                @foreach ($categorias as $categoria)
                    <option
                        value="{{ $categoria->id_categoria }}"
                        {{ (int) $filtros['categoria'] === (int) $categoria->id_categoria ? 'selected' : '' }}
                    >{{ $categoria->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="campo-filtro">
            <label for="disponibilidad">Disponibilidad</label>
            <select id="disponibilidad" name="disponibilidad">
                <option value="">Cualquier estado</option>
                <option value="disponible" {{ $filtros['disponibilidad'] === 'disponible' ? 'selected' : '' }}>Disponible</option>
                <option value="bajo" {{ $filtros['disponibilidad'] === 'bajo' ? 'selected' : '' }}>Stock bajo</option>
                <option value="agotado" {{ $filtros['disponibilidad'] === 'agotado' ? 'selected' : '' }}>Agotado</option>
            </select>
        </div>

        <div class="filtros-acciones">
            <button type="submit" class="boton">Buscar</button>

            @if ($filtros['q'] !== '' || $filtros['categoria'] !== null || $filtros['disponibilidad'] !== null)
                <a href="{{ route('inventario.index') }}" class="boton boton-secundario">Limpiar</a>
            @endif
        </div>
    </form>

    @php
        $etiquetasStock = [
            'disponible' => ['Disponible', 'insignia-disponible'],
            'bajo' => ['Stock bajo', 'insignia-bajo'],
            'agotado' => ['Agotado', 'insignia-agotado'],
        ];
        $hayFiltros = $filtros['q'] !== ''
            || $filtros['categoria'] !== null
            || $filtros['disponibilidad'] !== null;
    @endphp

    @if ($productos->count() === 0)
        <div class="estado-vacio">
            <span class="estado-vacio-icono" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                </svg>
            </span>

            @if ($hayFiltros)
                <h3>Ningún libro coincide con la búsqueda</h3>
                <p>Prueba con otros criterios o limpia los filtros para ver todo el inventario.</p>
                <a href="{{ route('inventario.index') }}" class="boton boton-secundario">Limpiar filtros</a>
            @else
                <h3>Todavía no hay libros en el inventario</h3>
                <p>Registra el primer título para empezar a controlar las existencias de la librería.</p>
                <a href="{{ route('inventario.crear') }}" class="boton">Registrar el primer libro</a>
            @endif
        </div>
    @else
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th scope="col">Libro</th>
                        <th scope="col">Autor</th>
                        <th scope="col">Editorial</th>
                        <th scope="col">ISBN</th>
                        <th scope="col">Categoría</th>
                        <th scope="col" class="num">Precio</th>
                        <th scope="col">Existencia</th>
                        <th scope="col" class="num">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $producto)
                        @php($estado = $etiquetasStock[$producto->estadoStock()])
                        <tr>
                            <td>
                                <span class="libro-titulo">{{ $producto->nombre }}</span>
                                <span class="libro-codigo">{{ $producto->codigo }}</span>
                            </td>
                            <td>{{ $producto->autor ?? '—' }}</td>
                            <td>{{ $producto->editorial ?? '—' }}</td>
                            <td class="mono">{{ $producto->isbn ?? '—' }}</td>
                            <td>{{ $producto->categoria->nombre ?? '—' }}</td>
                            <td class="num">${{ number_format((float) $producto->precio_venta, 2) }}</td>
                            <td>
                                <div class="stock-celda">
                                    <span class="stock-numero">{{ $producto->cantidad_disponible }} unid.</span>
                                    <span class="insignia {{ $estado[1] }}">{{ $estado[0] }}</span>
                                </div>
                            </td>
                            <td class="acciones">
                                <a
                                    href="{{ route('inventario.editar', $producto) }}"
                                    class="boton boton-secundario boton-mini"
                                >Editar</a>

                                <button
                                    type="button"
                                    class="boton boton-peligro-tenue boton-mini"
                                    data-eliminar="{{ $producto->nombre }}"
                                    data-url="{{ route('inventario.eliminar', $producto) }}"
                                >Eliminar</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $productos->links('paginacion.simple') }}
    @endif

    {{-- Confirmación de eliminación --}}
    <dialog class="dialogo" id="dialogo-eliminar">
        <div class="dialogo-cuerpo">
            <h2>Eliminar libro</h2>
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
