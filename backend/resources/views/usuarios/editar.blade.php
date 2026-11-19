@extends('layouts.app')

@section('titulo', 'Editar cuenta')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Editar cuenta</h1>
            <p class="subtitulo">Actualiza los datos, el rol o el estado de la cuenta.</p>
        </div>

        <a href="{{ route('usuarios.index') }}" class="boton boton-secundario">Volver a cuentas</a>
    </div>

    @include('usuarios.formulario')
@endsection
