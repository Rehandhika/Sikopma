<?php

namespace App\Livewire\Product;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Product;
use App\Models\VariantOption;
use App\Services\ProductImageService;
use App\Services\ProductVariantService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Str;

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
    public $variants = []; // Array of variant rows with free text values

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,webp,gif',
            'has_variants' => 'boolean',
        ];

        if (!$this->has_variants) {
            $rules['stock'] = 'required|integer|min:0';
            $rules['min_stock'] = 'required|integer|min:0';
        }

        return $rules;
    }

    protected $messages = [
        'image.max' => 'Ukuran gambar maksimal 5MB.',
        'image.mimes' => 'Format gambar harus JPG, PNG, WebP, atau GIF.',
        'image.image' => 'File harus berupa gambar.',
        'cost_price.required' => 'Harga beli wajib diisi.',
        'cost_price.min' => 'Harga beli tidak boleh negatif.',
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
        if (!$value) {
            $this->variants = [];
            $this->selectedVariantOptions = [];
        }
    }

    /**
     * Add new variant row with empty values
     */
    public function addVariant()
    {
        if (empty($this->selectedVariantOptions)) {
            $this->dispatch('alert', type: 'error', message: 'Pilih tipe varian terlebih dahulu.');
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
            return ['count' => 0, 'total_stock' => 0, 'price_range' => null];
        }

        $prices = collect($this->variants)->pluck('price')->filter()->map(fn($p) => (float) $p);
        $totalStock = collect($this->variants)->sum('stock');

        return [
            'count' => count($this->variants),
            'total_stock' => $totalStock,
            'price_range' => $prices->isNotEmpty() ? ['min' => $prices->min(), 'max' => $prices->max()] : null,
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
                $this->dispatch('alert', type: 'error', message: 'Produk dengan varian harus memiliki minimal 1 varian.');
                return;
            }

            if (empty($this->selectedVariantOptions)) {
                $this->dispatch('alert', type: 'error', message: 'Pilih minimal satu tipe varian.');
                return;
            }

            // Check for empty and duplicate combinations
            $combinations = [];
            foreach ($this->variants as $index => $variant) {
                $values = collect($variant['option_texts'])
                    ->filter(fn($v) => trim($v) !== '')
                    ->map(fn($v) => strtolower(trim($v)))
                    ->sortKeys()
                    ->implode('|');
                
                if (empty($values)) {
                    $this->dispatch('alert', type: 'error', message: 'Varian #' . ($index + 1) . ' belum diisi.');
                    return;
                }
                if (isset($combinations[$values])) {
                    $this->dispatch('alert', type: 'error', message: 'Ada kombinasi varian yang duplikat.');
                    return;
                }
                $combinations[$values] = true;
            }
        }

        if ($this->has_variants && $this->status === 'active' && empty($this->variants)) {
            $this->dispatch('alert', type: 'error', message: 'Tidak dapat mengaktifkan produk tanpa varian.');
            return;
        }

        $imagePath = null;
        if ($this->image) {
            try {
                $imageService = app(ProductImageService::class);
                $imagePath = $imageService->upload($this->image);
            } catch (\Exception $e) {
                $this->dispatch('alert', type: 'error', message: 'Gagal upload gambar: ' . $e->getMessage());
                return;
            }
        }

        $product = Product::create([
            'name' => $this->name,
            'sku' => $this->sku ?: null,
            'price' => $this->price,
            'cost_price' => $this->cost_price,
            'stock' => $this->has_variants ? 0 : $this->stock,
            'min_stock' => $this->min_stock,
            'category' => $this->category,
            'description' => $this->description,
            'status' => $this->status,
            'has_variants' => $this->has_variants,
            'image' => $imagePath,
        ]);

        if ($this->has_variants && !empty($this->variants)) {
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

        session()->flash('message', 'Produk berhasil ditambahkan.');
        return redirect()->route('admin.products.index');
    }

    public function render()
    {
        return view('livewire.product.create-product', [
            'variantOptions' => VariantOption::ordered()->get(),
        ]);
    }
}
