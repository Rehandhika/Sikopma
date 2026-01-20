<?php

namespace App\Livewire\Leave;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use App\Models\LeaveRequest;
use App\Services\Storage\FileStorageServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

#[Title('Ajukan Cuti/Izin')]
class CreateRequest extends Component
{
    use WithFileUploads;

    public $leave_type = 'permission';
    public $start_date;
    public $end_date;
    public $reason = '';
    public $attachment;

    protected FileStorageServiceInterface $fileStorageService;

    protected $rules = [
        'leave_type' => 'required|in:sick,permission,emergency,other',
        'start_date' => 'required|date|after_or_equal:today',
        'end_date' => 'required|date|after_or_equal:start_date',
        'reason' => 'required|string|min:10|max:500',
        'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
    ];

    public function boot(FileStorageServiceInterface $fileStorageService)
    {
        $this->fileStorageService = $fileStorageService;
    }

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

        // Handle file upload using FileStorageService
        $attachmentPath = null;
        if ($this->attachment) {
            $attachmentPath = $this->uploadAttachment();
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

    /**
     * Upload leave attachment using FileStorageService.
     * Uses private disk for sensitive files.
     * 
     * @return string|null Attachment path or null on failure
     */
    protected function uploadAttachment(): ?string
    {
        try {
            // Upload using FileStorageService with 'leave' type (uses private disk)
            $result = $this->fileStorageService->upload($this->attachment, 'leave', [
                'user_id' => auth()->id(),
            ]);

            Log::info('Leave attachment uploaded via FileStorageService', [
                'user_id' => auth()->id(),
                'path' => $result->path,
                'size' => $result->size,
            ]);

            return $result->path;
        } catch (\Exception $e) {
            Log::warning('FileStorageService upload failed, using fallback', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            // Fallback to legacy upload
            return $this->uploadAttachmentLegacy();
        }
    }

    /**
     * Legacy method for uploading leave attachment.
     * Used as fallback when FileStorageService fails.
     * 
     * @return string|null
     */
    protected function uploadAttachmentLegacy(): ?string
    {
        try {
            // Store in public disk as fallback (legacy behavior)
            return $this->attachment->store('leave-attachments', 'public');
        } catch (\Exception $e) {
            Log::error('Legacy leave attachment upload failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get attachment URL for display.
     * Handles both private and public disk files.
     * 
     * @param string|null $path
     * @return string|null
     */
    public function getAttachmentUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        try {
            // Try FileStorageService first (handles signed URLs for private files)
            return $this->fileStorageService->getUrl($path);
        } catch (\Exception $e) {
            // Fallback to direct URL for legacy files
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->url($path);
            }
            
            // Try local disk for private files
            if (Storage::disk('local')->exists($path)) {
                // For private files, we need to generate a temporary URL or route
                return route('leave.attachment.download', ['path' => base64_encode($path)]);
            }
            
            return null;
        }
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
