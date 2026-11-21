<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de movimientos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .sub { color: #6b7280; margin-bottom: 12px; }
        .resumen { margin-bottom: 12px; }
        .resumen span { display: inline-block; margin-right: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e3a8a; color: #fff; text-align: left; padding: 6px; }
        td { padding: 5px 6px; border-bottom: 1px solid #e5e7eb; }
        .der { text-align: right; }
        .entrada { color: #16a34a; font-weight: bold; }
        .salida { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Reporte de entradas y salidas – Librería Independencia</h1>
    <div class="sub">
        Generado el {{ $generado }}
        @if (!empty($filtros['desde']) || !empty($filtros['hasta']))
            · Periodo: {{ $filtros['desde'] ?? 'inicio' }} al {{ $filtros['hasta'] ?? 'hoy' }}
        @endif
        @if (!empty($filtros['tipo']))
            · Tipo: {{ ucfirst($filtros['tipo']) }}
        @endif
    </div>

    <div class="resumen">
        <span><strong>Movimientos:</strong> {{ $resumen['total_movimientos'] }}</span>
        <span><strong>Unidades que entraron:</strong> {{ $resumen['total_entradas'] }}</span>
        <span><strong>Unidades que salieron:</strong> {{ $resumen['total_salidas'] }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th><th>Código</th><th>Producto</th><th>Tipo</th>
                <th class="der">Cantidad</th><th>Motivo</th><th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($filas as $f)
                <tr>
                    <td>{{ $f['fecha'] }}</td>
                    <td>{{ $f['codigo'] }}</td>
                    <td>{{ $f['producto'] }}</td>
                    <td class="{{ $f['tipo'] }}">{{ ucfirst($f['tipo']) }}</td>
                    <td class="der">{{ $f['cantidad'] }}</td>
                    <td>{{ $f['motivo'] }}</td>
                    <td>{{ $f['usuario'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No hay movimientos para los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
