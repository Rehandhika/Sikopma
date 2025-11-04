<?php

namespace App\Livewire\Report;

use Livewire\Component;

class PenaltyReport extends Component
{
    public function render()
    {
        return view('livewire.report.penalty-report')
            ->layout('layouts.app');
    }
}
