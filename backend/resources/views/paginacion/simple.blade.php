{{-- Paginación propia, con etiquetas en español y tokens del sistema. --}}
@if ($paginator->hasPages())
    <nav class="paginacion" role="navigation" aria-label="Paginación del inventario">
        <span>
            @if ($paginator->firstItem())
                Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
                de {{ $paginator->total() }} libros
            @else
                {{ $paginator->total() }} libros
            @endif
        </span>

        <div class="paginacion-enlaces">
            @if (! $paginator->onFirstPage())
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>
            @else
                <span class="pagina-inactiva">Anterior</span>
            @endif

            @for ($pagina = 1; $pagina <= $paginator->lastPage(); $pagina++)
                @if ($pagina === $paginator->currentPage())
                    <span class="pagina-actual" aria-current="page">{{ $pagina }}</span>
                @elseif (
                    $pagina === 1
                    || $pagina === $paginator->lastPage()
                    || abs($pagina - $paginator->currentPage()) <= 1
                )
                    <a href="{{ $paginator->url($pagina) }}">{{ $pagina }}</a>
                @elseif (abs($pagina - $paginator->currentPage()) === 2)
                    <span class="pagina-inactiva">…</span>
                @endif
            @endfor

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a>
            @else
                <span class="pagina-inactiva">Siguiente</span>
            @endif
        </div>
    </nav>
@endif
