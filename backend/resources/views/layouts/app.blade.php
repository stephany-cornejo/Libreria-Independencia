<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', config('app.name'))</title>
    <style>
        :root {
            --color-fondo: #f4f2ec;
            --color-tarjeta: #ffffff;
            --color-texto: #1b1b18;
            --color-secundario: #6b6a65;
            --color-borde: #dcdad2;
            --color-primario: #1b1b18;
            --color-error: #b42318;
            --color-error-fondo: #fef3f2;
            --color-exito: #067647;
            --color-exito-fondo: #ecfdf3;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            padding: 1.5rem;
            background: var(--color-fondo);
            color: var(--color-texto);
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        .marca {
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .tarjeta {
            width: 100%;
            max-width: 26rem;
            background: var(--color-tarjeta);
            border: 1px solid var(--color-borde);
            border-radius: 0.75rem;
            padding: 2rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        h1 {
            margin: 0 0 0.25rem;
            font-size: 1.35rem;
        }

        .subtitulo {
            margin: 0 0 1.5rem;
            color: var(--color-secundario);
            font-size: 0.9rem;
        }

        label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.6rem 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid var(--color-borde);
            border-radius: 0.5rem;
            font-size: 0.95rem;
            background: #fff;
        }

        input:focus {
            outline: 2px solid var(--color-texto);
            outline-offset: 1px;
        }

        .boton {
            display: inline-block;
            width: 100%;
            padding: 0.65rem 1rem;
            border: none;
            border-radius: 0.5rem;
            background: var(--color-primario);
            color: #fff;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }

        .boton:hover {
            opacity: 0.9;
        }

        .boton-secundario {
            background: transparent;
            color: var(--color-texto);
            border: 1px solid var(--color-borde);
            width: auto;
            padding: 0.45rem 0.9rem;
            font-size: 0.85rem;
        }

        .alerta {
            padding: 0.7rem 0.9rem;
            margin-bottom: 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }

        .alerta-error {
            background: var(--color-error-fondo);
            color: var(--color-error);
            border: 1px solid #fecdca;
        }

        .alerta-exito {
            background: var(--color-exito-fondo);
            color: var(--color-exito);
            border: 1px solid #abefc6;
        }

        .barra-superior {
            width: 100%;
            max-width: 40rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .datos-lista {
            list-style: none;
            margin: 0 0 1.5rem;
            padding: 0;
        }

        .datos-lista li {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.6rem 0;
            border-bottom: 1px solid var(--color-borde);
            font-size: 0.9rem;
        }

        .datos-lista li span:first-child {
            color: var(--color-secundario);
        }

        .enlaces {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="marca">{{ config('app.name') }}</div>

    @yield('contenido')
</body>
</html>
