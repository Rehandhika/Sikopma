<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\VariantOption;
use App\Services\ActivityLogService;
use App\Services\ProductImageService;
use App\Services\ProductVariantService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Tambah Produk')]
class CreateProduct extends Component
{
    use WithFileUploads;

    public $name = '';

    public $sku = '';

    public $price = '';

    public $cost_price = '';

    public $stock = 0;

    public $min_stock = 5;

    public $category = '';

    public $description = '';

    public $status = 'active';

    public $image;

    public $imagePreview = null;

    // Variant properties - simplified with free text input
    public $has_variants = false;

    public $selectedVariantOptions = []; // IDs of variant option types
    
    public $newVariantOptionName = ''; // For creating new variant option

    public $variants = []; // Array of variant rows with free text values

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:products,sku',
            // 'price' => 'required|numeric|min:0', // Removed from main rules, handled conditionally
            // 'cost_price' => 'required|numeric|min:0', // Removed from main rules, handled conditionally
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,webp,gif',
            'has_variants' => 'boolean',
        ];

        if (! $this->has_variants) {
            $rules['price'] = 'required|numeric|min:0';
            $rules['cost_price'] = 'required|numeric|min:0';
            $rules['stock'] = 'required|integer|min:0';
            $rules['min_stock'] = 'required|integer|min:0';
        } else {
            // Variant validation
            $rules['variants'] = 'required|array|min:1';
            $rules['variants.*.price'] = 'required|numeric|min:0';
            $rules['variants.*.cost_price'] = 'required|numeric|min:0';
            $rules['variants.*.stock'] = 'required|integer|min:0';
            $rules['variants.*.min_stock'] = 'required|integer|min:0';
        }

        return $rules;
    }

    protected $messages = [
        'image.max' => 'Ukuran gambar maksimal 5MB.',
        'image.mimes' => 'Format gambar harus JPG, PNG, WebP, atau GIF.',
        'image.image' => 'File harus berupa gambar.',
        'cost_price.required' => 'Harga beli wajib diisi.',
        'cost_price.min' => 'Harga beli tidak boleh negatif.',
        'variants.*.price.required' => 'Harga jual varian wajib diisi.',
        'variants.*.price.min' => 'Harga jual varian tidak boleh negatif.',
        'variants.*.cost_price.required' => 'Harga beli varian wajib diisi.',
        'variants.*.cost_price.min' => 'Harga beli varian tidak boleh negatif.',
        'variants.*.stock.min' => 'Stok varian tidak boleh negatif.',
    ];

    public function updatedImage()
    {
        $this->validateOnly('image');
        if ($this->image) {
            $this->imagePreview = $this->image->temporaryUrl();
        }
    }

    public function removeImage()
    {
        $this->image = null;
        $this->imagePreview = null;
    }

    public function updatedHasVariants($value)
    {
        if (! $value) {
            $this->variants = [];
            $this->selectedVariantOptions = [];
            $this->newVariantOptionName = '';
        }
    }

    /**
     * Create a new custom variant option type
     */
    public function createVariantOption()
    {
        $this->validate([
            'newVariantOptionName' => 'required|string|min:2|max:50|unique:variant_options,name'
        ], [
            'newVariantOptionName.required' => 'Nama tipe varian tidak boleh kosong.',
            'newVariantOptionName.unique' => 'Tipe varian ini sudah ada.',
        ]);

        $option = VariantOption::create([
            'name' => trim($this->newVariantOptionName),
            'display_order' => VariantOption::max('display_order') + 1
        ]);

        // Add to selected options automatically
        $this->selectedVariantOptions[] = $option->id;
        
        // Reset input
        $this->newVariantOptionName = '';
        
        $this->dispatch('toast', message: 'Tipe varian berhasil ditambahkan.', type: 'success');
    }

    /**
     * Add new variant row with empty values
     */
    public function addVariant()
    {
        if (empty($this->selectedVariantOptions)) {
            $this->dispatch('toast', message: 'Pilih tipe varian terlebih dahulu.', type: 'error');

            return;
        }

        $this->variants[] = [
            'option_texts' => [], // Will be filled with free text
            'price' => $this->price ?: 0,
            'cost_price' => $this->cost_price ?: 0,
            'stock' => 0,
            'min_stock' => 5,
        ];
    }

    /**
     * Remove variant row
     */
    public function removeVariant($index)
    {
        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);
    }

    /**
     * Get variant summary
     */
    public function getVariantSummary(): array
    {
        if (empty($this->variants)) {
            return ['count' => 0, 'total_stock' => 0, 'price_range' => null, 'cost_range' => null];
        }

        $prices = collect($this->variants)->pluck('price')->filter()->map(fn ($p) => (float) $p);
        $costs = collect($this->variants)->pluck('cost_price')->filter()->map(fn ($p) => (float) $p);
        $totalStock = collect($this->variants)->sum('stock');

        return [
            'count' => count($this->variants),
            'total_stock' => $totalStock,
            'price_range' => $prices->isNotEmpty() ? ['min' => $prices->min(), 'max' => $prices->max()] : null,
            'cost_range' => $costs->isNotEmpty() ? ['min' => $costs->min(), 'max' => $costs->max()] : null,
        ];
    }

    /**
     * Build option_values structure for saving from free text inputs
     */
    protected function buildOptionValues(array $optionTexts, $variantOptions): array
    {
        $optionValues = [];
        foreach ($variantOptions as $option) {
            $textValue = trim($optionTexts[$option->id] ?? '');
            if ($textValue !== '') {
                $optionValues[$option->slug] = [
                    'option_id' => $option->id,
                    'option_name' => $option->name,
                    'value_id' => null, // No predefined value ID - free text
                    'value' => $textValue,
                ];
            }
        }

        return $optionValues;
    }

    public function save()
    {
        $this->validate();

        // Validate variants
        if ($this->has_variants) {
            if (empty($this->variants)) {
                $this->dispatch('toast', message: 'Produk dengan varian harus memiliki minimal 1 varian.', type: 'error');

                return;
            }

            if (empty($this->selectedVariantOptions)) {
                $this->dispatch('toast', message: 'Pilih minimal satu tipe varian.', type: 'error');

                return;
            }

            // Check for empty and duplicate combinations
            $combinations = [];
            foreach ($this->variants as $index => $variant) {
                $values = collect($variant['option_texts'])
                    ->filter(fn ($v) => trim($v) !== '')
                    ->map(fn ($v) => strtolower(trim($v)))
                    ->sortKeys()
                    ->implode('|');

                if (empty($values)) {
                    $this->dispatch('toast', message: 'Varian #'.($index + 1).' belum diisi.', type: 'error');

                    return;
                }
                if (isset($combinations[$values])) {
                    $this->dispatch('toast', message: 'Ada kombinasi varian yang duplikat.', type: 'error');

                    return;
                }
                $combinations[$values] = true;
            }
        }

        if ($this->has_variants && $this->status === 'active' && empty($this->variants)) {
            $this->dispatch('toast', message: 'Tidak dapat mengaktifkan produk tanpa varian.', type: 'error');

            return;
        }

        $imagePath = null;
        if ($this->image) {
            try {
                $imageService = app(ProductImageService::class);
                $imagePath = $imageService->upload($this->image);
            } catch (\Exception $e) {
                $this->dispatch('toast', message: 'Gagal upload gambar: '.$e->getMessage(), type: 'error');

                return;
            }
        }

        // Sync parent price to minimum variant price if has variants
        if ($this->has_variants && !empty($this->variants)) {
            $minPrice = collect($this->variants)->min('price');
            $this->price = $minPrice;
            
            // Also sync cost_price from minimum cost variant (or average, but min is safer for "starting from")
            $minCostPrice = collect($this->variants)->min('cost_price');
            $this->cost_price = $minCostPrice;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($imagePath) {
            $product = Product::create([
                'name' => $this->name,
                'sku' => $this->sku ?: null,
                'price' => $this->price ?? 0, // Ensure not null
                'cost_price' => $this->cost_price ?? 0, // Ensure not null
                'stock' => $this->has_variants ? 0 : $this->stock,
                'min_stock' => $this->has_variants ? 0 : $this->min_stock, // Handle min_stock for parent
                'category' => $this->category,
                'description' => $this->description,
                'status' => $this->status,
                'has_variants' => $this->has_variants,
                'image' => $imagePath,
            ]);

            if ($this->has_variants && ! empty($this->variants)) {
                $variantService = app(ProductVariantService::class);
                $variantOptions = VariantOption::findMany($this->selectedVariantOptions);

                foreach ($this->variants as $variantData) {
                    $optionValues = $this->buildOptionValues($variantData['option_texts'], $variantOptions);
                    $variantService->createVariant($product, [
                        'option_values' => $optionValues,
                        'price' => $variantData['price'],
                        'cost_price' => $variantData['cost_price'],
                        'stock' => $variantData['stock'],
                        'min_stock' => $variantData['min_stock'],
                    ]);
                }

                $product->variantOptions()->sync($this->selectedVariantOptions);
            }

            // Log activity
            ActivityLogService::logProductCreated($this->name);
        });

        $this->dispatch('toast', message: 'Produk berhasil ditambahkan.', type: 'success');

        return redirect()->route('admin.products.index');
    }

    public function render()
    {
        return view('livewire.product.create-product', [
            'variantOptions' => VariantOption::ordered()->get(),
        ]);
    }
}
