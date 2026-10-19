@extends('layouts.app')
@section('titulo', 'Proveedores')
@section('contenido')
<div class="g2">
@include('partials.base-grupo2')
<div class="cabecera">
    <h1>Proveedores</h1>
    <a class="boton" href="{{ route('proveedores.create') }}">Nuevo proveedor</a>
</div>
<form method="get" class="filtros">
    <label>Buscar por nombre
        <input type="search" name="buscar" value="{{ request('buscar') }}">
    </label>
    <button class="secundario">Buscar</button>
</form>
<div class="tabla-contenedor">
<table>
    <thead><tr><th>Nombre</th><th>Teléfono</th><th>Correo</th><th>Dirección</th><th>Productos</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
    @forelse($proveedores as $p)
        <tr>
            <td>{{ $p->nombre }}</td>
            <td>{{ $p->telefono }}</td>
            <td>{{ $p->email }}</td>
            <td>{{ $p->direccion }}</td>
            <td>{{ $p->productos_count }}</td>
            <td><span class="etiqueta {{ $p->estado }}">{{ $p->estado }}</span></td>
            <td class="acciones">
                <a href="{{ route('proveedores.edit', $p) }}">Editar</a>
                <form method="post" action="{{ route('proveedores.estado', $p) }}">
                    @csrf @method('PATCH')
                    <button class="enlace">{{ $p->estado === 'activo' ? 'Desactivar' : 'Activar' }}</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="vacio">Aún no hay proveedores. Registra el primero con «Nuevo proveedor».</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $proveedores->onEachSide(1)->links('pagination::bootstrap-4') }}
</div>
@endsection
