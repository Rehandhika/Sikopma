<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockProductsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $search = '',
        private string $category = '',
        private string $stockFilter = 'all'
    ) {}

    public function query()
    {
        return Product::query()
            ->with(['variants' => fn ($q) => $q->where('is_active', true)->orderBy('variant_name')])
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub->where('name', 'like', "%{$this->search}%")->orWhere('sku', 'like', "%{$this->search}%")
            ))
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->when($this->stockFilter === 'low', fn ($q) => $q->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0))
            ->when($this->stockFilter === 'out', fn ($q) => $q->where('stock', '<=', 0))
            ->when($this->stockFilter === 'normal', fn ($q) => $q->whereColumn('stock', '>', 'min_stock'))
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'No',
            'SKU',
            'Nama Produk',
            'Kategori',
            'Varian',
            'Stok',
            'Min Stok',
            'Harga Beli',
            'Harga Jual',
            'Nilai Aset',
        ];
    }

    public function map($product): array
    {
        $rows = [];
        static $no = 0;
        $no++;

        if ($product->has_variants) {
            foreach ($product->variants as $variant) {
                $rows[] = [
                    $no,
                    $variant->sku,
                    $product->name,
                    $product->category,
                    $variant->variant_name,
                    $variant->stock,
                    $variant->min_stock,
                    $variant->cost_price,
                    $variant->price,
                    $variant->stock * $variant->cost_price,
                ];
            }
        } else {
            $rows[] = [
                $no,
                $product->sku,
                $product->name,
                $product->category,
                '-',
                $product->stock,
                $product->min_stock,
                $product->cost_price,
                $product->price,
                $product->stock * $product->cost_price,
            ];
        }

        return $rows;
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
