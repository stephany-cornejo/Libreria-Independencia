@extends('layouts.app')

@section('titulo', 'Editar proveedor')

@section('contenido')
    <div class="cabecera-pagina">
        <div>
            <h1>Editar proveedor</h1>
            <p class="subtitulo">Actualiza los datos del proveedor.</p>
        </div>

        <a href="{{ route('proveedores.index') }}" class="boton boton-secundario">Volver a proveedores</a>
    </div>

    @include('proveedores.formulario')
@endsection
