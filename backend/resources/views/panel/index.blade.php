@extends('layouts.app')

@section('titulo', 'Panel')

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
        <h1>Panel de control</h1>
        <p class="subtitulo">Sesión iniciada correctamente.</p>

        <ul class="datos-lista">
            <li><span>Nombre</span> <span>{{ $usuario->nombre }}</span></li>
            <li><span>Usuario</span> <span>{{ $usuario->usuario }}</span></li>
            <li><span>Rol</span> <span>{{ $usuario->rol->nombre }}</span></li>
            <li><span>Estado</span> <span>{{ $usuario->estado }}</span></li>
        </ul>

        <div class="enlaces">
            @if ($usuario->rol->nombre === 'Administrador')
                <a href="{{ route('panel.configuracion') }}" class="boton boton-secundario">
                    Configuración del sistema
                </a>
            @endif
        </div>
    </div>
@endsection
