@extends('layouts.app')

@section('titulo', 'Registrar categoría')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Registrar categoría</h1>
            <p class="subtitulo">Añade una nueva categoría para organizar el catálogo.</p>
        </div>

        <a href="{{ route('categorias.index') }}" class="boton boton-secundario">Volver a categorías</a>
    </div>

    @include('categorias.formulario', ['categoria' => null])
@endsection
