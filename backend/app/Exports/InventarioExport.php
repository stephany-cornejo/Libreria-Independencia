<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventarioExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(private Collection $filas)
    {
    }

    public function collection(): Collection
    {
        return $this->filas;
    }

    public function headings(): array
    {
        return ['Código', 'Producto', 'Categoría', 'Proveedor', 'Stock', 'Stock mínimo', 'Precio compra', 'Valor inventario', 'Estado'];
    }

    public function map($fila): array
    {
        return [
            $fila['codigo'], $fila['nombre'], $fila['categoria'], $fila['proveedor'],
            $fila['stock'], $fila['stock_minimo'], $fila['precio_compra'],
            $fila['valor_inventario'], ucfirst($fila['estado']),
        ];
    }

    public function styles(Worksheet $hoja): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
