@extends('layouts.app')

@section('titulo', 'Configuración')

@section('contenido')
    <div class="tarjeta">
        <h1>Configuración del sistema</h1>
        <p class="subtitulo">Área reservada al rol Administrador.</p>

        <div class="enlaces">
            <a href="{{ route('panel') }}" class="boton boton-secundario">Volver al panel</a>
        </div>
    </div>
@endsection
