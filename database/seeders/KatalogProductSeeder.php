<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantOption;
use App\Models\VariantOptionValue;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class KatalogProductSeeder extends Seeder
{
    private array $variantProducts = [];
    private array $variantOptions = [];
    private array $variantValues = [];

    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing data
        ProductVariant::query()->forceDelete();
        DB::table('product_variant_options')->truncate();
        Product::query()->forceDelete();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Load variant options
        $this->loadVariantOptions();

        // Parse CSV and group products
        $csvPath = database_path('Data/katalog 2024.xlsx - Daftar Harga.csv');
        $this->parseAndGroupProducts($csvPath);

        // Create products
        $this->createProducts();

        $this->command->info('Katalog products seeded successfully!');
    }

    private function loadVariantOptions(): void
    {
        $this->variantOptions = VariantOption::all()->keyBy('slug')->toArray();
        
        foreach (VariantOption::with('values')->get() as $option) {
            foreach ($option->values as $value) {
                $this->variantValues[$option->slug][$value->slug] = $value->id;
                $this->variantValues[$option->slug][strtolower($value->value)] = $value->id;
            }
        }
    }

    private function parseAndGroupProducts(string $csvPath): void
    {
        if (!file_exists($csvPath)) {
            $this->command->error("CSV file not found: {$csvPath}");
            return;
        }

        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle); // Skip header

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) continue;
            
            $name = trim($row[1] ?? '');
            $size = trim($row[2] ?? '');
            $category = trim($row[3] ?? '');
            $priceStr = trim($row[4] ?? '');
            $description = trim($row[7] ?? '');

            if (empty($name)) continue;

            // Parse price
            $price = $this->parsePrice($priceStr);
            if ($price <= 0) continue; // Skip items without price

            // Detect if this is a variant product
            $variantInfo = $this->detectVariant($name, $size);
            
            if ($variantInfo['is_variant']) {
                $baseName = $variantInfo['base_name'];
                $variantType = $variantInfo['type'];
                $variantValue = $variantInfo['value'];

                if (!isset($this->variantProducts[$baseName])) {
                    $this->variantProducts[$baseName] = [
                        'name' => $baseName,
                        'category' => $category,
                        'description' => $description,
                        'variants' => [],
                        'variant_types' => [],
                    ];
                }

                $this->variantProducts[$baseName]['variants'][] = [
                    'type' => $variantType,
                    'value' => $variantValue,
                    'price' => $price,
                    'original_name' => $name,
                ];
                
                if (!in_array($variantType, $this->variantProducts[$baseName]['variant_types'])) {
                    $this->variantProducts[$baseName]['variant_types'][] = $variantType;
                }
            } else {
                // Non-variant product
                $this->variantProducts[$name] = [
                    'name' => $name,
                    'category' => $category,
                    'description' => $description,
                    'price' => $price,
                    'variants' => [],
                    'variant_types' => [],
                ];
            }
        }

        fclose($handle);
    }

    private function parsePrice(string $priceStr): float
    {
        // Remove "Rp" and formatting
        $price = preg_replace('/[^0-9]/', '', $priceStr);
        return (float) $price;
    }

    private function detectVariant(string $name, string $size): array
    {
        // Pattern 1: "Product / Uk. XX" or "Product / Uk. X"
        if (preg_match('/^(.+?)\s*\/\s*Uk\.\s*(.+)$/i', $name, $matches)) {
            return [
                'is_variant' => true,
                'base_name' => trim($matches[1]),
                'type' => 'ukuran',
                'value' => trim($matches[2]),
            ];
        }

        // Pattern 2: "Product Ukuran XX"
        if (preg_match('/^(.+?)\s+Ukuran\s+(.+)$/i', $name, $matches)) {
            return [
                'is_variant' => true,
                'base_name' => trim($matches[1]),
                'type' => 'ukuran',
                'value' => trim($matches[2]),
            ];
        }

        // Pattern 3: Size in separate column
        if (!empty($size) && preg_match('/^[0-9.]+$|^[SMLX]{1,4}L?$/i', $size)) {
            // Extract base name by removing size suffix
            $baseName = preg_replace('/\s*\/\s*Uk\.\s*.+$/i', '', $name);
            $baseName = preg_replace('/\s+Ukuran\s+.+$/i', '', $baseName);
            
            return [
                'is_variant' => true,
                'base_name' => trim($baseName),
                'type' => 'ukuran',
                'value' => $size,
            ];
        }

        // Pattern 4: Color variants (Hitam/Putih in name)
        if (preg_match('/^(.+?)\s+(Hitam|Putih|Biru|Merah|Hijau|Kuning)$/i', $name, $matches)) {
            return [
                'is_variant' => true,
                'base_name' => trim($matches[1]),
                'type' => 'warna',
                'value' => trim($matches[2]),
            ];
        }

        return ['is_variant' => false, 'base_name' => $name, 'type' => null, 'value' => null];
    }

    private function createProducts(): void
    {
        $ukuranOptionId = $this->variantOptions['ukuran']['id'] ?? null;
        $warnaOptionId = $this->variantOptions['warna']['id'] ?? null;

        foreach ($this->variantProducts as $productData) {
            $hasVariants = !empty($productData['variants']);
            
            // Calculate base price (min price from variants or direct price)
            $basePrice = $productData['price'] ?? 0;
            if ($hasVariants && empty($basePrice)) {
                $basePrice = min(array_column($productData['variants'], 'price'));
            }

            // Create product
            $product = Product::create([
                'name' => $productData['name'],
                'category' => $productData['category'] ?: null,
                'description' => $productData['description'] ?: null,
                'price' => $basePrice,
                'cost_price' => $basePrice * 0.7, // Assume 30% margin
                'stock' => $hasVariants ? 0 : 10,
                'min_stock' => 5,
                'status' => 'active',
                'has_variants' => $hasVariants,
                'is_public' => true,
                'is_featured' => false,
            ]);

            if ($hasVariants) {
                // Attach variant options to product
                $optionIds = [];
                foreach ($productData['variant_types'] as $type) {
                    if ($type === 'ukuran' && $ukuranOptionId) {
                        $optionIds[] = $ukuranOptionId;
                    } elseif ($type === 'warna' && $warnaOptionId) {
                        $optionIds[] = $warnaOptionId;
                    }
                }
                if (!empty($optionIds)) {
                    $product->variantOptions()->sync($optionIds);
                }

                // Create variants
                foreach ($productData['variants'] as $variant) {
                    $optionValues = [];
                    $valueId = $this->findOrCreateVariantValue($variant['type'], $variant['value']);
                    
                    if ($valueId) {
                        $optionValues[$variant['type']] = [
                            'option_id' => $variant['type'] === 'ukuran' ? $ukuranOptionId : $warnaOptionId,
                            'option_name' => ucfirst($variant['type']),
                            'value_id' => $valueId,
                            'value' => $variant['value'],
                        ];
                    }

                    $variantName = $product->name . ' - ' . $variant['value'];
                    $sku = $this->generateSku($product->name, $variant['value']);

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $sku,
                        'variant_name' => $variantName,
                        'price' => $variant['price'],
                        'cost_price' => $variant['price'] * 0.7,
                        'stock' => 10,
                        'min_stock' => 5,
                        'option_values' => $optionValues,
                        'is_active' => true,
                    ]);
                }

                // Sync product total stock from variants
                $totalVariantStock = ProductVariant::where('product_id', $product->id)
                    ->where('is_active', true)
                    ->sum('stock');
                $product->update(['stock' => $totalVariantStock]);
            }
        }
    }

    private function findOrCreateVariantValue(string $type, string $value): ?int
    {
        $slug = Str::slug($value);
        $lowerValue = strtolower($value);

        // Check if value exists
        if (isset($this->variantValues[$type][$slug])) {
            return $this->variantValues[$type][$slug];
        }
        if (isset($this->variantValues[$type][$lowerValue])) {
            return $this->variantValues[$type][$lowerValue];
        }

        // Create new value
        $optionId = $this->variantOptions[$type]['id'] ?? null;
        if (!$optionId) return null;

        $newValue = VariantOptionValue::create([
            'variant_option_id' => $optionId,
            'value' => $value,
            'slug' => $slug,
            'display_order' => 999,
        ]);

        $this->variantValues[$type][$slug] = $newValue->id;
        $this->variantValues[$type][$lowerValue] = $newValue->id;

        return $newValue->id;
    }

    private function generateSku(string $productName, string $variantValue): string
    {
        $base = Str::upper(Str::limit(Str::slug($productName, ''), 8, ''));
        $suffix = Str::upper(Str::limit(Str::slug($variantValue, ''), 4, ''));
        $sku = $base . '-' . $suffix;

        // Ensure uniqueness
        $counter = 1;
        $originalSku = $sku;
        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = $originalSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }
}
