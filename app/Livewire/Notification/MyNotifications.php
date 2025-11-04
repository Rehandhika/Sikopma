<?php

namespace App\Livewire\Notification;

use Livewire\Component;

class MyNotifications extends Component
{
    public function render()
    {
        return view('livewire.notification.my-notifications')
            ->layout('layouts.app');
    }
}
