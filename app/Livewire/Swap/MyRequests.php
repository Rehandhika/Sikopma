<?php

namespace App\Livewire\Swap;

use Livewire\Component;

class MyRequests extends Component
{
    public function render()
    {
        return view('livewire.swap.my-requests')
            ->layout('layouts.app');
    }
}
