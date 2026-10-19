@extends('layouts.app')
@php($categoria = $categoria ?? null)
@section('titulo', $categoria ? 'Editar categoría' : 'Nueva categoría')
@section('contenido')
<div class="g2">
@include('partials.base-grupo2')
<div class="cabecera"><h1>{{ $categoria ? 'Editar categoría' : 'Nueva categoría' }}</h1></div>
<form method="post" class="formulario"
      action="{{ $categoria ? route('categorias.update', $categoria) : route('categorias.store') }}">
    @csrf
    @if($categoria) @method('PUT') @endif
    <label>Nombre
        <input name="nombre" maxlength="50" required value="{{ old('nombre', $categoria->nombre ?? '') }}">
    </label>
    <label>Descripción (opcional)
        <textarea name="descripcion" maxlength="150" rows="3">{{ old('descripcion', $categoria->descripcion ?? '') }}</textarea>
    </label>
    <div class="acciones">
        <button>Guardar cambios</button>
        <a href="{{ route('categorias.index') }}">Cancelar</a>
    </div>
</form>
</div>
@endsection
