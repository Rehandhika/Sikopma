<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Schedule;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ScheduleTemplates extends Component
{
    use WithPagination;

    public $templates = [];
    public $selectedTemplate = null;
    public $showModal = false;
    public $isEditing = false;
    
    // Form fields
    public $name;
    public $day;
    public $session;
    public $timeStart;
    public $timeEnd;
    public $description;
    public $isActive = true;

    protected $rules = [
        'name' => 'required|string|max:100',
        'day' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        'session' => 'required|integer|min:1|max:3',
        'timeStart' => 'required|date_format:H:i',
        'timeEnd' => 'required|date_format:H:i|after:timeStart',
        'description' => 'nullable|string|max:255',
        'isActive' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'Nama template harus diisi.',
        'day.required' => 'Hari harus dipilih.',
        'session.required' => 'Sesi harus dipilih.',
        'timeStart.required' => 'Waktu mulai harus diisi.',
        'timeEnd.required' => 'Waktu selesai harus diisi.',
        'timeEnd.after' => 'Waktu selesai harus setelah waktu mulai.',
    ];

    public function mount()
    {
        $this->loadTemplates();
    }

    public function loadTemplates()
    {
        $this->templates = Schedule::orderBy('day')
            ->orderBy('session')
            ->orderBy('time_start')
            ->get();
    }

    public function createTemplate()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function editTemplate($id)
    {
        $template = Schedule::find($id);
        
        if ($template) {
            $this->selectedTemplate = $template;
            $this->name = $template->name;
            $this->day = $template->day;
            $this->session = $template->session;
            $this->timeStart = $template->time_start;
            $this->timeEnd = $template->time_end;
            $this->description = $template->description;
            $this->isActive = $template->is_active;
            $this->isEditing = true;
            $this->showModal = true;
        }
    }

    public function saveTemplate()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            if ($this->isEditing && $this->selectedTemplate) {
                // Update existing template
                $this->selectedTemplate->update([
                    'name' => $this->name,
                    'day' => $this->day,
                    'session' => $this->session,
                    'time_start' => $this->timeStart,
                    'time_end' => $this->timeEnd,
                    'description' => $this->description,
                    'is_active' => $this->isActive,
                ]);

                // Log activity
                ActivityLogService::log("Mengubah template jadwal '{$this->name}'");

                $message = 'Template jadwal berhasil diperbarui!';
            } else {
                // Check for duplicate
                $existing = Schedule::where('day', $this->day)
                    ->where('session', $this->session)
                    ->where('time_start', $this->timeStart)
                    ->where('time_end', $this->timeEnd)
                    ->first();

                if ($existing) {
                    throw new \Exception('Template dengan konfigurasi yang sama sudah ada.');
                }

                // Create new template
                Schedule::create([
                    'name' => $this->name,
                    'day' => $this->day,
                    'session' => $this->session,
                    'time_start' => $this->timeStart,
                    'time_end' => $this->timeEnd,
                    'description' => $this->description,
                    'is_active' => $this->isActive,
                ]);

                // Log activity
                ActivityLogService::log("Membuat template jadwal '{$this->name}'");

                $message = 'Template jadwal berhasil dibuat!';
            }

            DB::commit();

            $this->dispatch('toast', message: $message, type: 'success');
            $this->closeModal();
            $this->loadTemplates();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toast', message: 'Gagal menyimpan template: ' . $e->getMessage(), type: 'error');
        }
    }

    public function deleteTemplate($id)
    {
        $template = Schedule::find($id);
        
        if ($template) {
            try {
                // Check if template is being used in assignments
                $hasAssignments = \App\Models\ScheduleAssignment::where('schedule_id', $template->id)->exists();
                
                if ($hasAssignments) {
                    $this->dispatch('toast', message: 'Template tidak dapat dihapus karena sudah digunakan dalam penugasan.', type: 'error');
                    return;
                }

                $templateName = $template->name;
                $template->delete();
                
                // Log activity
                ActivityLogService::log("Menghapus template jadwal '{$templateName}'");
                
                $this->dispatch('toast', message: 'Template berhasil dihapus!', type: 'success');
                $this->loadTemplates();

            } catch (\Exception $e) {
                $this->dispatch('toast', message: 'Gagal menghapus template: ' . $e->getMessage(), type: 'error');
            }
        }
    }

    public function toggleStatus($id)
    {
        $template = Schedule::find($id);
        
        if ($template) {
            $template->update(['is_active' => !$template->is_active]);
            $status = $template->is_active ? 'diaktifkan' : 'dinonaktifkan';
            $this->dispatch('toast', message: "Template berhasil $status!", type: 'success');
            $this->loadTemplates();
        }
    }

    public function duplicateTemplate($id)
    {
        $template = Schedule::find($id);
        
        if ($template) {
            $this->resetForm();
            $this->name = $template->name . ' (Copy)';
            $this->day = $template->day;
            $this->session = $template->session;
            $this->timeStart = $template->time_start;
            $this->timeEnd = $template->time_end;
            $this->description = $template->description;
            $this->isActive = $template->is_active;
            $this->isEditing = false;
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->selectedTemplate = null;
        $this->name = '';
        $this->day = '';
        $this->session = 1;
        $this->timeStart = '';
        $this->timeEnd = '';
        $this->description = '';
        $this->isActive = true;
        $this->isEditing = false;
    }

    public function getDayName($day)
    {
        $days = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];

        return $days[$day] ?? $day;
    }

    public function getSessionName($session)
    {
        $sessions = [
            1 => 'Sesi 1 (Pagi)',
            2 => 'Sesi 2 (Siang)',
            3 => 'Sesi 3 (Sore)',
        ];

        return $sessions[$session] ?? "Sesi $session";
    }

    public function getTemplatesByDay()
    {
        return $this->templates->groupBy('day');
    }

    public function getStats()
    {
        return [
            'total' => $this->templates->count(),
            'active' => $this->templates->where('is_active', true)->count(),
            'inactive' => $this->templates->where('is_active', false)->count(),
            'by_session' => $this->templates->groupBy('session')->map->count(),
        ];
    }

    public function render()
    {
        return view('livewire.schedule.schedule-templates', [
            'templatesByDay' => $this->getTemplatesByDay(),
            'stats' => $this->getStats(),
        ])->layout('layouts.app')->title('Template Jadwal');
    }
}
