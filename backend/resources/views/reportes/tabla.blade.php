@extends('layouts.app')
@section('titulo', $titulo)
@section('contenido')
<div class="g2">
@include('partials.base-grupo2')
<div class="cabecera">
    <h1>{{ $titulo }}</h1>
    <a class="boton secundario" href="#" onclick="window.print(); return false;">Imprimir</a>
</div>

@if(isset($filtros['periodo']))
<form method="get" class="filtros">
    <label>Desde <input type="date" name="desde" value="{{ $filtros['periodo'][0] }}"></label>
    <label>Hasta <input type="date" name="hasta" value="{{ $filtros['periodo'][1] }}"></label>
    @if(isset($filtros['umbral']))
        <label>Máximo de movimientos <input type="number" name="umbral" min="0" max="1000" value="{{ $filtros['umbral'] }}"></label>
    @endif
    <button>Ver reporte</button>
</form>
@endif

<div class="tabla-contenedor">
<table>
    <thead><tr>@foreach($columnas as $etiqueta)<th>{{ $etiqueta }}</th>@endforeach</tr></thead>
    <tbody>
    @forelse($filas as $fila)
        <tr>@foreach($columnas as $clave => $etiqueta)<td>{{ data_get($fila, $clave) }}</td>@endforeach</tr>
    @empty
        <tr><td colspan="{{ count($columnas) }}" class="vacio">{{ $vacio }}</td></tr>
    @endforelse
    </tbody>
</table>
</div>
</div>
@endsection
