<?php

namespace App\Livewire\Swap;

use Livewire\Component;

class CreateRequest extends Component
{
    public function render()
    {
        return view('livewire.swap.create-request')
            ->layout('layouts.app');
    }
}
