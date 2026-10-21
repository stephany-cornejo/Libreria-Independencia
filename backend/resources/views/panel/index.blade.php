@extends('layouts.app')

@section('titulo', 'Panel de control')

@section('contenido')
    @php
        $iniciales = collect(explode(' ', trim($usuario->nombre)))
            ->filter()
            ->take(2)
            ->map(fn (string $parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
            ->implode('');
        $esActivo = $usuario->estado === 'activo';
    @endphp

    <div class="panel-inicio">
        <div class="panel-tarjeta">
            <div class="panel-avatar" aria-hidden="true">{{ $iniciales }}</div>

            <h1>Panel de control</h1>
            <p class="subtitulo">
                Bienvenido, {{ $usuario->nombre }}. Todo listo para trabajar en la librería.
            </p>

            <span class="chip-rol">{{ $usuario->rol->nombre }}</span>

            <ul class="datos-lista panel-datos">
                <li><span>Nombre</span> <span>{{ $usuario->nombre }}</span></li>
                <li><span>Usuario</span> <span>{{ $usuario->usuario }}</span></li>
                <li><span>Rol</span> <span>{{ $usuario->rol->nombre }}</span></li>
                <li>
                    <span>Estado</span>
                    <span class="insignia {{ $esActivo ? 'insignia-disponible' : 'insignia-agotado' }}">
                        {{ $esActivo ? 'Activo' : 'Inactivo' }}
                    </span>
                </li>
            </ul>

            <div class="panel-acciones">
                <a href="{{ route('inventario.index') }}" class="panel-accion">
                    <span class="panel-accion-icono resumen-icono-indigo" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                        </svg>
                    </span>
                    <span class="panel-accion-texto">
                        <strong>Inventario de libros</strong>
                        <small>Consulta, registra y controla las existencias.</small>
                    </span>
                </a>

                @if ($usuario->rol->nombre === 'Administrador')
                    <a href="{{ route('panel.configuracion') }}" class="panel-accion">
                        <span class="panel-accion-icono resumen-icono-violeta" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                            </svg>
                        </span>
                        <span class="panel-accion-texto">
                            <strong>Configuración del sistema</strong>
                            <small>Administra usuarios, roles y parámetros.</small>
                        </span>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
