<style>
.g2 { --tinta:#1d2733; --linea:#d3dbe1; --acento:#1b5e63; --alerta:#b3261e; --ok:#1f6b3a; color:var(--tinta); font-family:system-ui,-apple-system,"Segoe UI",sans-serif; line-height:1.5; max-width:1100px; margin:1.5rem auto; padding:0 1.25rem; }
.g2 *, .g2 *::before, .g2 *::after { box-sizing:border-box; }
.g2 h1 { font-size:1.5rem; margin:0; }
.g2 .menu { display:flex; gap:1.25rem; flex-wrap:wrap; padding:.6rem .9rem; background:var(--tinta); border-radius:4px; margin-bottom:1.25rem; }
.g2 .menu a { color:#dbe7ea; text-decoration:none; }
.g2 .menu a:hover, .g2 .menu a:focus-visible { color:#fff; text-decoration:underline; }
.g2 .cabecera { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1rem; flex-wrap:wrap; }
.g2 .boton, .g2 button { background:var(--acento); color:#fff; border:0; border-radius:4px; padding:.5rem .9rem; font:inherit; cursor:pointer; text-decoration:none; display:inline-block; }
.g2 .boton.secundario, .g2 button.secundario { background:#fff; color:var(--acento); border:1px solid var(--acento); }
.g2 button.enlace { background:none; color:var(--acento); padding:0; text-decoration:underline; }
.g2 a { color:var(--acento); }
.g2 :focus-visible { outline:2px solid var(--acento); outline-offset:2px; }
.g2 .tabla-contenedor { overflow-x:auto; background:#fff; border:1px solid var(--linea); border-radius:4px; }
.g2 table { width:100%; border-collapse:collapse; }
.g2 th, .g2 td { text-align:left; padding:.55rem .75rem; border-bottom:1px solid var(--linea); vertical-align:top; }
.g2 th { background:#eaf0f2; font-weight:600; white-space:nowrap; }
.g2 tr:last-child td { border-bottom:0; }
.g2 td.vacio { text-align:center; color:#5b6773; padding:1.5rem; }
.g2 .acciones { display:flex; gap:.75rem; align-items:center; }
.g2 .acciones form { margin:0; }
.g2 .etiqueta { padding:.1rem .5rem; border-radius:3px; font-size:.85rem; background:#e3e8eb; }
.g2 .etiqueta.activo { background:#dcefe2; color:var(--ok); }
.g2 .etiqueta.inactivo, .g2 .etiqueta.descontinuado { background:#f3dedc; color:var(--alerta); }
.g2 .mensaje { padding:.6rem .9rem; border-radius:4px; margin-bottom:1rem; }
.g2 .mensaje.ok { background:#dcefe2; color:var(--ok); }
.g2 .mensaje.error { background:#f8e2e0; color:var(--alerta); }
.g2 .mensaje.error ul { margin:0; padding-left:1.1rem; }
.g2 form.filtros { display:flex; gap:.75rem; align-items:end; flex-wrap:wrap; margin-bottom:1rem; }
.g2 label { display:flex; flex-direction:column; font-size:.9rem; gap:.2rem; }
.g2 input, .g2 select, .g2 textarea { font:inherit; padding:.45rem .55rem; border:1px solid var(--linea); border-radius:4px; background:#fff; color:var(--tinta); }
.g2 form.formulario { background:#fff; border:1px solid var(--linea); border-radius:4px; padding:1.25rem; max-width:560px; display:grid; gap:1rem; }
.g2 .ayuda { color:#5b6773; font-size:.85rem; }
.g2 .alertas { background:#fdf3e1; border:1px solid #e8c98b; border-radius:4px; padding:.75rem 1rem; margin-bottom:1rem; }
.g2 .alertas h2 { font-size:1.05rem; margin:0 0 .4rem; }
.g2 .alertas ul { margin:0; padding-left:1.1rem; }
.g2 .pagination { display:flex; gap:.35rem; list-style:none; padding:0; margin:1rem 0; flex-wrap:wrap; }
.g2 .pagination li > * { display:block; padding:.3rem .6rem; border:1px solid var(--linea); border-radius:4px; background:#fff; text-decoration:none; }
.g2 .pagination .active > * { background:var(--acento); color:#fff; border-color:var(--acento); }
</style>
@php($rolActual = auth()->user()?->rol?->nombre)
<nav class="menu" aria-label="Menú principal">
    <a href="{{ route('panel') }}">Panel</a>
    <a href="{{ route('reportes.stock-bajo') }}">Alertas</a>
    <a href="{{ route('historial.index') }}">Historial</a>
    <a href="{{ route('reportes.existencias') }}">Existencias</a>
    @if($rolActual === 'Administrador')
        <a href="{{ route('categorias.index') }}">Categorías</a>
        <a href="{{ route('proveedores.index') }}">Proveedores</a>
        <a href="{{ route('reportes.index') }}">Reportes</a>
    @endif
</nav>
@if(session('ok'))
    <div class="mensaje ok" role="status">{{ session('ok') }}</div>
@endif
@if($errors->any())
    <div class="mensaje error" role="alert">
        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
