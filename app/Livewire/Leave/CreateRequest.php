<?php

namespace App\Livewire\Leave;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use App\Models\LeaveRequest;
use App\Models\ScheduleAssignment;
use App\Services\Storage\FileStorageServiceInterface;
use App\Services\ActivityLogService;
use App\Services\LeaveService;
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
    public $affectedSchedules = [];

    protected FileStorageServiceInterface $fileStorageService;
    protected LeaveService $leaveService;

    protected $rules = [
        'leave_type' => 'required|in:sick,permission,emergency,other',
        'start_date' => 'required|date|after_or_equal:today',
        'end_date' => 'required|date|after_or_equal:start_date',
        'reason' => 'required|string|min:10|max:500',
        'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
    ];

    public function boot(FileStorageServiceInterface $fileStorageService, LeaveService $leaveService)
    {
        $this->fileStorageService = $fileStorageService;
        $this->leaveService = $leaveService;
    }

    public function mount()
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        
        // Update affected schedules when dates change
        if (in_array($propertyName, ['start_date', 'end_date'])) {
            $this->updateAffectedSchedules();
        }
    }

    /**
     * Update the list of affected schedules based on selected dates
     */
    public function updateAffectedSchedules()
    {
        $this->affectedSchedules = [];
        
        if ($this->start_date && $this->end_date) {
            try {
                $startDate = Carbon::parse($this->start_date);
                $endDate = Carbon::parse($this->end_date);
                
                // Get affected schedule assignments
                $assignments = ScheduleAssignment::where('user_id', auth()->id())
                    ->whereBetween('date', [$startDate, $endDate])
                    ->whereIn('status', ['scheduled', 'excused'])
                    ->with('schedule')
                    ->orderBy('date')
                    ->orderBy('session')
                    ->get();
                
                $this->affectedSchedules = $assignments->map(function ($assignment) {
                    return [
                        'id' => $assignment->id,
                        'date' => $assignment->date->format('d M Y'),
                        'session' => $assignment->session,
                        'session_name' => $this->getSessionName($assignment->session),
                        'time' => $this->getSessionTime($assignment->session),
                        'status' => $assignment->status,
                    ];
                })->toArray();
            } catch (\Exception $e) {
                Log::error('Error updating affected schedules', [
                    'error' => $e->getMessage(),
                    'user_id' => auth()->id(),
                ]);
            }
        }
    }

    /**
     * Get session name
     */
    private function getSessionName(int $session): string
    {
        return match($session) {
            1 => 'Sesi 1',
            2 => 'Sesi 2',
            3 => 'Sesi 3',
            default => "Sesi {$session}",
        };
    }

    /**
     * Get session time range
     */
    private function getSessionTime(int $session): string
    {
        return match($session) {
            1 => '08:00 - 12:00',
            2 => '12:00 - 16:00',
            3 => '16:00 - 20:00',
            default => '-',
        };
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

        // Log activity
        ActivityLogService::logLeaveCreated(
            auth()->user()->name,
            Carbon::parse($this->start_date)->format('d M Y'),
            Carbon::parse($this->end_date)->format('d M Y')
        );

        $this->dispatch('toast', message: 'Pengajuan cuti/izin berhasil dibuat dan menunggu persetujuan', type: 'success');
        
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
            'affectedSchedules' => $this->affectedSchedules,
        ])->layout('layouts.app');
    }
}
