@extends('layouts.app')

@section('titulo', 'Registrar cuenta')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Registrar cuenta</h1>
            <p class="subtitulo">Crea un acceso con su rol para el personal de la librería.</p>
        </div>

        <a href="{{ route('usuarios.index') }}" class="boton boton-secundario">Volver a cuentas</a>
    </div>

    @include('usuarios.formulario', ['usuario' => null])
@endsection
