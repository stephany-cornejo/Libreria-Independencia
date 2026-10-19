@extends('layouts.app')
@section('titulo', 'Historial de movimientos')
@section('contenido')
<div class="g2">
@include('partials.base-grupo2')
<div class="cabecera"><h1>Historial de movimientos</h1></div>

<form method="get" class="filtros">
    <label>Desde <input type="date" name="desde" value="{{ request('desde') }}"></label>
    <label>Hasta <input type="date" name="hasta" value="{{ request('hasta') }}"></label>
    <label>Producto
        <select name="id_producto">
            <option value="">Todos</option>
            @foreach($productos as $p)
                <option value="{{ $p->id_producto }}" @selected(request('id_producto') == $p->id_producto)>{{ $p->nombre }}</option>
            @endforeach
        </select>
    </label>
    <label>Categoría
        <select name="id_categoria">
            <option value="">Todas</option>
            @foreach($categorias as $c)
                <option value="{{ $c->id_categoria }}" @selected(request('id_categoria') == $c->id_categoria)>{{ $c->nombre }}</option>
            @endforeach
        </select>
    </label>
    <label>Tipo
        <select name="tipo">
            <option value="">Entradas y salidas</option>
            <option value="entrada" @selected(request('tipo') === 'entrada')>Entradas</option>
            <option value="salida" @selected(request('tipo') === 'salida')>Salidas</option>
        </select>
    </label>
    @if($esAdmin)
    <label>Usuario
        <select name="id_usuario">
            <option value="">Todos</option>
            @foreach($usuarios as $u)
                <option value="{{ $u->id_usuario }}" @selected(request('id_usuario') == $u->id_usuario)>{{ $u->nombre }}</option>
            @endforeach
        </select>
    </label>
    @endif
    <button>Filtrar</button>
    <a href="{{ route('historial.index') }}">Quitar filtros</a>
</form>

<div class="tabla-contenedor">
<table>
    <thead><tr><th>Fecha</th><th>Código</th><th>Producto</th><th>Tipo</th><th>Motivo</th><th>Cantidad</th><th>Existencia resultante</th><th>Usuario</th><th>Observaciones</th></tr></thead>
    <tbody>
    @forelse($movimientos as $m)
        <tr>
            <td>{{ $m->fecha }}</td><td>{{ $m->codigo }}</td><td>{{ $m->producto }}</td>
            <td>{{ $m->tipo }}</td><td>{{ $m->motivo }}</td><td>{{ $m->cantidad }}</td>
            <td>{{ $m->existencia_resultante }}</td><td>{{ $m->usuario }}</td><td>{{ $m->observaciones }}</td>
        </tr>
    @empty
        <tr><td colspan="9" class="vacio">No hay movimientos con esos filtros.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $movimientos->onEachSide(1)->links('pagination::bootstrap-4') }}
</div>
@endsection
