<?php

namespace App\Livewire\Admin\Settings;

use App\Models\AcademicHoliday;
use App\Services\StoreStatusService;
use Carbon\Carbon;
use Livewire\Component;

class StoreSettings extends Component
{
    public $currentStatus = [];

    public $statusLoaded = false;

    // Next Open Mode properties
    public $nextOpenMode = 'default';

    public $customClosedMessage = '';

    public $customNextOpenDate = '';

    public $academicHolidayStart = '';

    public $academicHolidayEnd = '';

    public $academicHolidayName = '';

    // Academic Holidays list
    public $academicHolidays = [];

    public $showHolidayForm = false;

    public $editingHolidayId = null;

    public $holidayForm = [
        'name' => '',
        'start_date' => '',
        'end_date' => '',
    ];

    protected StoreStatusService $storeStatusService;

    public function boot(StoreStatusService $storeStatusService)
    {
        $this->storeStatusService = $storeStatusService;
    }

    public function mount()
    {
        if (! auth()->user()->hasAnyRole(['Super Admin', 'Ketua', 'Wakil Ketua'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $this->refreshStatus();
        $this->loadNextOpenSettings();
        $this->loadAcademicHolidays();
    }

    public function loadNextOpenSettings()
    {
        $setting = \App\Models\StoreSetting::first();

        if ($setting) {
            $this->nextOpenMode = $setting->next_open_mode ?? 'default';
            $this->customClosedMessage = $setting->custom_closed_message ?? '';
            $this->customNextOpenDate = $setting->custom_next_open_date?->format('Y-m-d') ?? '';
            $this->academicHolidayStart = $setting->academic_holiday_start?->format('Y-m-d') ?? '';
            $this->academicHolidayEnd = $setting->academic_holiday_end?->format('Y-m-d') ?? '';
            $this->academicHolidayName = $setting->academic_holiday_name ?? '';
        }
    }

    public function loadAcademicHolidays()
    {
        $this->academicHolidays = AcademicHoliday::orderBy('start_date', 'desc')
            ->take(20)
            ->get()
            ->toArray();
    }

    public function refreshStatus()
    {
        $this->currentStatus = $this->storeStatusService->getStatus();
        $this->statusLoaded = true;
    }

    // Override Buka methods
    public function enableOpenOverride()
    {
        $this->storeStatusService->manualOpenOverride(true);
        $this->refreshStatus();

        $this->dispatch('toast', message: 'Override buka diaktifkan - koperasi dapat buka di luar jadwal jika ada pengurus', type: 'success');
    }

    public function disableOpenOverride()
    {
        $this->storeStatusService->manualOpenOverride(false);
        $this->refreshStatus();

        $this->dispatch('toast', message: 'Override buka dinonaktifkan - kembali ke jadwal normal', type: 'success');
    }

    // Manual Mode methods
    public function enableManualMode()
    {
        $reason = 'Mode manual diaktifkan oleh admin';
        $this->storeStatusService->toggleManualMode(false, $reason);
        $this->refreshStatus();

        $this->dispatch('toast', message: 'Mode manual diaktifkan - Anda memiliki kontrol penuh terhadap status', type: 'info');
    }

    public function setManualStatus(bool $isOpen)
    {
        $reason = $isOpen ? 'Dibuka manual oleh admin' : 'Ditutup manual oleh admin';
        $this->storeStatusService->toggleManualMode($isOpen, $reason);
        $this->refreshStatus();

        $statusText = $isOpen ? 'BUKA' : 'TUTUP';
        $this->dispatch('toast', message: "Status diubah menjadi {$statusText} (mode manual, type: 'success')");
    }

    public function disableManualMode()
    {
        $this->storeStatusService->backToAutoMode();
        $this->refreshStatus();

        $this->dispatch('toast', message: 'Mode manual dinonaktifkan - kembali ke mode otomatis', type: 'success');
    }

    public function resetToAuto()
    {
        $this->storeStatusService->backToAutoMode();
        $this->storeStatusService->resetToDefaultMode();
        $this->loadNextOpenSettings();
        $this->refreshStatus();

        $this->dispatch('toast', message: 'Semua pengaturan manual direset - kembali ke mode otomatis', type: 'success');
    }

    // Next Open Mode methods
    public function saveNextOpenSettings()
    {
        if ($this->nextOpenMode === 'custom') {
            // Validate
            if (empty($this->academicHolidayName) && empty($this->customClosedMessage)) {
                $this->dispatch('toast', message: 'Harap isi nama libur atau pesan kustom', type: 'error');

                return;
            }

            if (! empty($this->academicHolidayStart) && ! empty($this->academicHolidayEnd)) {
                $start = Carbon::parse($this->academicHolidayStart);
                $end = Carbon::parse($this->academicHolidayEnd);

                if ($end->lt($start)) {
                    $this->dispatch('toast', message: 'Tanggal akhir harus setelah tanggal mulai', type: 'error');

                    return;
                }
            }

            $this->storeStatusService->setCustomNextOpenMode(
                $this->customClosedMessage ?: null,
                ! empty($this->customNextOpenDate) ? Carbon::parse($this->customNextOpenDate) : null,
                ! empty($this->academicHolidayStart) ? Carbon::parse($this->academicHolidayStart) : null,
                ! empty($this->academicHolidayEnd) ? Carbon::parse($this->academicHolidayEnd) : null,
                $this->academicHolidayName ?: null
            );

            $this->dispatch('toast', message: 'Mode kustom berhasil diaktifkan', type: 'success');
        } else {
            $this->storeStatusService->resetToDefaultMode();
            $this->dispatch('toast', message: 'Kembali ke mode default', type: 'success');
        }

        $this->refreshStatus();
    }

    public function resetNextOpenMode()
    {
        $this->storeStatusService->resetToDefaultMode();
        $this->loadNextOpenSettings();
        $this->refreshStatus();

        $this->dispatch('toast', message: 'Mode keterangan buka direset ke default', type: 'success');
    }

    // Academic Holiday CRUD methods
    public function openHolidayForm()
    {
        $this->showHolidayForm = true;
        $this->editingHolidayId = null;
        $this->holidayForm = [
            'name' => '',
            'start_date' => '',
            'end_date' => '',
        ];
    }

    public function editHoliday($id)
    {
        $holiday = AcademicHoliday::find($id);
        if ($holiday) {
            $this->editingHolidayId = $id;
            $this->showHolidayForm = true;
            $this->holidayForm = [
                'name' => $holiday->name,
                'start_date' => $holiday->start_date->format('Y-m-d'),
                'end_date' => $holiday->end_date->format('Y-m-d'),
            ];
        }
    }

    public function saveHoliday()
    {
        if (empty($this->holidayForm['name'])) {
            $this->dispatch('toast', message: 'Nama libur harus diisi', type: 'error');

            return;
        }

        if (empty($this->holidayForm['start_date']) || empty($this->holidayForm['end_date'])) {
            $this->dispatch('toast', message: 'Tanggal mulai dan akhir harus diisi', type: 'error');

            return;
        }

        $start = Carbon::parse($this->holidayForm['start_date']);
        $end = Carbon::parse($this->holidayForm['end_date']);

        if ($end->lt($start)) {
            $this->dispatch('toast', message: 'Tanggal akhir harus setelah tanggal mulai', type: 'error');

            return;
        }

        $data = [
            'name' => $this->holidayForm['name'],
            'start_date' => $start,
            'end_date' => $end,
            'is_active' => true,
        ];

        if ($this->editingHolidayId) {
            AcademicHoliday::where('id', $this->editingHolidayId)->update($data);
            $this->dispatch('toast', message: 'Libur berhasil diperbarui', type: 'success');
        } else {
            $data['created_by'] = auth()->id();
            AcademicHoliday::create($data);
            $this->dispatch('toast', message: 'Libur berhasil ditambahkan', type: 'success');
        }

        $this->showHolidayForm = false;
        $this->editingHolidayId = null;
        $this->loadAcademicHolidays();
        $this->storeStatusService->forceUpdate();
        $this->refreshStatus();
    }

    public function cancelHolidayForm()
    {
        $this->showHolidayForm = false;
        $this->editingHolidayId = null;
    }

    public function toggleHolidayStatus($id)
    {
        $holiday = AcademicHoliday::find($id);
        if ($holiday) {
            $holiday->update(['is_active' => ! $holiday->is_active]);
            $this->loadAcademicHolidays();
            $this->storeStatusService->forceUpdate();
            $this->refreshStatus();
        }
    }

    public function deleteHoliday($id)
    {
        AcademicHoliday::where('id', $id)->delete();
        $this->loadAcademicHolidays();
        $this->storeStatusService->forceUpdate();
        $this->refreshStatus();
        $this->dispatch('toast', message: 'Libur berhasil dihapus', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.settings.store-settings')
            ->layout('layouts.app')
            ->title('Pengaturan Toko');
    }
}
