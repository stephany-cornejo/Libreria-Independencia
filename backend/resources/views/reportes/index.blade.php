@extends('layouts.app')
@section('titulo', 'Reportes')
@section('contenido')
<div class="g2">
@include('partials.base-grupo2')
<div class="cabecera"><h1>Reportes</h1></div>
<div class="tabla-contenedor">
<table>
    <thead><tr><th>Reporte</th><th>Qué muestra</th></tr></thead>
    <tbody>
        <tr><td><a href="{{ route('reportes.existencias') }}">Existencias actuales</a></td><td>Cantidad disponible de todos los productos.</td></tr>
        <tr><td><a href="{{ route('reportes.stock-bajo') }}">Poca existencia</a></td><td>Productos en su mínimo o por debajo.</td></tr>
        <tr><td><a href="{{ route('reportes.agotados') }}">Agotados</a></td><td>Productos con existencia en cero.</td></tr>
        <tr><td><a href="{{ route('reportes.mas-vendidos') }}">Más vendidos</a></td><td>Unidades vendidas en un período.</td></tr>
        <tr><td><a href="{{ route('reportes.poca-rotacion') }}">Poca rotación</a></td><td>Productos con pocos movimientos en un período.</td></tr>
        <tr><td><a href="{{ route('reportes.movimientos') }}">Movimientos por período</a></td><td>Entradas y salidas entre dos fechas.</td></tr>
    </tbody>
</table>
</div>
</div>
@endsection
