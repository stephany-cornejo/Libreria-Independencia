@extends('layouts.app')

@section('titulo', 'Editar categoría')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Editar categoría</h1>
            <p class="subtitulo">Actualiza los datos de la categoría.</p>
        </div>

        <a href="{{ route('categorias.index') }}" class="boton boton-secundario">Volver a categorías</a>
    </div>

    @include('categorias.formulario')
@endsection
