<?php

namespace App\Livewire\Leave;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\{Title, Layout, Url, Lazy};
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Auth, Cache};

#[Title('Izin & Cuti')]
#[Layout('layouts.app')]
#[Lazy]
class LeaveManager extends Component
{
    use WithPagination, WithFileUploads;

    #[Url(as: 'tab')]
    public string $activeTab = 'my-requests';
    
    #[Url(as: 'status')]
    public string $statusFilter = 'all';

    // Form state - simple properties
    public bool $showForm = false;
    public string $leave_type = 'permission';
    public string $start_date = '';
    public string $end_date = '';
    public string $reason = '';
    public $attachment;

    // Modal state
    public ?int $viewingId = null;
    public ?int $reviewingId = null;
    public string $reviewAction = '';
    public string $reviewNotes = '';

    public function mount(): void
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');
    }

    // Placeholder for lazy loading
    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="animate-pulse space-y-4">
            <div class="h-8 bg-gray-200 rounded w-1/4"></div>
            <div class="h-32 bg-gray-200 rounded"></div>
            <div class="h-64 bg-gray-200 rounded"></div>
        </div>
        HTML;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->statusFilter = 'all';
        $this->resetPage();
    }

    public function openForm(): void
    {
        $this->reset(['leave_type', 'reason', 'attachment']);
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
    }

    public function submitForm(): void
    {
        $this->validate([
            'leave_type' => 'required|in:sick,permission,emergency,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);

        $path = $this->attachment?->store('leave-attachments', 'public');

        LeaveRequest::create([
            'user_id' => Auth::id(),
            'leave_type' => $this->leave_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'total_days' => $start->diffInDays($end) + 1,
            'reason' => $this->reason,
            'attachment' => $path,
            'status' => 'pending',
        ]);

        $this->closeForm();
        $this->dispatch('toast', message: 'Pengajuan berhasil dikirim', type: 'success');
    }

    public function viewRequest(int $id): void
    {
        $this->viewingId = $id;
    }

    public function closeView(): void
    {
        $this->viewingId = null;
    }

    public function cancelRequest(int $id): void
    {
        LeaveRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);
        
        $this->viewingId = null;
        $this->dispatch('toast', message: 'Pengajuan dibatalkan', type: 'success');
    }

    public function openReview(int $id, string $action): void
    {
        $this->reviewingId = $id;
        $this->reviewAction = $action;
        $this->reviewNotes = '';
    }

    public function closeReview(): void
    {
        $this->reviewingId = null;
        $this->reviewAction = '';
        $this->reviewNotes = '';
    }

    public function submitReview(): void
    {
        LeaveRequest::where('id', $this->reviewingId)
            ->update([
                'status' => $this->reviewAction,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_notes' => $this->reviewNotes,
            ]);

        $msg = $this->reviewAction === 'approved' ? 'Pengajuan disetujui' : 'Pengajuan ditolak';
        $this->closeReview();
        $this->viewingId = null;
        $this->dispatch('toast', message: $msg, type: 'success');
    }

    public function render()
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->hasAnyRole(['Super Admin', 'Ketua', 'Wakil Ketua', 'BPH']);

        // Simple stats - cached
        $stats = Cache::remember("leave_stats_{$userId}_{$this->activeTab}", 60, function () use ($userId, $isAdmin) {
            if ($this->activeTab === 'my-requests') {
                return [
                    'pending' => LeaveRequest::where('user_id', $userId)->where('status', 'pending')->count(),
                    'approved' => LeaveRequest::where('user_id', $userId)->where('status', 'approved')->count(),
                    'rejected' => LeaveRequest::where('user_id', $userId)->where('status', 'rejected')->count(),
                ];
            }
            return [
                'pending' => LeaveRequest::where('status', 'pending')->count(),
                'approved' => LeaveRequest::where('status', 'approved')->count(),
                'rejected' => LeaveRequest::where('status', 'rejected')->count(),
            ];
        });

        // Query with eager loading
        $query = $this->activeTab === 'my-requests'
            ? LeaveRequest::where('user_id', $userId)
            : LeaveRequest::with('user:id,name,nim');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $requests = $query->latest()->paginate(10);

        // Get viewing request if needed
        $viewingRequest = $this->viewingId 
            ? LeaveRequest::with(['user:id,name,nim', 'reviewer:id,name'])->find($this->viewingId)
            : null;

        return view('livewire.leave.leave-manager', [
            'requests' => $requests,
            'stats' => $stats,
            'isAdmin' => $isAdmin,
            'viewingRequest' => $viewingRequest,
        ]);
    }
}
