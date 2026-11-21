@extends('layouts.app')

@section('titulo', $producto->nombre)

@section('contenido')
    @php
        $etiquetasStock = [
            'disponible' => ['Disponible', 'insignia-disponible'],
            'bajo' => ['Stock bajo', 'insignia-bajo'],
            'agotado' => ['Agotado', 'insignia-agotado'],
        ];
        $estadoStock = $etiquetasStock[$producto->estadoStock()];
        $etiquetasTipo = [
            'entrada' => ['Entrada', 'insignia-disponible'],
            'salida' => ['Salida', 'insignia-agotado'],
        ];
        $activo = $producto->estado === 'activo';
    @endphp

    <div class="cabecera-pagina">
        <div>
            <h1>{{ $producto->nombre }}</h1>
            <p class="subtitulo">
                Código <span class="mono">{{ $producto->codigo }}</span>
                @if (! $activo)
                    · <span class="insignia insignia-inactivo">Desactivado</span>
                @endif
            </p>
        </div>

        <div class="acciones">
            <a href="{{ route('inventario.index') }}" class="boton boton-secundario">Volver al inventario</a>
            <a href="{{ route('inventario.editar', $producto) }}" class="boton">Editar</a>
        </div>
    </div>

    {{-- Existencias --}}
    <section class="resumen" aria-label="Existencias del libro">
        <div class="resumen-tarjeta {{ $activo && $producto->estadoStock() === 'bajo' ? 'alerta-baja' : '' }} {{ $activo && $producto->estadoStock() === 'agotado' ? 'alerta-agotada' : '' }}">
            <div class="resumen-texto">
                <span class="valor">{{ $producto->cantidad_disponible }}</span>
                <span class="etiqueta">Unidades en existencia</span>
            </div>
        </div>

        <div class="resumen-tarjeta">
            <div class="resumen-texto">
                <span class="valor">{{ $producto->existencia_minima }}</span>
                <span class="etiqueta">Existencia mínima</span>
            </div>
        </div>

        <div class="resumen-tarjeta">
            <div class="resumen-texto">
                <span class="valor">${{ number_format((float) $producto->precio_venta, 2) }}</span>
                <span class="etiqueta">Precio de venta</span>
            </div>
        </div>

        <div class="resumen-tarjeta">
            <div class="resumen-texto">
                <span class="valor">
                    @if ($activo)
                        <span class="insignia {{ $estadoStock[1] }}">{{ $estadoStock[0] }}</span>
                    @else
                        <span class="insignia insignia-inactivo">Desactivado</span>
                    @endif
                </span>
                <span class="etiqueta">Estado</span>
            </div>
        </div>
    </section>

    {{-- Datos del libro --}}
    <div class="tabla-contenedor">
        <table class="tabla">
            <tbody>
                <tr>
                    <th scope="row">Autor</th>
                    <td>{{ $producto->autor ?? '—' }}</td>
                </tr>
                <tr>
                    <th scope="row">Editorial</th>
                    <td>{{ $producto->editorial ?? '—' }}</td>
                </tr>
                <tr>
                    <th scope="row">ISBN</th>
                    <td class="mono">{{ $producto->isbn ?? '—' }}</td>
                </tr>
                <tr>
                    <th scope="row">Categoría</th>
                    <td>{{ $producto->categoria->nombre ?? '—' }}</td>
                </tr>
                <tr>
                    <th scope="row">Proveedor</th>
                    <td>
                        @if ($producto->proveedor)
                            {{ $producto->proveedor->nombre }}
                            @if ($producto->proveedor->telefono || $producto->proveedor->email)
                                <span class="texto-secundario">
                                    · {{ collect([$producto->proveedor->telefono, $producto->proveedor->email])->filter()->implode(' · ') }}
                                </span>
                            @endif
                        @else
                            Sin proveedor
                        @endif
                    </td>
                </tr>
                <tr>
                    <th scope="row">Fecha de ingreso</th>
                    <td>{{ $producto->fecha_ingreso?->format('d/m/Y') ?? '—' }}</td>
                </tr>
                <tr>
                    <th scope="row">Descripción</th>
                    <td>{{ $producto->descripcion ?? '—' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Últimos movimientos --}}
    <div class="cabecera-pagina">
        <div>
            <h2>Últimos movimientos</h2>
            <p class="subtitulo">Los 10 movimientos más recientes de este libro.</p>
        </div>

        <div class="acciones">
            <a href="{{ route('movimientos.index', ['q' => $producto->codigo]) }}" class="boton boton-secundario">Ver historial completo</a>
            @if ($activo)
                <a href="{{ route('movimientos.crear') }}" class="boton">Registrar movimiento</a>
            @endif
        </div>
    </div>

    @if ($movimientos->count() === 0)
        <div class="estado-vacio">
            <h3>Este libro todavía no tiene movimientos</h3>
            <p>Cuando registres entradas o salidas, aparecerán aquí con su fecha y responsable.</p>
        </div>
    @else
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th scope="col">Fecha</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Motivo</th>
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
                            <td class="num">{{ $movimiento->cantidad }} unid.</td>
                            <td class="num">{{ $movimiento->existencia_resultante }} unid.</td>
                            <td>{{ $movimiento->usuario->nombre ?? '—' }}</td>
                            <td class="texto-secundario">{{ $movimiento->observaciones ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection