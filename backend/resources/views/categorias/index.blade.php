@extends('layouts.app')
@section('titulo', 'Categorías')
@section('contenido')
<div class="g2">
@include('partials.base-grupo2')
<div class="cabecera">
    <h1>Categorías</h1>
    <a class="boton" href="{{ route('categorias.create') }}">Nueva categoría</a>
</div>
<form method="get" class="filtros">
    <label>Buscar por nombre
        <input type="search" name="buscar" value="{{ request('buscar') }}">
    </label>
    <button class="secundario">Buscar</button>
</form>
<div class="tabla-contenedor">
<table>
    <thead><tr><th>Nombre</th><th>Descripción</th><th>Productos</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
    @forelse($categorias as $c)
        <tr>
            <td>{{ $c->nombre }}</td>
            <td>{{ $c->descripcion }}</td>
            <td>{{ $c->productos_count }}</td>
            <td><span class="etiqueta {{ $c->estado }}">{{ $c->estado }}</span></td>
            <td class="acciones">
                <a href="{{ route('categorias.edit', $c) }}">Editar</a>
                <form method="post" action="{{ route('categorias.estado', $c) }}">
                    @csrf @method('PATCH')
                    <button class="enlace">{{ $c->estado === 'activo' ? 'Desactivar' : 'Activar' }}</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="vacio">Aún no hay categorías. Crea la primera con «Nueva categoría».</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $categorias->onEachSide(1)->links('pagination::bootstrap-4') }}
</div>
@endsection
