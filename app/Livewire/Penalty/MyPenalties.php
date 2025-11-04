<?php

namespace App\Livewire\Penalty;

use Livewire\Component;

class MyPenalties extends Component
{
    public function render()
    {
        return view('livewire.penalty.my-penalties')
            ->layout('layouts.app');
    }
}
