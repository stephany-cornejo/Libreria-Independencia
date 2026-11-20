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

    <div class="cabecera-pagina">
        <div class="cabecera-bienvenida">
            <div class="panel-avatar panel-avatar-cabecera" aria-hidden="true">{{ $iniciales }}</div>
            <div>
                <h1>Hola, {{ explode(' ', trim($usuario->nombre))[0] }}</h1>
                <p class="subtitulo">
                    Bienvenido de nuevo. Esto es lo que está pasando en la librería hoy.
                </p>
                <span class="chip-rol">{{ $usuario->rol->nombre }}</span>
            </div>
        </div>

        <div class="cabecera-acciones">
            <a href="{{ route('inventario.crear') }}" class="boton">Registrar libro</a>
            <a href="{{ route('movimientos.crear') }}" class="boton boton-secundario">Nuevo movimiento</a>
        </div>
    </div>

    {{-- Métricas reales del sistema --}}
    <section class="resumen" aria-label="Resumen del sistema">
        @foreach ($estadisticas as $metrica)
            <a href="{{ $metrica['enlace'] }}" class="resumen-tarjeta resumen-enlace">
                <span class="resumen-icono resumen-icono-{{ $metrica['tono'] }}" aria-hidden="true">
                    @if ($metrica['icono'] === 'libros')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                        </svg>
                    @elseif ($metrica['icono'] === 'disponible')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    @elseif ($metrica['icono'] === 'alerta')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    @elseif ($metrica['icono'] === 'movimientos')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="17 1 21 5 17 9"/>
                            <path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                            <polyline points="7 23 3 19 7 15"/>
                            <path d="M21 13v2a4 4 0 0 1-4 4H3"/>
                        </svg>
                    @elseif ($metrica['icono'] === 'usuarios')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                            <line x1="7" y1="7" x2="7.01" y2="7"/>
                        </svg>
                    @endif
                </span>
                <span class="resumen-texto">
                    <span class="valor">{{ $metrica['valor'] }}</span>
                    <span class="etiqueta">{{ $metrica['etiqueta'] }}</span>
                    <span class="resumen-detalle">{{ $metrica['detalle'] }}</span>
                </span>
            </a>
        @endforeach
    </section>

    <div class="panel-rejilla">
        {{-- Accesos rápidos --}}
        <section class="tarjeta tarjeta-plena">
            <h2>Accesos rápidos</h2>
            <p class="subtitulo">Lo que más usarás en el día a día.</p>

            <div class="panel-acciones panel-acciones-sin-borde">
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
                    <span class="panel-accion-flecha" aria-hidden="true">→</span>
                </a>

                <a href="{{ route('movimientos.index') }}" class="panel-accion">
                    <span class="panel-accion-icono resumen-icono-violeta" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="17 1 21 5 17 9"/>
                            <path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                            <polyline points="7 23 3 19 7 15"/>
                            <path d="M21 13v2a4 4 0 0 1-4 4H3"/>
                        </svg>
                    </span>
                    <span class="panel-accion-texto">
                        <strong>Movimientos de inventario</strong>
                        <small>Registra entradas y salidas con su motivo.</small>
                    </span>
                    <span class="panel-accion-flecha" aria-hidden="true">→</span>
                </a>

                @if ($esAdministrador)
                    <a href="{{ route('usuarios.index') }}" class="panel-accion">
                        <span class="panel-accion-icono resumen-icono-rosa" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </span>
                        <span class="panel-accion-texto">
                            <strong>Cuentas de usuario</strong>
                            <small>Alta, edición y permisos de cada rol.</small>
                        </span>
                        <span class="panel-accion-flecha" aria-hidden="true">→</span>
                    </a>

                    <a href="{{ route('categorias.index') }}" class="panel-accion">
                        <span class="panel-accion-icono resumen-icono-esmeralda" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                                <line x1="7" y1="7" x2="7.01" y2="7"/>
                            </svg>
                        </span>
                        <span class="panel-accion-texto">
                            <strong>Categorías y proveedores</strong>
                            <small>Administra los catálogos del sistema.</small>
                        </span>
                        <span class="panel-accion-flecha" aria-hidden="true">→</span>
                    </a>
                @endif
            </div>
        </section>

        {{-- Cuenta --}}
        <aside class="tarjeta tarjeta-plena panel-cuenta">
            <h2>Tu cuenta</h2>
            <p class="subtitulo">Datos de la sesión actual.</p>

            <div class="panel-avatar" aria-hidden="true">{{ $iniciales }}</div>

            <ul class="datos-lista panel-datos">
                <li><span>Nombre</span> <span>{{ $usuario->nombre }}</span></li>
                <li><span>Usuario</span> <span class="mono">{{ $usuario->usuario }}</span></li>
                <li><span>Rol</span> <span>{{ $usuario->rol->nombre }}</span></li>
                <li>
                    <span>Estado</span>
                    <span class="insignia {{ $esActivo ? 'insignia-disponible' : 'insignia-inactivo' }}">
                        {{ $esActivo ? 'Activo' : 'Inactivo' }}
                    </span>
                </li>
            </ul>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="boton boton-secundario boton-ancho">
                    Cerrar sesión
                </button>
            </form>
        </aside>
    </div>
@endsection
