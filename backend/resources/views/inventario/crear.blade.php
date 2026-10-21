@extends('layouts.app')

@section('titulo', 'Registrar libro')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Registrar libro</h1>
            <p class="subtitulo">Añade un nuevo título al inventario de la librería.</p>
        </div>

        <a href="{{ route('inventario.index') }}" class="boton boton-secundario">Volver al inventario</a>
    </div>

    @include('inventario.formulario', ['producto' => null])
@endsection
