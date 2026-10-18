@extends('layouts.app')

@section('titulo', 'Configuración')

@section('contenido')
    <div class="barra-superior">
        <div>
            <strong>{{ $usuario->nombre }}</strong>
            <span class="subtitulo" style="display:block; margin:0;">Rol: {{ $usuario->rol->nombre }}</span>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="boton boton-secundario">Cerrar sesión</button>
        </form>
    </div>

    <div class="tarjeta">
        <h1>Configuración del sistema</h1>
        <p class="subtitulo">Área reservada al rol Administrador.</p>

        <div class="enlaces">
            <a href="{{ route('panel') }}" class="boton boton-secundario">Volver al panel</a>
        </div>
    </div>
@endsection
