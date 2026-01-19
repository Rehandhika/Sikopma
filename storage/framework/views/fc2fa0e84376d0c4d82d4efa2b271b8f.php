<div class="p-4 space-y-4" 
    x-data="posEntry()"
    x-init="initProducts(<?php echo \Illuminate\Support\Js::from($this->products)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($this->selectedDate)->toHtml() ?>)"
    @date-changed.window="handleDateChange($event.detail.date)"
    @transactions-saved.window="onTransactionsSaved()"
    wire:ignore.self>
    
    
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-semibold text-gray-900">Entry Transaksi</h1>
            <input type="date" wire:model.live="selectedDate" max="<?php echo e(now()->format('Y-m-d')); ?>"
                class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <span x-show="isDraft" x-cloak class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded">Draft</span>
        </div>
        <div class="text-right">
            <span class="text-sm text-gray-500">Total Hari Ini</span>
            <p class="text-xl font-bold text-indigo-600">Rp <?php echo e(number_format($this->dailySummary['total'], 0, ',', '.')); ?></p>
        </div>
    </div>

    
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
            <span class="font-medium text-gray-700">Entry Baru</span>
            <div class="flex items-center gap-3">
                <button x-show="isDraft" @click="clearDraft(); resetRows()" class="text-sm text-gray-500 hover:text-gray-700">
                    Reset
                </button>
                <button @click="submitAll()" :disabled="!hasValidRows() || isSubmitting"
                    class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="submitAll">Simpan Semua</span>
                    <span wire:loading wire:target="submitAll">Menyimpan...</span>
                </button>
            </div>
        </div>
        
        
        <div class="overflow-visible">
            <table class="w-full">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Produk</th>
                        <th class="px-4 py-3 text-center font-medium w-24">Qty</th>
                        <th class="px-4 py-3 text-right font-medium w-32">Total</th>
                        <th class="px-4 py-3 text-center font-medium w-32">Bayar</th>
                        <th class="px-4 py-3 w-12"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="(row, idx) in rows" :key="row.id">
                        <tr :class="row.product_id ? 'bg-green-50/50' : ''" class="relative">
                            <td class="px-4 py-3 relative" style="overflow: visible;">
                                <div class="relative">
                                    <input type="text" x-model="row.search" 
                                        @focus="openDropdown(idx)"
                                        @input="openDropdown(idx); row.product_id = null; row.price = 0"
                                        @keydown.escape="closeDropdown(idx)"
                                        @keydown.enter.prevent="selectFirstProduct(idx)"
                                        placeholder="Ketik nama produk..."
                                        class="w-full px-3 py-2 text-sm border rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="row.product_id ? 'border-green-400 bg-green-50' : 'border-gray-300'">
                                    
                                    <div x-show="row.showDropdown && row.search.length > 0" x-transition
                                        @click.away="closeDropdown(idx)"
                                        class="absolute left-0 right-0 mt-1 bg-white border rounded-md shadow-xl max-h-48 overflow-auto"
                                        style="z-index: 9999;">
                                        <template x-for="p in filterProducts(row.search)" :key="p.id">
                                            <button type="button" @click="selectProduct(idx, p)"
                                                class="w-full px-3 py-2 text-left text-sm hover:bg-indigo-50 flex justify-between items-center border-b border-gray-100 last:border-0">
                                                <span x-text="p.name"></span>
                                                <span class="text-indigo-600 font-medium" x-text="formatRupiah(p.price)"></span>
                                            </button>
                                        </template>
                                        <div x-show="filterProducts(row.search).length === 0" class="px-3 py-3 text-sm text-gray-400 text-center">
                                            Tidak ditemukan
                                        </div>
                                    </div>
                                </div>
                            </td>
                        <td class="px-4 py-3">
                            <input type="number" x-model.number="row.qty" min="1" :disabled="!row.product_id"
                                @change="validateQty(row); saveDraft()"
                                @keydown.enter.prevent="addRowAndFocus()"
                                class="w-full px-3 py-2 text-sm text-center border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-sm font-semibold" :class="row.product_id ? 'text-gray-900' : 'text-gray-300'" 
                                x-text="formatRupiah(row.price * row.qty)"></span>
                        </td>
                        <td class="px-4 py-3">
                            <select x-model="row.payment_method" @change="saveDraft()"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button @click="removeRow(idx)" x-show="rows.length > 1" 
                                class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
            <tfoot class="bg-gray-50 border-t">
                <tr>
                    <td class="px-4 py-3">
                        <button @click="addRowAndFocus()" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            + Tambah
                        </button>
                    </td>
                    <td class="px-4 py-3 text-center text-sm text-gray-600" x-text="getTotalQty() + ' item'"></td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-base font-bold text-indigo-600" x-text="formatRupiah(getGrandTotal())"></span>
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-4 py-3 bg-gray-50 border-b">
            <span class="font-medium text-gray-700">Tersimpan (<?php echo e($this->dailySummary['count']); ?>)</span>
        </div>
        
        <!--[if BLOCK]><![endif]--><?php if(count($this->transactions) > 0): ?>
        <div class="max-h-80 overflow-y-auto">
            <table class="w-full">
                <tbody class="divide-y divide-gray-100">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $tx['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemIdx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm"><?php echo e($item['product_name']); ?></td>
                            <td class="px-4 py-3 text-sm text-center text-gray-500 w-24"><?php echo e($item['quantity']); ?>x</td>
                            <td class="px-4 py-3 text-sm text-right font-medium w-32">Rp <?php echo e(number_format($item['subtotal'], 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-center w-32">
                                <span class="text-xs px-2 py-1 rounded-full 
                                    <?php echo e($tx['payment_method'] === 'cash' ? 'bg-green-100 text-green-700' : ''); ?>

                                    <?php echo e($tx['payment_method'] === 'transfer' ? 'bg-blue-100 text-blue-700' : ''); ?>

                                    <?php echo e($tx['payment_method'] === 'qris' ? 'bg-purple-100 text-purple-700' : ''); ?>">
                                    <?php echo e(ucfirst($tx['payment_method'])); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-center w-12">
                                <!--[if BLOCK]><![endif]--><?php if($itemIdx === 0): ?>
                                <button wire:click="deleteTransaction(<?php echo e($tx['id']); ?>)" wire:confirm="Hapus transaksi ini?"
                                    class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="px-4 py-12 text-center text-gray-400">
            Belum ada transaksi untuk tanggal ini
        </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    
    <div class="bg-white rounded-lg shadow-sm border p-4">
        <div class="flex items-center justify-between">
            <div class="flex gap-6 text-sm">
                <div>
                    <span class="text-gray-500">Cash</span>
                    <p class="font-semibold text-green-600">Rp <?php echo e(number_format($this->dailySummary['cash'], 0, ',', '.')); ?></p>
                </div>
                <div>
                    <span class="text-gray-500">Transfer</span>
                    <p class="font-semibold text-blue-600">Rp <?php echo e(number_format($this->dailySummary['transfer'], 0, ',', '.')); ?></p>
                </div>
                <div>
                    <span class="text-gray-500">QRIS</span>
                    <p class="font-semibold text-purple-600">Rp <?php echo e(number_format($this->dailySummary['qris'], 0, ',', '.')); ?></p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-gray-500 text-sm">Total</span>
                <p class="text-2xl font-bold text-gray-900">Rp <?php echo e(number_format($this->dailySummary['total'], 0, ',', '.')); ?></p>
            </div>
        </div>
    </div>
</div>

    <?php
        $__scriptKey = '1967123547-0';
        ob_start();
    ?>
<script>
Alpine.data('posEntry', () => ({
    products: [],
    currentDate: '',
    rows: [],
    isDraft: false,
    counter: 0,
    isSubmitting: false,
    
    initProducts(products, date) {
        this.products = products || [];
        this.currentDate = date;
        this.loadDraft();
        if (!this.rows.length) this.addRow();
        
        document.addEventListener('keydown', e => {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                this.submitAll();
            }
        });
    },
    
    getDraftKey() {
        return `pos_draft_${this.currentDate}`;
    },
    
    loadDraft() {
        try {
            const saved = localStorage.getItem(this.getDraftKey());
            if (saved) {
                const data = JSON.parse(saved);
                this.rows = data.rows || [];
                this.counter = data.counter || 0;
                this.isDraft = this.rows.some(r => r.product_id);
            }
        } catch (e) {
            console.error('Load draft error:', e);
        }
    },
    
    saveDraft() {
        if (this.rows.some(r => r.product_id)) {
            localStorage.setItem(this.getDraftKey(), JSON.stringify({
                rows: this.rows,
                counter: this.counter
            }));
            this.isDraft = true;
        }
    },
    
    clearDraft() {
        localStorage.removeItem(this.getDraftKey());
        this.isDraft = false;
    },
    
    handleDateChange(newDate) {
        this.currentDate = newDate;
        this.rows = [];
        this.counter = 0;
        this.isDraft = false;
        this.loadDraft();
        if (!this.rows.length) this.addRow();
    },
    
    onTransactionsSaved() {
        this.clearDraft();
        this.rows = [];
        this.counter = 0;
        this.addRow();
    },
    
    addRow() {
        this.rows.push({
            id: ++this.counter,
            product_id: null,
            search: '',
            name: '',
            qty: 1,
            price: 0,
            payment_method: 'cash',
            showDropdown: false
        });
    },
    
    addRowAndFocus() {
        this.addRow();
        this.$nextTick(() => {
            const inputs = document.querySelectorAll('input[placeholder="Ketik nama produk..."]');
            if (inputs.length > 0) {
                inputs[inputs.length - 1].focus();
            }
        });
    },
    
    removeRow(idx) {
        if (this.rows.length > 1) {
            this.rows.splice(idx, 1);
            this.saveDraft();
        }
    },
    
    resetRows() {
        this.rows = [];
        this.counter = 0;
        this.addRow();
    },
    
    openDropdown(idx) {
        this.rows[idx].showDropdown = true;
    },
    
    closeDropdown(idx) {
        this.rows[idx].showDropdown = false;
    },
    
    filterProducts(search) {
        if (!search || !this.products.length) return [];
        const term = search.toLowerCase();
        return this.products
            .filter(p => p.name.toLowerCase().includes(term) || (p.sku && p.sku.toLowerCase().includes(term)))
            .slice(0, 8);
    },
    
    selectProduct(idx, product) {
        this.rows[idx].product_id = product.id;
        this.rows[idx].name = product.name;
        this.rows[idx].search = product.name;
        this.rows[idx].price = parseFloat(product.price);
        this.rows[idx].showDropdown = false;
        this.saveDraft();
    },
    
    selectFirstProduct(idx) {
        const filtered = this.filterProducts(this.rows[idx].search);
        if (filtered.length > 0) {
            this.selectProduct(idx, filtered[0]);
        }
    },
    
    validateQty(row) {
        if (!row.product_id) return;
        const product = this.products.find(p => p.id === row.product_id);
        if (product) {
            row.qty = Math.max(1, Math.min(row.qty || 1, product.stock));
        }
    },
    
    getMaxStock(productId) {
        if (!productId) return 999;
        const product = this.products.find(p => p.id === productId);
        return product ? product.stock : 999;
    },
    
    hasValidRows() {
        return this.rows.some(r => r.product_id && r.qty > 0);
    },
    
    getTotalQty() {
        return this.rows.reduce((sum, r) => sum + (r.product_id ? r.qty : 0), 0);
    },
    
    getGrandTotal() {
        return this.rows.reduce((sum, r) => sum + (r.product_id ? r.price * r.qty : 0), 0);
    },
    
    formatRupiah(amount) {
        return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
    },
    
    async submitAll() {
        if (this.isSubmitting) return;
        
        const validRows = this.rows
            .filter(r => r.product_id && r.qty > 0)
            .map(r => ({
                product_id: r.product_id,
                qty: r.qty,
                payment_method: r.payment_method
            }));
        
        if (validRows.length === 0) return;
        
        this.isSubmitting = true;
        try {
            await this.$wire.submitAll(validRows);
        } finally {
            this.isSubmitting = false;
        }
    }
}));
</script>
    <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
<?php /**PATH C:\laragon\www\Kopma\resources\views/livewire/cashier/pos-entry.blade.php ENDPATH**/ ?>