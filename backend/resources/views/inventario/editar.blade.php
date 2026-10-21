@extends('layouts.app')

@section('titulo', 'Editar libro')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Editar libro</h1>
            <p class="subtitulo">Actualiza los datos de «{{ $producto->nombre }}».</p>
        </div>

        <a href="{{ route('inventario.index') }}" class="boton boton-secundario">Volver al inventario</a>
    </div>

    @include('inventario.formulario', ['producto' => $producto])
@endsection
