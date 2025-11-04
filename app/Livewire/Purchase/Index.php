<?php

namespace App\Livewire\Purchase;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PurchaseOrder;

class Index extends Component
{
    use WithPagination;

    public $statusFilter = '';
    public $search = '';

    public function approvePurchase($id)
    {
        $purchase = PurchaseOrder::find($id);
        
        if ($purchase && $purchase->status === 'pending') {
            $purchase->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            
            $this->dispatch('alert', type: 'success', message: 'Purchase order disetujui');
        }
    }

    public function render()
    {
        $purchases = PurchaseOrder::query()
            ->when($this->search, fn($q) => $q->where('po_number', 'like', '%' . $this->search . '%')
                ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', '%' . $this->search . '%')))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->with(['supplier', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total' => PurchaseOrder::count(),
            'pending' => PurchaseOrder::where('status', 'pending')->count(),
            'approved' => PurchaseOrder::where('status', 'approved')->count(),
            'received' => PurchaseOrder::where('status', 'received')->count(),
        ];

        return view('livewire.purchase.index', [
            'purchases' => $purchases,
            'stats' => $stats,
        ])->layout('layouts.app')->title('Purchase Orders');
    }
}
