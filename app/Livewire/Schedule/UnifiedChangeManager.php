<?php

namespace App\Livewire\Schedule;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Manajemen Perubahan Jadwal')]
#[Layout('layouts.app')]
class UnifiedChangeManager extends Component
{
    #[Url(as: 'tab')]
    public string $activeType = 'change'; // 'swap' or 'change'

    public function setType(string $type): void
    {
        $this->activeType = $type;
    }

    public function render()
    {
        return view('livewire.schedule.unified-change-manager');
    }
}
