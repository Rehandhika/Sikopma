<?php

namespace App\Livewire\Purchase;

use App\Models\Purchase;
use App\Services\ActivityLogService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $statusFilter = '';

    public $search = '';

    public function approvePurchase($id)
    {
        $purchase = Purchase::find($id);

        if ($purchase && $purchase->payment_status === 'unpaid') {
            $purchase->update([
                'payment_status' => 'paid',
            ]);

            // Log activity
            ActivityLogService::logPurchaseApproved($purchase->invoice_number);

            $this->dispatch('toast', message: 'Purchase order disetujui', type: 'success');
        }
    }

    public function render()
    {
        $purchases = Purchase::query()
            ->when($this->search, fn ($q) => $q->where('invoice_number', 'like', '%'.$this->search.'%')
                ->orWhere('supplier_name', 'like', '%'.$this->search.'%'))
            ->when($this->statusFilter, fn ($q) => $q->where('payment_status', $this->statusFilter))
            ->with(['user', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total' => Purchase::count(),
            'pending' => Purchase::where('payment_status', 'unpaid')->count(),
            'approved' => Purchase::where('payment_status', 'partial')->count(),
            'received' => Purchase::where('payment_status', 'paid')->count(),
        ];

        return view('livewire.purchase.index', [
            'purchases' => $purchases,
            'stats' => $stats,
        ])->layout('layouts.app')->title('Purchase Orders');
    }
}
