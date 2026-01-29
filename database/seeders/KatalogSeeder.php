<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class KatalogSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = database_path('Data/Katalog.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error("❌ File Katalog.csv tidak ditemukan");
            return;
        }

        $this->command->info("📦 Import Katalog.csv...");

        $products = $this->readKatalogCsv($csvFile);
        
        if (empty($products)) {
            $this->command->warn("⚠️  Tidak ada data produk");
            return;
        }

        $this->insertProducts($products);

        $count = count($products);
        $this->command->info("✅ {$count} produk berhasil di-import");
    }


    private function readKatalogCsv(string $filePath): array
    {
        $products = [];
        
        if (($handle = fopen($filePath, 'r')) !== false) {
            $rowIndex = 0;
            
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if ($rowIndex === 0) {
                    $rowIndex++;
                    continue;
                }
                
                if (empty(array_filter($row)) || empty($row[1])) {
                    continue;
                }
                
                $products[] = [
                    'nama_barang' => $row[1],
                    'ukuran' => $row[2] ?? null,
                    'kategori' => $row[3] ?? 'Lain-lain',
                    'harga' => $this->parsePrice($row[4] ?? '0'),
                    'satuan' => $row[5] ?? 'buah',
                    'stok' => $this->parseStock($row[6] ?? '0'),
                    'keterangan' => $row[7] ?? null,
                ];
                
                $rowIndex++;
            }
            
            fclose($handle);
        }
        
        return $products;
    }

    private function parsePrice(string $price): int
    {
        $cleaned = preg_replace('/[Rp\s\.]/', '', $price);
        $cleaned = str_replace(',', '', $cleaned);
        return (int)$cleaned;
    }

    private function parseStock(string $stock): int
    {
        return empty($stock) ? 0 : (int)$stock;
    }

    private function insertProducts(array $products): void
    {
        foreach ($products as $productData) {
            $name = $productData['nama_barang'];
            if (!empty($productData['ukuran'])) {
                $name .= " / Uk. " . $productData['ukuran'];
            }
            
            Product::updateOrCreate(
                ['name' => $name],
                [
                    'category' => $productData['kategori'],
                    'price' => $productData['harga'],
                    'stock' => $productData['stok'],
                    'description' => $productData['keterangan'],
                    'status' => 'active',
                ]
            );
        }
    }
}
