@extends('layouts.app')
@php($proveedor = $proveedor ?? null)
@section('titulo', $proveedor ? 'Editar proveedor' : 'Nuevo proveedor')
@section('contenido')
<div class="g2">
@include('partials.base-grupo2')
<div class="cabecera"><h1>{{ $proveedor ? 'Editar proveedor' : 'Nuevo proveedor' }}</h1></div>
<form method="post" class="formulario"
      action="{{ $proveedor ? route('proveedores.update', $proveedor) : route('proveedores.store') }}">
    @csrf
    @if($proveedor) @method('PUT') @endif
    <label>Nombre
        <input name="nombre" maxlength="100" required value="{{ old('nombre', $proveedor->nombre ?? '') }}">
    </label>
    <label>Teléfono (opcional)
        <input name="telefono" maxlength="20" value="{{ old('telefono', $proveedor->telefono ?? '') }}">
    </label>
    <label>Correo (opcional)
        <input type="email" name="email" maxlength="100" value="{{ old('email', $proveedor->email ?? '') }}">
    </label>
    <label>Dirección (opcional)
        <input name="direccion" maxlength="150" value="{{ old('direccion', $proveedor->direccion ?? '') }}">
    </label>
    <div class="acciones">
        <button>Guardar cambios</button>
        <a href="{{ route('proveedores.index') }}">Cancelar</a>
    </div>
</form>
</div>
@endsection
