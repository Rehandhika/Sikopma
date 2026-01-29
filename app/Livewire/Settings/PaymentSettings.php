<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\PaymentConfigurationService;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Storage;

class PaymentSettings extends Component
{
    use WithFileUploads;

    // Payment method toggles
    public bool $cashEnabled = true;
    public bool $transferEnabled = false;
    public bool $qrisEnabled = false;

    // Multiple bank accounts
    public array $bankAccounts = [];
    
    // Form for adding/editing bank
    public bool $showBankForm = false;
    public ?string $editingBankId = null;
    public string $bankName = '';
    public string $accountNumber = '';
    public string $accountHolder = '';

    // QRIS
    public $qrisImage; // Livewire file upload
    public ?string $currentQrisImage = null;
    public ?string $qrisImagePreview = null;

    protected PaymentConfigurationService $paymentService;

    protected $rules = [
        'qrisImage' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'bankName' => 'required|string|max:100',
        'accountNumber' => 'required|string|max:50',
        'accountHolder' => 'required|string|max:100',
    ];

    protected $messages = [
        'qrisImage.image' => 'File harus berupa gambar',
        'qrisImage.mimes' => 'Format file harus JPG, JPEG, atau PNG',
        'qrisImage.max' => 'Ukuran file maksimal 2MB',
        'bankName.required' => 'Nama bank wajib diisi',
        'bankName.max' => 'Nama bank maksimal 100 karakter',
        'accountNumber.required' => 'Nomor rekening wajib diisi',
        'accountNumber.max' => 'Nomor rekening maksimal 50 karakter',
        'accountHolder.required' => 'Nama pemilik rekening wajib diisi',
        'accountHolder.max' => 'Nama pemilik rekening maksimal 100 karakter',
    ];

