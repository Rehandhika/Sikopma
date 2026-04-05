<?php

namespace App\Livewire\Swap;

use App\Models\ScheduleAssignment;
use App\Models\SwapRequest;
use App\Models\User;
use App\Services\SwapService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Tukar Jadwal')]
#[Layout('layouts.app')]
#[Lazy]
class SwapManager extends Component
{
    use WithPagination;

    #[Url(as: 'tab')]
    public string $activeTab = 'my-requests'; // my-requests, received, admin

    #[Url(as: 'status')]
    public string $statusFilter = 'all';

    // Create form
    public bool $showForm = false;

    public ?int $selectedAssignment = null;

    public string $targetDate = '';

    public int $targetSession = 0;

    public ?int $selectedTarget = null;

    public string $reason = '';

    // Detail/Review modal
    public ?int $viewingId = null;

    public ?int $reviewingId = null;

    public string $reviewAction = '';

    public string $reviewNotes = '';

    public function mount(): void
    {
        // Check permission
        abort_unless(auth()->user()->can('ajukan_tukar_jadwal'), 403, 'Unauthorized.');
        
        $this->targetDate = now()->addDay()->format('Y-m-d');
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="animate-pulse space-y-4">
            <div class="h-8 bg-gray-200 rounded w-1/4"></div>
            <div class="h-32 bg-gray-200 rounded"></div>
        </div>
        HTML;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->statusFilter = 'all';
        $this->resetPage();
    }

    // === CREATE FORM ===
    public function openForm(): void
    {
        $this->reset(['selectedAssignment', 'targetSession', 'selectedTarget', 'reason']);
        $this->targetDate = now()->addDay()->format('Y-m-d');
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
    }

    public function submitForm(): void
    {
        // Check permission
        abort_unless(auth()->user()->can('ajukan_tukar_jadwal'), 403, 'Unauthorized.');

        $this->validate([
            'selectedAssignment' => 'required|exists:schedule_assignments,id',
            'targetDate' => 'required|date|after_or_equal:today',
            'targetSession' => 'required|in:1,2,3',
            'selectedTarget' => 'required|exists:users,id',
            'reason' => 'required|string|min:10|max:500',
        ], [
            'selectedAssignment.required' => 'Pilih jadwal Anda yang ingin ditukar',
            'targetSession.required' => 'Pilih sesi target',
            'selectedTarget.required' => 'Pilih anggota target',
            'reason.min' => 'Alasan minimal 10 karakter',
        ]);

        try {
            // Get assignments
            $myAssignment = ScheduleAssignment::find($this->selectedAssignment);
            $targetAssignment = ScheduleAssignment::where('date', $this->targetDate)
                ->where('session', $this->targetSession)
                ->where('user_id', $this->selectedTarget)
                ->first();

            if (! $targetAssignment) {
                $this->addError('selectedTarget', 'Target tidak memiliki jadwal di waktu tersebut');
                return;
            }

            // Use service layer for validation and creation
            $service = app(SwapService::class);
            $swapRequest = $service->createSwapRequest(
                $myAssignment,
                $targetAssignment,
                $this->reason
            );

            $this->closeForm();
            $this->clearCache();
            $this->dispatch('toast', message: 'Permintaan tukar jadwal dikirim', type: 'success');

        } catch (ValidationException $e) {
            // Handle validation errors from service
            foreach ($e->errors() as $field => $messages) {
                $formField = match ($field) {
                    'assignment' => 'myAssignment', // Or targetAssignment
                    default => $field,
                };
                
                $this->addError($formField, $messages[0]);
                
                // Show toast for errors that don't match UI fields perfectly
                if (!in_array($formField, ['myAssignment', 'targetAssignment', 'reason'])) {
                    $this->dispatch('toast', message: $messages[0], type: 'error');
                }
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Gagal mengirim permintaan: ' . $e->getMessage(), type: 'error');
        }
    }

    // === VIEW & ACTIONS ===
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
        SwapRequest::where('id', $id)
            ->where('requester_id', Auth::id())
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        $this->viewingId = null;
        $this->clearCache();
        $this->dispatch('toast', message: 'Permintaan dibatalkan', type: 'success');
        
        // Trigger re-render
        $this->resetPage();
    }

    // Target user approve/reject
    public function targetApprove(int $id): void
    {
        // Check permission
        abort_unless(auth()->user()->can('ajukan_tukar_jadwal'), 403, 'Unauthorized.');

        try {
            $swap = SwapRequest::find($id);
            if (!$swap || $swap->target_id !== Auth::id()) {
                $this->dispatch('toast', message: 'Permintaan tidak ditemukan', type: 'error');
                return;
            }

            $service = app(SwapService::class);
            $service->targetRespond($swap, true, 'Disetujui');

            $this->viewingId = null;
            $this->clearCache();
            $this->dispatch('toast', message: 'Permintaan disetujui, menunggu admin', type: 'success');
            
            // Trigger re-render
            $this->resetPage();

        } catch (ValidationException $e) {
            $errorMsg = collect($e->errors())->flatten()->first() ?? 'Validasi gagal';
            $this->dispatch('toast', message: $errorMsg, type: 'error');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Gagal memproses: ' . $e->getMessage(), type: 'error');
        }
    }

