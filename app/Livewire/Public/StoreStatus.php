<?php

namespace App\Livewire\Public;

use App\Services\StoreStatusService;
use Livewire\Component;

class StoreStatus extends Component
{
    public bool $isOpen = false;

    public string $reason = '';

    public array $attendees = [];

    public ?string $nextOpenTime = null;

    /**
     * Mount the component and load initial status
     */
    public function mount(StoreStatusService $storeStatusService): void
    {
        $this->refresh($storeStatusService);
    }

    /**
     * Refresh status data from service
     */
    public function refresh(StoreStatusService $storeStatusService): void
    {
        $status = $storeStatusService->getStatus();

        $this->isOpen = $status['is_open'];
        $this->reason = $status['reason'];
        $this->attendees = $status['attendees'];
        $this->nextOpenTime = $status['next_open_time'];
    }

    public function render()
    {
        return view('livewire.public.store-status');
    }
}
