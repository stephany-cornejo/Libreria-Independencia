@if(count($alertas))
<section class="alertas" role="alert">
    <h2>Productos con existencia baja ({{ count($alertas) }})</h2>
    <ul>
        @foreach($alertas as $a)
            <li><strong>{{ $a->nombre }}</strong> ({{ $a->codigo }}): {{ $a->cantidad_disponible }} disponibles, mínimo {{ $a->existencia_minima }}</li>
        @endforeach
    </ul>
    <a href="{{ route('reportes.stock-bajo') }}">Ver detalle</a>
</section>
@else
<p class="ayuda">Todos los productos están por encima de su existencia mínima.</p>
@endif
