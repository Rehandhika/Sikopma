<?php

namespace App\Livewire\Purchase;

use Livewire\Component;

class PurchaseList extends Component
{
    public function render()
    {
        return view('livewire.purchase.purchase-list')
            ->layout('layouts.app');
    }
}
