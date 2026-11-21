<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Iniciar sesión') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body class="cuerpo-acceso">
    <div class="acceso">
        {{-- Panel de marca: identidad visual del sistema --}}
        <aside class="acceso-marca" aria-hidden="true">
            <div class="acceso-marca-decoracion">
                <span class="acceso-burbuja acceso-burbuja-1"></span>
                <span class="acceso-burbuja acceso-burbuja-2"></span>
                <span class="acceso-burbuja acceso-burbuja-3"></span>
            </div>

            <div class="acceso-marca-contenido">
                <span class="marca-icono acceso-logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                </span>

                <p class="acceso-marca-nombre">{{ config('app.name') }}</p>
                <h2 class="acceso-marca-titulo">Gestión de inventario para tu librería</h2>
                <p class="acceso-marca-texto">
                    Controla existencias, categorías, proveedores y movimientos
                    desde un solo lugar, con acceso seguro por roles.
                </p>

                <ul class="acceso-beneficios">
                    <li>
                        <span class="acceso-beneficio-icono" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                        Inventario en tiempo real con alertas de stock bajo
                    </li>
                    <li>
                        <span class="acceso-beneficio-icono" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                        Roles de Administrador y Empleado con permisos claros
                    </li>
                    <li>
                        <span class="acceso-beneficio-icono" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                        Historial trazable de cada entrada y salida
                    </li>
                </ul>
            </div>

            <p class="acceso-marca-pie">Sistema de gestión · Librería Independencia</p>
        </aside>

        {{-- Formulario de acceso --}}
        <main class="acceso-zona-formulario">
            <div class="acceso-formulario">
                <header class="acceso-cabecera">
                    <span class="marca-icono acceso-logo-movil" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                        </svg>
                    </span>
                    <h1>Iniciar sesión</h1>
                    <p class="subtitulo">Introduce tu usuario y contraseña para acceder al panel.</p>
                </header>

                @if ($errors->any())
                    <div class="alerta alerta-error" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}" autocomplete="on">
                    @csrf

                    <div class="campo">
                        <label for="usuario">Usuario</label>
                        <div class="campo-con-icono">
                            <span class="campo-icono" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </span>
                            <input
                                id="usuario"
                                type="text"
                                name="usuario"
                                value="{{ old('usuario') }}"
                                autocomplete="username"
                                maxlength="50"
                                placeholder="Ej.: admin.libreria"
                                class="{{ $errors->has('usuario') ? 'con-error' : '' }}"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="campo">
                        <label for="password">Contraseña</label>
                        <div class="campo-con-icono">
                            <span class="campo-icono" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                autocomplete="current-password"
                                placeholder="Tu contraseña"
                                class="{{ $errors->has('password') ? 'con-error' : '' }}"
                                required
                            >
                            <button
                                type="button"
                                class="campo-accion"
                                id="alternar-visibilidad"
                                aria-label="Mostrar u ocultar contraseña"
                                aria-pressed="false"
                            >
                                <svg class="icono-ojo" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="icono-ojo-cerrado" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                    <path d="M1 1l22 22"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="boton boton-ancho boton-acceso">
                        Entrar al panel
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <polyline points="10 17 15 12 10 7"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                    </button>
                </form>

                <footer class="acceso-pie">
                    <p>
                        ¿No recuerdas tu contraseña?
                        <strong>Contacta al administrador del sistema.</strong>
                    </p>
                </footer>
            </div>
        </main>
    </div>

    <script>
        const alternar = document.getElementById('alternar-visibilidad');
        const campo = document.getElementById('password');

        alternar.addEventListener('click', () => {
            const visible = campo.type === 'text';
            campo.type = visible ? 'password' : 'text';
            alternar.setAttribute('aria-pressed', String(!visible));
            alternar.classList.toggle('activo', !visible);
        });

        document.querySelector('form').addEventListener('submit', (evento) => {
            const boton = evento.currentTarget.querySelector('[type="submit"]');
            boton.disabled = true;
        });
    </script>
</body>
</html>
