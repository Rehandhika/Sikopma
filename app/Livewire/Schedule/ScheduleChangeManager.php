<?php

namespace App\Livewire\Schedule;

use App\Models\ScheduleAssignment;
use App\Models\ScheduleChangeRequest;
use App\Models\AssignmentEditHistory;
use App\Services\ActivityLogService;
use App\Services\ScheduleChangeRequestService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Pengajuan Perubahan Jadwal')]
#[Layout('layouts.app')]
#[Lazy]
class ScheduleChangeManager extends Component
{
    use WithPagination;

    #[Url(as: 'tab')]
    public string $activeTab = 'my-requests';

    // Form state
    public bool $showForm = false;

    public ?int $selectedAssignment = null;

    public string $changeType = 'reschedule'; // reschedule atau cancel

    public string $requestedDate = '';

    public int $requestedSession = 0;

    public string $reason = '';

    // Modal state
    public ?int $viewingId = null;

    public ?int $reviewingId = null;

    public string $reviewAction = '';

    public string $reviewNotes = '';

    public function mount(): void
    {
        // Check permission
        abort_unless(auth()->user()->can('ajukan_perubahan_jadwal'), 403, 'Unauthorized.');
        
        $this->requestedDate = now()->addDay()->format('Y-m-d');
    }

    public function placeholder(): string
    {
        return '<div class="animate-pulse space-y-4"><div class="h-8 bg-gray-200 rounded w-1/4"></div><div class="h-64 bg-gray-200 rounded"></div></div>';
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // === FORM ===
    #[On('openChangeForm')]
    public function openForm(): void
    {
        $this->reset(['selectedAssignment', 'requestedSession', 'reason']);
        $this->changeType = 'reschedule';
        $this->requestedDate = now()->addDay()->format('Y-m-d');
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

    public function submitForm(): void
    {
        // Check permission
        abort_unless(auth()->user()->can('ajukan_perubahan_jadwal'), 403, 'Unauthorized.');

        $rules = [
            'selectedAssignment' => 'required|exists:schedule_assignments,id',
            'changeType' => 'required|in:reschedule,cancel',
            'reason' => 'required|string|min:10|max:500',
        ];

        // Jika reschedule, wajib pilih tanggal & sesi baru
        if ($this->changeType === 'reschedule') {
            $rules['requestedDate'] = 'required|date|after_or_equal:today';
            $rules['requestedSession'] = 'required|in:1,2,3';
        }

        $this->validate($rules, [
            'selectedAssignment.required' => 'Pilih jadwal yang ingin diubah',
            'requestedDate.required' => 'Pilih tanggal tujuan',
            'requestedSession.required' => 'Pilih sesi tujuan',
            'reason.min' => 'Alasan minimal 10 karakter',
        ]);

        try {
            // Use service layer for validation and creation
            $service = app(ScheduleChangeRequestService::class);
            
            $request = $service->submitRequest(
                Auth::id(),
                $this->selectedAssignment,
                $this->changeType,
                $this->reason,
                $this->changeType === 'reschedule' ? Carbon::parse($this->requestedDate) : null,
                $this->changeType === 'reschedule' ? $this->requestedSession : null
            );

            $this->closeForm();
            $this->clearCache();
            $this->dispatch('toast', message: 'Pengajuan berhasil dikirim', type: 'success');

        } catch (ValidationException $e) {
            // Handle validation errors from service
            foreach ($e->errors() as $field => $messages) {
                $formField = match ($field) {
                    'assignment_id' => 'selectedAssignment',
                    'change_type' => 'changeType',
                    'requested_date' => 'requestedDate',
                    'requested_session' => 'requestedSession',
                    default => $field,
                };
                
                $this->addError($formField, $messages[0]);
                
                if (in_array($formField, ['changeType', 'permission'])) {
                    $this->dispatch('toast', message: $messages[0], type: 'error');
                }
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Gagal mengirim pengajuan: ' . $e->getMessage(), type: 'error');
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
        ScheduleChangeRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        $this->viewingId = null;
        $this->clearCache();
        $this->dispatch('toast', message: 'Pengajuan dibatalkan', type: 'success');
        
        // Trigger re-render
        $this->resetPage();
    }

    // === ADMIN REVIEW ===
    public function openReview(int $id, string $action): void
    {
        abort_if(!Auth::user()->can('setujui_perubahan_jadwal'), 403, 'Unauthorized.');

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
        abort_if(!Auth::user()->can('setujui_perubahan_jadwal'), 403, 'Unauthorized.');

        $request = ScheduleChangeRequest::with('originalAssignment')->find($this->reviewingId);
        if (! $request || $request->status !== 'pending') {
            return;
        }

        try {
            $service = app(ScheduleChangeRequestService::class);
            
            if ($this->reviewAction === 'approved') {
                $service->approveRequest($request, Auth::id(), $this->reviewNotes);
                $msg = 'Pengajuan disetujui dan diproses';
            } else {
                $service->rejectRequest($request, Auth::id(), $this->reviewNotes ?: 'Ditolak oleh admin');
                $msg = 'Pengajuan ditolak';
            }

            // Log activity
            $requestUserName = $request->user->name ?? 'Unknown';
            $originalDate = $request->originalAssignment?->date?->format('d/m/Y') ?? 'N/A';
            if ($this->reviewAction === 'approved') {
                ActivityLogService::log("Menyetujui pengajuan perubahan jadwal {$requestUserName} dari tanggal {$originalDate}");
            } else {
                ActivityLogService::log("Menolak pengajuan perubahan jadwal {$requestUserName} dari tanggal {$originalDate}");
            }

            $this->closeReview();
            $this->viewingId = null;
            $this->clearCache();
            $this->dispatch('toast', message: $msg, type: 'success');
            
            // Trigger re-render by resetting the query
            $this->resetPage();

        } catch (ValidationException $e) {
            $errorMsg = collect($e->errors())->flatten()->first() ?? 'Validasi gagal';
            $this->dispatch('toast', message: $errorMsg, type: 'error');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Gagal memproses: '.$e->getMessage(), type: 'error');
        }
    }

    private function clearCache(): void
    {
        $userId = Auth::id();
        Cache::forget("schedule_change_stats_{$userId}_my-requests");
        Cache::forget("schedule_change_stats_{$userId}_admin");
    }

    public function render()
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->can('setujui_perubahan_jadwal');

        // Stats
        $stats = Cache::remember("schedule_change_stats_{$userId}_{$this->activeTab}", 60, function () use ($userId) {
            if ($this->activeTab === 'my-requests') {
                return [
                    'pending' => ScheduleChangeRequest::where('user_id', $userId)->where('status', 'pending')->count(),
                    'approved' => ScheduleChangeRequest::where('user_id', $userId)->where('status', 'approved')->count(),
                    'rejected' => ScheduleChangeRequest::where('user_id', $userId)->whereIn('status', ['rejected', 'cancelled'])->count(),
                ];
            }

            return [
                'pending' => ScheduleChangeRequest::where('status', 'pending')->count(),
                'approved' => ScheduleChangeRequest::where('status', 'approved')->count(),
                'rejected' => ScheduleChangeRequest::where('status', 'rejected')->count(),
            ];
        });

        // Query
        $query = $this->activeTab === 'my-requests'
            ? ScheduleChangeRequest::where('user_id', $userId)
            : ScheduleChangeRequest::with('user:id,name,nim');

        $requests = $query->with([
            'originalAssignment:id,date,session,time_start,time_end',
            'adminResponder:id,name',
        ])->latest()->paginate(10);

        // My assignments for form
        $myAssignments = $this->showForm
            ? ScheduleAssignment::where('user_id', $userId)
                ->where('date', '>=', now()->format('Y-m-d'))
                ->orderBy('date')
                ->get(['id', 'date', 'session', 'time_start', 'time_end'])
            : collect();

        // Viewing request
        $viewingRequest = $this->viewingId
            ? ScheduleChangeRequest::with([
                'user:id,name,nim',
                'originalAssignment:id,date,session,time_start,time_end',
                'adminResponder:id,name',
            ])->find($this->viewingId)
            : null;

        return view('livewire.schedule.schedule-change-manager', [
            'requests' => $requests,
            'stats' => $stats,
            'isAdmin' => $isAdmin,
            'myAssignments' => $myAssignments,
            'viewingRequest' => $viewingRequest,
        ]);
    }
}
