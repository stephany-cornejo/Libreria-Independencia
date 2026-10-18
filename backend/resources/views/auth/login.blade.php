@extends('layouts.app')

@section('titulo', 'Iniciar sesión')

@section('contenido')
    <div class="tarjeta">
        <h1>Iniciar sesión</h1>
        <p class="subtitulo">Introduce tu usuario y contraseña para acceder al sistema.</p>

        @if ($errors->any())
            <div class="alerta alerta-error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf

            <label for="usuario">Usuario</label>
            <input
                id="usuario"
                type="text"
                name="usuario"
                value="{{ old('usuario') }}"
                autocomplete="username"
                maxlength="50"
                required
                autofocus
            >

            <label for="password">Contraseña</label>
            <input
                id="password"
                type="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button type="submit" class="boton">Entrar</button>
        </form>
    </div>
@endsection
