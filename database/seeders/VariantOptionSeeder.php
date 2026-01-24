<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VariantOption;
use App\Models\VariantOptionValue;

class VariantOptionSeeder extends Seeder
{
    public function run(): void
    {
        // Ukuran (Size) - berdasarkan katalog Kopma
        $ukuran = VariantOption::firstOrCreate(
            ['slug' => 'ukuran'],
            ['name' => 'Ukuran', 'display_order' => 1]
        );

        $ukuranValues = [
            // Numeric sizes (Celana, Kemeja)
            '14', '14.5', '15', '15.5', '16', '17', '18', '20',
            '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38',
            // Letter sizes
            'S', 'M', 'L', 'XL', 'XXL', 'XXXL', '4XL',
        ];

        foreach ($ukuranValues as $order => $value) {
            VariantOptionValue::firstOrCreate(
                ['variant_option_id' => $ukuran->id, 'slug' => strtolower($value)],
                ['value' => $value, 'display_order' => $order]
            );
        }

        // Warna (Color)
        $warna = VariantOption::firstOrCreate(
            ['slug' => 'warna'],
            ['name' => 'Warna', 'display_order' => 2]
        );

        $warnaValues = ['Hitam', 'Putih', 'Biru', 'Merah', 'Hijau', 'Kuning', 'Abu-Abu', 'Coklat'];

        foreach ($warnaValues as $order => $value) {
            VariantOptionValue::firstOrCreate(
                ['variant_option_id' => $warna->id, 'slug' => \Illuminate\Support\Str::slug($value)],
                ['value' => $value, 'display_order' => $order]
            );
        }

        // Tipe (Type)
        $tipe = VariantOption::firstOrCreate(
            ['slug' => 'tipe'],
            ['name' => 'Tipe', 'display_order' => 3]
        );

        $tipeValues = ['A', 'B', 'Panjang', 'Pendek', 'Tebal', 'Tipis', 'Baru', 'Lama'];

        foreach ($tipeValues as $order => $value) {
            VariantOptionValue::firstOrCreate(
                ['variant_option_id' => $tipe->id, 'slug' => \Illuminate\Support\Str::slug($value)],
                ['value' => $value, 'display_order' => $order]
            );
        }
    }
}
