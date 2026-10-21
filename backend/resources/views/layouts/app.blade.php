<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', config('app.name'))</title>
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body>
    @auth
        <header class="barra-navegacion">
            <div class="barra-navegacion-interno">
                <div class="barra-grupo">
                    <a href="{{ route('panel') }}" class="marca">
                        <span class="marca-icono" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                            </svg>
                        </span>
                        {{ config('app.name') }}
                    </a>

                    <nav class="navegacion-principal" aria-label="Navegación principal">
                        <a
                            href="{{ route('panel') }}"
                            class="enlace-nav {{ request()->routeIs('panel') ? 'activo' : '' }}"
                        >Panel</a>

                        <a
                            href="{{ route('inventario.index') }}"
                            class="enlace-nav {{ request()->routeIs('inventario.*') ? 'activo' : '' }}"
                        >Inventario</a>

                        <a
                            href="{{ route('movimientos.index') }}"
                            class="enlace-nav {{ request()->routeIs('movimientos.*') ? 'activo' : '' }}"
                        >Movimientos</a>

                        @if (Auth::user()->rol->nombre === 'Administrador')
                            <a
                                href="{{ route('categorias.index') }}"
                                class="enlace-nav {{ request()->routeIs('categorias.*') ? 'activo' : '' }}"
                            >Categorías</a>

                            <a
                                href="{{ route('proveedores.index') }}"
                                class="enlace-nav {{ request()->routeIs('proveedores.*') ? 'activo' : '' }}"
                            >Proveedores</a>

                            <a
                                href="{{ route('panel.configuracion') }}"
                                class="enlace-nav {{ request()->routeIs('panel.configuracion') ? 'activo' : '' }}"
                            >Configuración</a>
                        @endif
                    </nav>
                </div>

                <div class="barra-grupo">
                    <span class="usuario-barra">
                        <strong>{{ Auth::user()->nombre }}</strong>
                        <span class="chip-rol">{{ Auth::user()->rol->nombre }}</span>
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="boton boton-secundario boton-mini">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="contenedor">
            @if (session('exito'))
                <div class="alerta alerta-exito" role="status">
                    {{ session('exito') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alerta alerta-error" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @yield('contenido')
        </main>
    @endauth

    @guest
        <main class="zona-centrada">
            <div class="marca">
                <span class="marca-icono" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                </span>
                {{ config('app.name') }}
            </div>

            @yield('contenido')
        </main>
    @endguest
</body>
</html>
