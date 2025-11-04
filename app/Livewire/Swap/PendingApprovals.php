<?php

namespace App\Livewire\Swap;

use Livewire\Component;

class PendingApprovals extends Component
{
    public function render()
    {
        return view('livewire.swap.pending-approvals')
            ->layout('layouts.app');
    }
}
