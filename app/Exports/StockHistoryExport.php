<?php

namespace App\Exports;

use App\Models\StockAdjustment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockHistoryExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $search = '',
        private string $type = 'all'
    ) {}

    public function query()
    {
        return StockAdjustment::query()
            ->with(['product:id,name,sku', 'user:id,name', 'variant:id,variant_name'])
            ->when($this->search, fn ($q) => $q->whereHas('product', fn ($sub) => 
                $sub->where('name', 'like', "%{$this->search}%")
            ))
            ->when($this->type !== 'all', fn ($q) => $q->where('type', $this->type))
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Produk',
            'Varian',
            'Jumlah',
            'Stok Awal',
            'Stok Akhir',
            'Keterangan',
            'Oleh',
        ];
    }

    public function map($adjustment): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $adjustment->created_at->format('d/m/Y H:i'),
            $adjustment->product->name ?? 'Produk Dihapus',
            $adjustment->variant->variant_name ?? '-',
            $adjustment->quantity,
            $adjustment->previous_stock,
            $adjustment->new_stock,
            $adjustment->reason,
            $adjustment->user->name ?? 'System',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB'],
                ],
            ],
        ];
    }
}
