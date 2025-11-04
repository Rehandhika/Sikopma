<?php

namespace App\Livewire\Leave;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use App\Models\LeaveRequest;
use Carbon\Carbon;

#[Title('Ajukan Cuti/Izin')]
class CreateRequest extends Component
{
    use WithFileUploads;

    public $leave_type = 'permission';
    public $start_date;
    public $end_date;
    public $reason = '';
    public $attachment;

    protected $rules = [
        'leave_type' => 'required|in:sick,permission,emergency,other',
        'start_date' => 'required|date|after_or_equal:today',
        'end_date' => 'required|date|after_or_equal:start_date',
        'reason' => 'required|string|min:10|max:500',
        'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
    ];

    public function mount()
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function submit()
    {
        $this->validate();

        // Calculate total days
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Handle file upload
        $attachmentPath = null;
        if ($this->attachment) {
            $attachmentPath = $this->attachment->store('leave-attachments', 'public');
        }

        // Create leave request
        LeaveRequest::create([
            'user_id' => auth()->id(),
            'leave_type' => $this->leave_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'total_days' => $totalDays,
            'reason' => $this->reason,
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        session()->flash('success', 'Pengajuan cuti/izin berhasil dibuat dan menunggu persetujuan');
        
        return $this->redirect(route('leave.my-requests'), navigate: true);
    }

    public function getTotalDaysProperty()
    {
        if ($this->start_date && $this->end_date) {
            try {
                $start = Carbon::parse($this->start_date);
                $end = Carbon::parse($this->end_date);
                return $start->diffInDays($end) + 1;
            } catch (\Exception $e) {
                return 0;
            }
        }
        return 0;
    }

    public function render()
    {
        return view('livewire.leave.create-request', [
            'totalDays' => $this->totalDays,
        ])->layout('layouts.app');
    }
}
