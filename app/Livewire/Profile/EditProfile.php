<?php

namespace App\Livewire\Profile;

use Livewire\Component;

class EditProfile extends Component
{
    public function render()
    {
        return view('livewire.profile.edit-profile')
            ->layout('layouts.app');
    }
}