    public function targetReject(int $id): void
    {
        // Check permission
        abort_unless(auth()->user()->can('ajukan_tukar_jadwal'), 403, 'Unauthorized.');

        try {
            $swap = SwapRequest::find($id);
            if (!$swap || $swap->target_id !== Auth::id()) {
                $this->dispatch('toast', message: 'Permintaan tidak ditemukan', type: 'error');
                return;
            }

            $service = app(SwapService::class);
            $service->targetRespond($swap, false, 'Ditolak oleh target');

            $this->viewingId = null;
            $this->clearCache();
            $this->dispatch('toast', message: 'Permintaan ditolak', type: 'success');
            
            // Trigger re-render
            $this->resetPage();

        } catch (ValidationException $e) {
            $errorMsg = collect($e->errors())->flatten()->first() ?? 'Validasi gagal';
            $this->dispatch('toast', message: $errorMsg, type: 'error');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Gagal memproses: ' . $e->getMessage(), type: 'error');
        }
    }

    // Admin approve/reject
    public function openAdminReview(int $id, string $action): void
    {
        $this->reviewingId = $id;
        $this->reviewAction = $action;
        $this->reviewNotes = '';
    }

    public function closeAdminReview(): void
    {
        $this->reviewingId = null;
        $this->reviewAction = '';
        $this->reviewNotes = '';
    }

    public function submitAdminReview(): void
    {
        // Check permission
        abort_unless(auth()->user()->can('setujui_tukar_jadwal'), 403, 'Unauthorized.');

        $swap = SwapRequest::with(['requesterAssignment', 'targetAssignment'])->find($this->reviewingId);
        if (! $swap) {
            $this->dispatch('toast', message: 'Permintaan tidak ditemukan', type: 'error');
            return;
        }

        try {
            $service = app(SwapService::class);
            $approved = $this->reviewAction === 'approved';
            
            $service->adminRespond($swap, $approved, $this->reviewNotes);

            $this->closeAdminReview();
            $this->viewingId = null;
            $this->clearCache();

            $msg = $approved ? 'Tukar jadwal disetujui dan diproses' : 'Permintaan ditolak';
            $this->dispatch('toast', message: $msg, type: 'success');
            
            // Trigger re-render
            $this->resetPage();

        } catch (ValidationException $e) {
            $errorMsg = collect($e->errors())->flatten()->first() ?? 'Validasi gagal';
            $this->dispatch('toast', message: $errorMsg, type: 'error');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Gagal memproses: ' . $e->getMessage(), type: 'error');
        }
    }

    private function clearCache(): void
    {
        Cache::forget('swap_stats_'.Auth::id());
    }

    public function render()
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->can('setujui_tukar_jadwal');

        // Stats - cached
        $stats = Cache::remember("swap_stats_{$userId}_{$this->activeTab}", 60, function () use ($userId) {
            if ($this->activeTab === 'my-requests') {
                return [
                    'pending' => SwapRequest::where('requester_id', $userId)->where('status', 'pending')->count(),
                    'approved' => SwapRequest::where('requester_id', $userId)->whereIn('status', ['target_approved', 'admin_approved'])->count(),
                    'rejected' => SwapRequest::where('requester_id', $userId)->whereIn('status', ['target_rejected', 'admin_rejected', 'cancelled'])->count(),
                ];
            } elseif ($this->activeTab === 'received') {
                return [
                    'pending' => SwapRequest::where('target_id', $userId)->where('status', 'pending')->count(),
                    'approved' => SwapRequest::where('target_id', $userId)->where('status', 'target_approved')->count(),
                    'rejected' => SwapRequest::where('target_id', $userId)->where('status', 'target_rejected')->count(),
                ];
            } else {
                return [
                    'pending' => SwapRequest::where('status', 'target_approved')->count(),
                    'approved' => SwapRequest::where('status', 'admin_approved')->count(),
                    'rejected' => SwapRequest::where('status', 'admin_rejected')->count(),
                ];
            }
        });

        // Query
        $query = match ($this->activeTab) {
            'my-requests' => SwapRequest::where('requester_id', $userId),
            'received' => SwapRequest::where('target_id', $userId),
            'admin' => SwapRequest::whereIn('status', ['target_approved', 'admin_approved', 'admin_rejected']),
            default => SwapRequest::where('requester_id', $userId),
        };

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $requests = $query->with([
            'requester:id,name,nim',
            'target:id,name,nim',
            'requesterAssignment:id,date,session,time_start,time_end',
            'targetAssignment:id,date,session,time_start,time_end',
        ])->latest()->paginate(10);

        // My assignments for form
        $myAssignments = $this->showForm
            ? ScheduleAssignment::where('user_id', $userId)
                ->where('date', '>=', now()->format('Y-m-d'))
                ->orderBy('date')
                ->get(['id', 'date', 'session', 'time_start', 'time_end'])
            : collect();

        // Available targets for form
        $availableTargets = ($this->showForm && $this->targetDate && $this->targetSession)
            ? ScheduleAssignment::where('date', $this->targetDate)
                ->where('session', $this->targetSession)
                ->where('user_id', '!=', $userId)
                ->with('user:id,name,nim')
                ->get()
                ->map(fn ($a) => ['id' => $a->user_id, 'name' => $a->user->name, 'nim' => $a->user->nim])
            : collect();

        // Viewing request
        $viewingRequest = $this->viewingId
            ? SwapRequest::with([
                'requester:id,name,nim',
                'target:id,name,nim',
                'requesterAssignment:id,date,session,time_start,time_end',
                'targetAssignment:id,date,session,time_start,time_end',
                'adminResponder:id,name',
            ])->find($this->viewingId)
            : null;

        return view('livewire.swap.swap-manager', [
            'requests' => $requests,
            'stats' => $stats,
            'isAdmin' => $isAdmin,
            'myAssignments' => $myAssignments,
            'availableTargets' => $availableTargets,
            'viewingRequest' => $viewingRequest,
        ]);
    }
}