    public function boot(PaymentConfigurationService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function mount(): void
    {
        // Authorization check
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Ketua', 'Wakil Ketua'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Load existing configuration
        $config = $this->paymentService->getAll();

        $this->cashEnabled = $config['cash_enabled'];
        $this->transferEnabled = $config['transfer_enabled'];
        $this->qrisEnabled = $config['qris_enabled'];
        $this->bankAccounts = $config['transfer_banks'] ?? [];
        $this->currentQrisImage = $config['qris_image'] ?? null;
    }

    /**
     * Open form to add new bank
     */
    public function openAddBankForm(): void
    {
        $this->resetBankForm();
        $this->showBankForm = true;
    }

    /**
     * Open form to edit existing bank
     */
    public function editBank(string $bankId): void
    {
        $bank = collect($this->bankAccounts)->firstWhere('id', $bankId);
        
        if ($bank) {
            $this->editingBankId = $bankId;
            $this->bankName = $bank['bank_name'];
            $this->accountNumber = $bank['account_number'];
            $this->accountHolder = $bank['account_holder'];
            $this->showBankForm = true;
        }
    }

    /**
     * Close bank form
     */
    public function closeBankForm(): void
    {
        $this->resetBankForm();
    }

    /**
     * Reset bank form fields
     */
    protected function resetBankForm(): void
    {
        $this->showBankForm = false;
        $this->editingBankId = null;
        $this->bankName = '';
        $this->accountNumber = '';
        $this->accountHolder = '';
        $this->resetValidation(['bankName', 'accountNumber', 'accountHolder']);
    }

    /**
     * Save bank (add or update)
     */
    public function saveBank(): void
    {
        $this->validate([
            'bankName' => 'required|string|max:100',
            'accountNumber' => 'required|string|max:50',
            'accountHolder' => 'required|string|max:100',
        ]);

        try {
            $bankData = [
                'bank_name' => $this->bankName,
                'account_number' => $this->accountNumber,
                'account_holder' => $this->accountHolder,
                'is_active' => true,
            ];

            if ($this->editingBankId) {
                // Update existing bank
                $this->paymentService->updateBankAccount($this->editingBankId, $bankData);
                $this->dispatch('toast', message: 'Rekening bank berhasil diperbarui', type: 'success');
            } else {
                // Add new bank
                $this->paymentService->addBankAccount($bankData);
                $this->dispatch('toast', message: 'Rekening bank berhasil ditambahkan', type: 'success');
            }

            // Refresh bank accounts list
            $this->bankAccounts = $this->paymentService->getBankAccounts();
            $this->resetBankForm();

            // Log activity
            ActivityLogService::logSettingsUpdated('Pembayaran - Rekening Bank');

        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Gagal menyimpan rekening bank: ' . $e->getMessage(), type: 'error');
        }
    }

    /**
     * Toggle bank active status
     */
    public function toggleBank(string $bankId): void
    {
        try {
            $this->paymentService->toggleBankAccount($bankId);
            $this->bankAccounts = $this->paymentService->getBankAccounts();
            $this->dispatch('toast', message: 'Status rekening bank berhasil diubah', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Gagal mengubah status: ' . $e->getMessage(), type: 'error');
        }
    }

    /**
     * Delete bank account
     */
    public function deleteBank(string $bankId): void
    {
        try {
            $this->paymentService->deleteBankAccount($bankId);
            $this->bankAccounts = $this->paymentService->getBankAccounts();
            $this->dispatch('toast', message: 'Rekening bank berhasil dihapus', type: 'success');
            
            // Log activity
            ActivityLogService::logSettingsUpdated('Pembayaran - Hapus Rekening Bank');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Gagal menghapus rekening bank: ' . $e->getMessage(), type: 'error');
        }
    }


    public function updatedQrisImage(): void
    {
        $this->validateOnly('qrisImage');

        if ($this->qrisImage) {
            $this->qrisImagePreview = $this->qrisImage->temporaryUrl();
        }
    }

    public function removeQrisImage(): void
    {
        // Remove the uploaded file preview
        $this->qrisImage = null;
        $this->qrisImagePreview = null;

        // If there's an existing image, mark it for removal
        if ($this->currentQrisImage) {
            // Delete the file from storage
            if (Storage::disk('public')->exists($this->currentQrisImage)) {
                Storage::disk('public')->delete($this->currentQrisImage);
            }
            $this->currentQrisImage = null;

            // Update the setting to remove the image path
            $this->paymentService->saveConfiguration([
                'qris_image' => '',
            ]);

            $this->dispatch('toast', message: 'Gambar QRIS berhasil dihapus', type: 'success');
        }
    }

    /**
     * Validate that at least one payment method is enabled
     */
    protected function validateAtLeastOneEnabled(): bool
    {
        return $this->cashEnabled || $this->transferEnabled || $this->qrisEnabled;
    }

    /**
     * Validate QRIS configuration when enabled
     */
    protected function validateQrisConfiguration(): bool
    {
        if (!$this->qrisEnabled) {
            return true;
        }

        // QRIS requires an image (either new upload or existing)
        return $this->qrisImage !== null || !empty($this->currentQrisImage);
    }

    /**
     * Validate transfer configuration when enabled
     */
    protected function validateTransferConfiguration(): bool
    {
        if (!$this->transferEnabled) {
            return true;
        }

        // Transfer requires at least one active bank account
        $activeBanks = array_filter($this->bankAccounts, fn($bank) => $bank['is_active'] ?? true);
        return !empty($activeBanks);
    }

    public function save(): void
    {
        // Validate file upload rules only
        $this->validateOnly('qrisImage');

        // Custom validation: at least one method must be enabled
        if (!$this->validateAtLeastOneEnabled()) {
            $this->addError('general', 'Minimal satu metode pembayaran harus aktif');
            $this->dispatch('toast', message: 'Minimal satu metode pembayaran harus aktif', type: 'error');
            return;
        }

        // Custom validation: QRIS requires image when enabled
        if (!$this->validateQrisConfiguration()) {
            $this->addError('qrisImage', 'Gambar QRIS wajib diupload');
            $this->dispatch('toast', message: 'Gambar QRIS wajib diupload jika QRIS diaktifkan', type: 'error');
            return;
        }

        // Custom validation: Transfer requires at least one active bank when enabled
        if (!$this->validateTransferConfiguration()) {
            $this->addError('general', 'Minimal satu rekening bank aktif diperlukan jika Transfer diaktifkan');
            $this->dispatch('toast', message: 'Tambahkan minimal satu rekening bank aktif jika Transfer diaktifkan', type: 'error');
            return;
        }

        try {
            $data = [
                'cash_enabled' => $this->cashEnabled,
                'transfer_enabled' => $this->transferEnabled,
                'qris_enabled' => $this->qrisEnabled,
            ];

            // Handle QRIS image upload
            if ($this->qrisImage) {
                // Delete old image if exists
                if ($this->currentQrisImage && Storage::disk('public')->exists($this->currentQrisImage)) {
                    Storage::disk('public')->delete($this->currentQrisImage);
                }

                // Store new image
                $path = $this->qrisImage->store('payment', 'public');
                $data['qris_image'] = $path;
                $this->currentQrisImage = $path;
                $this->qrisImage = null;
                $this->qrisImagePreview = null;
            }

            // Save configuration
            $this->paymentService->saveConfiguration($data);

            // Log activity
            ActivityLogService::logSettingsUpdated('Pembayaran');

            $this->dispatch('toast', message: 'Pengaturan pembayaran berhasil disimpan', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Gagal menyimpan pengaturan: ' . $e->getMessage(), type: 'error');
        }
    }

    /**
     * Get the current QRIS image URL for display
     */
    public function getQrisImageUrlProperty(): ?string
    {
        if ($this->qrisImagePreview) {
            return $this->qrisImagePreview;
        }

        if ($this->currentQrisImage && Storage::disk('public')->exists($this->currentQrisImage)) {
            return Storage::disk('public')->url($this->currentQrisImage);
        }

        return null;
    }

    public function render()
    {
        return view('livewire.settings.payment-settings')
            ->layout('layouts.app')
            ->title('Pengaturan Pembayaran');
    }
}
