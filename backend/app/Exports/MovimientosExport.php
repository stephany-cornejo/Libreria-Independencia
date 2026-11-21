<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MovimientosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        return ['Fecha', 'Código', 'Producto', 'Tipo', 'Cantidad', 'Motivo', 'Usuario'];
    }

    public function map($fila): array
    {
        return [
            $fila['fecha'], $fila['codigo'], $fila['producto'],
            ucfirst($fila['tipo']), $fila['cantidad'], $fila['motivo'], $fila['usuario'],
        ];
    }

    public function styles(Worksheet $hoja): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
