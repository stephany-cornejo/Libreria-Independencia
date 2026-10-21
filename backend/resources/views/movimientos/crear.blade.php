@extends('layouts.app')

@section('titulo', 'Registrar movimiento')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Registrar movimiento</h1>
            <p class="subtitulo">Entrada o salida de existencias con su motivo y observaciones.</p>
        </div>

        <a href="{{ route('movimientos.index') }}" class="boton boton-secundario">Volver al historial</a>
    </div>

    @include('movimientos.formulario')
@endsection
