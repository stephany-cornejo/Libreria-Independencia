@extends('layouts.app')

@section('titulo', 'Catálogos React')

@push('head')
    @viteReactRefresh
    @vite('resources/js/catalogos.jsx')
@endpush

@section('contenido')
    <div id="catalogos-react"></div>
@endsection
