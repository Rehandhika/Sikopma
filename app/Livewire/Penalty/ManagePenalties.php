<?php

namespace App\Livewire\Penalty;

use Livewire\Component;

class ManagePenalties extends Component
{
    public function render()
    {
        return view('livewire.penalty.manage-penalties')
            ->layout('layouts.app');
    }
}
