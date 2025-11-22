<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;

class TestInputComponent extends Component
{
    #[Validate('required|min:3')]
    public $name = '';

    #[Validate('required|email')]
    public $email = '';

    #[Validate('required|min:8')]
    public $password = '';

    #[Validate('nullable|regex:/^[0-9]{10,15}$/')]
    public $phone = '';

    public function save()
    {
        $this->validate();

        session()->flash('message', 'Form saved successfully!');
        
        // Reset form after successful save
        $this->reset();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.test-input-component');
    }
}
