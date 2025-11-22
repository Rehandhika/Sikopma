<?php

namespace App\Livewire;

use Livewire\Component;

class TestFormComponents extends Component
{
    public $country = '';
    public $role = '';
    public $description = '';
    public $notes = '';
    public $terms = false;
    public $newsletter = false;
    public $payment = 'credit_card';
    public $shipping = '';
    public $bio = '';
    public $email_notifications = false;
    public $sms_notifications = false;
    public $account_type = 'personal';

    protected $rules = [
        'country' => 'required',
        'role' => 'required',
        'description' => 'nullable|max:500',
        'notes' => 'required|min:10',
        'terms' => 'accepted',
        'payment' => 'required',
        'shipping' => 'required',
        'bio' => 'nullable|max:200',
        'account_type' => 'required',
    ];

    protected $messages = [
        'country.required' => 'Please select a country',
        'role.required' => 'Please select a role',
        'notes.required' => 'Notes are required',
        'notes.min' => 'Notes must be at least 10 characters',
        'terms.accepted' => 'You must accept the terms and conditions',
        'payment.required' => 'Please select a payment method',
        'shipping.required' => 'Please select a shipping method',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function submit()
    {
        $this->validate();

        session()->flash('success', 'Form submitted successfully!');
        
        // Reset form
        $this->reset();
    }

    public function render()
    {
        return view('livewire.test-form-components');
    }
}
