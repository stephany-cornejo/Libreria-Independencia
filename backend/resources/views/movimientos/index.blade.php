@extends('layouts.app')

@section('titulo', 'Movimientos de inventario')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Movimientos de inventario</h1>
            <p class="subtitulo">Historial de entradas y salidas de libros. Las correcciones se registran como ajustes.</p>
        </div>

        <a href="{{ route('movimientos.crear') }}" class="boton">Registrar movimiento</a>
    </div>

    {{-- Búsqueda y filtros --}}
    <form class="filtros" method="GET" action="{{ route('movimientos.index') }}">
        <div class="campo-filtro campo-busqueda">
            <label for="q">Buscar</label>
            <input
                id="q"
                type="text"
                name="q"
                value="{{ $filtros['q'] }}"
                placeholder="Título o código del libro…"
                maxlength="100"
            >
        </div>

        <div class="campo-filtro">
            <label for="tipo">Tipo</label>
            <select id="tipo" name="tipo">
                <option value="">Todos los tipos</option>
                <option value="entrada" {{ $filtros['tipo'] === 'entrada' ? 'selected' : '' }}>Entrada</option>
                <option value="salida" {{ $filtros['tipo'] === 'salida' ? 'selected' : '' }}>Salida</option>
            </select>
        </div>

        <div class="filtros-acciones">
            <button type="submit" class="boton">Buscar</button>

            @if ($filtros['q'] !== '' || $filtros['tipo'] !== null)
                <a href="{{ route('movimientos.index') }}" class="boton boton-secundario">Limpiar</a>
            @endif
        </div>
    </form>

    @php
        $etiquetasTipo = [
            'entrada' => ['Entrada', 'insignia-disponible'],
            'salida' => ['Salida', 'insignia-agotado'],
        ];
        $hayFiltros = $filtros['q'] !== '' || $filtros['tipo'] !== null;
    @endphp

    @if ($movimientos->count() === 0)
        <div class="estado-vacio">
            <span class="estado-vacio-icono" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/>
                    <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
                </svg>
            </span>

            @if ($hayFiltros)
                <h3>Ningún movimiento coincide con la búsqueda</h3>
                <p>Prueba con otros criterios o limpia los filtros para ver todo el historial.</p>
                <a href="{{ route('movimientos.index') }}" class="boton boton-secundario">Limpiar filtros</a>
            @else
                <h3>Todavía no hay movimientos registrados</h3>
                <p>Registra la primera entrada o salida para empezar el historial de inventario.</p>
                <a href="{{ route('movimientos.crear') }}" class="boton">Registrar el primer movimiento</a>
            @endif
        </div>
    @else
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th scope="col">Fecha</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Motivo</th>
                        <th scope="col">Libro</th>
                        <th scope="col" class="num">Cantidad</th>
                        <th scope="col" class="num">Existencia resultante</th>
                        <th scope="col">Registró</th>
                        <th scope="col">Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movimientos as $movimiento)
                        @php($estadoTipo = $etiquetasTipo[$movimiento->tipo])
                        <tr>
                            <td>{{ $movimiento->fecha->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="insignia {{ $estadoTipo[1] }}">{{ $estadoTipo[0] }}</span>
                            </td>
                            <td>{{ ucfirst($movimiento->motivo) }}</td>
                            <td>
                                <span class="libro-titulo">{{ $movimiento->producto->nombre ?? '—' }}</span>
                                <span class="libro-codigo">{{ $movimiento->producto->codigo ?? '' }}</span>
                            </td>
                            <td class="num">{{ $movimiento->cantidad }} unid.</td>
                            <td class="num">{{ $movimiento->existencia_resultante }} unid.</td>
                            <td>{{ $movimiento->usuario->nombre ?? '—' }}</td>
                            <td class="texto-secundario">{{ $movimiento->observaciones ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $movimientos->links('paginacion.simple') }}
    @endif
@endsection
