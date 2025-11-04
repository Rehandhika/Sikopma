<?php

namespace App\Livewire\Kopma;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.kopma.index')
            ->layout('layouts.app')
            ->title('Kopma');
    }
}
