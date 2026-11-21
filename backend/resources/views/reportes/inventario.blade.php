<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de inventario</title>
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
        .agotado { color: #dc2626; font-weight: bold; }
        .bajo { color: #d97706; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Reporte de inventario – Librería Independencia</h1>
    <div class="sub">Generado el {{ $generado }}</div>

    <div class="resumen">
        <span><strong>Productos:</strong> {{ $resumen['total_productos'] }}</span>
        <span><strong>Unidades:</strong> {{ $resumen['total_unidades'] }}</span>
        <span><strong>Valor total:</strong> ${{ number_format($resumen['valor_total'], 2) }}</span>
        <span><strong>Agotados:</strong> {{ $resumen['agotados'] }}</span>
        <span><strong>Stock bajo:</strong> {{ $resumen['bajos'] }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th><th>Producto</th><th>Categoría</th><th>Proveedor</th>
                <th class="der">Stock</th><th class="der">Mínimo</th>
                <th class="der">P. compra</th><th class="der">Valor</th><th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($filas as $f)
                <tr>
                    <td>{{ $f['codigo'] }}</td>
                    <td>{{ $f['nombre'] }}</td>
                    <td>{{ $f['categoria'] }}</td>
                    <td>{{ $f['proveedor'] }}</td>
                    <td class="der">{{ $f['stock'] }}</td>
                    <td class="der">{{ $f['stock_minimo'] }}</td>
                    <td class="der">${{ number_format($f['precio_compra'], 2) }}</td>
                    <td class="der">${{ number_format($f['valor_inventario'], 2) }}</td>
                    <td class="{{ $f['estado'] }}">{{ ucfirst($f['estado']) }}</td>
                </tr>
            @empty
                <tr><td colspan="9">No hay productos para los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
