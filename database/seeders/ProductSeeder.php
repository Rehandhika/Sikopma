<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        if (Product::count() > 0) {
            return;
        }

        $categories = ['Minuman', 'Makanan', 'ATK', 'Kebutuhan Harian'];

        $items = [
            ['name' => 'Air Mineral 600ml', 'price' => 4000, 'category' => 'Minuman'],
            ['name' => 'Teh Botol 350ml', 'price' => 6000, 'category' => 'Minuman'],
            ['name' => 'Kopi Kaleng', 'price' => 8000, 'category' => 'Minuman'],
            ['name' => 'Mi Instan Goreng', 'price' => 3500, 'category' => 'Makanan'],
            ['name' => 'Mi Instan Kuah', 'price' => 3500, 'category' => 'Makanan'],
            ['name' => 'Biskuit Cokelat', 'price' => 7000, 'category' => 'Makanan'],
            ['name' => 'Roti Tawar', 'price' => 15000, 'category' => 'Makanan'],
            ['name' => 'Pulpen Biru', 'price' => 4000, 'category' => 'ATK'],
            ['name' => 'Buku Tulis 38 Lbr', 'price' => 6000, 'category' => 'ATK'],
            ['name' => 'Sabun Mandi', 'price' => 8000, 'category' => 'Kebutuhan Harian'],
            ['name' => 'Sampo Sachet', 'price' => 2000, 'category' => 'Kebutuhan Harian'],
        ];

        $now = now();
        foreach ($items as $i => $data) {
            $sku = strtoupper(substr(Str::slug($data['category']), 0, 3)) . '-' . strtoupper(substr(Str::slug($data['name']), 0, 3)) . '-' . $now->format('ymd') . '-' . str_pad((string)($i+1), 3, '0', STR_PAD_LEFT);

            Product::create([
                'name' => $data['name'],
                'sku' => $sku,
                'price' => $data['price'],
                'stock' => 0, // akan diisi oleh purchase/adjustment seeder
                'min_stock' => rand(3, 10),
                'category' => $data['category'],
                'description' => null,
                'status' => 'active',
            ]);
        }
    }
}
