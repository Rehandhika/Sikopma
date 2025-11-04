<?php

namespace App\Livewire\Cashier;

use Livewire\Component;

class SalesList extends Component
{
    public function render()
    {
        return view('livewire.cashier.sales-list')
            ->layout('layouts.app');
    }
}
