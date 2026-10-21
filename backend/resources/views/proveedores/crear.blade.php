@extends('layouts.app')

@section('titulo', 'Registrar proveedor')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Registrar proveedor</h1>
            <p class="subtitulo">Añade un nuevo proveedor al directorio de la librería.</p>
        </div>

        <a href="{{ route('proveedores.index') }}" class="boton boton-secundario">Volver a proveedores</a>
    </div>

    @include('proveedores.formulario', ['proveedor' => null])
@endsection
