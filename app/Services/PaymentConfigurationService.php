<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentConfigurationService
{
    // Cache configuration
    const CACHE_KEY = 'payment_configuration';
    const CACHE_TTL = 3600; // 1 hour

    // Setting keys
    const KEY_CASH_ENABLED = 'payment_cash_enabled';
    const KEY_TRANSFER_ENABLED = 'payment_transfer_enabled';
    const KEY_QRIS_ENABLED = 'payment_qris_enabled';
    const KEY_TRANSFER_BANKS = 'payment_transfer_banks'; // JSON array of bank accounts
    const KEY_QRIS_IMAGE = 'payment_qris_image';

    // Legacy keys (for migration)
    const KEY_TRANSFER_BANK_NAME = 'payment_transfer_bank_name';
    const KEY_TRANSFER_ACCOUNT_NUMBER = 'payment_transfer_account_number';
    const KEY_TRANSFER_ACCOUNT_HOLDER = 'payment_transfer_account_holder';

    // Payment method identifiers
    const METHOD_CASH = 'cash';
    const METHOD_TRANSFER = 'transfer';
    const METHOD_QRIS = 'qris';

    /**
     * Get all payment configuration
     */
    public function getAll(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            // Get banks from new JSON format, or migrate from legacy format
            $banks = $this->getBankAccounts();
            
            return [
                'cash_enabled' => $this->toBool(Setting::get(self::KEY_CASH_ENABLED, '1')),
                'transfer_enabled' => $this->toBool(Setting::get(self::KEY_TRANSFER_ENABLED, '0')),
                'qris_enabled' => $this->toBool(Setting::get(self::KEY_QRIS_ENABLED, '0')),
                'transfer_banks' => $banks,
                'qris_image' => Setting::get(self::KEY_QRIS_IMAGE, ''),
            ];
        });
    }

    /**
     * Get all bank accounts
     */
    public function getBankAccounts(): array
    {
        $banksJson = Setting::get(self::KEY_TRANSFER_BANKS);
        
        if ($banksJson) {
            $banks = json_decode($banksJson, true);
            return is_array($banks) ? $banks : [];
        }

        // Migrate from legacy single bank format
        $legacyBankName = Setting::get(self::KEY_TRANSFER_BANK_NAME);
        $legacyAccountNumber = Setting::get(self::KEY_TRANSFER_ACCOUNT_NUMBER);
        $legacyAccountHolder = Setting::get(self::KEY_TRANSFER_ACCOUNT_HOLDER);

        if ($legacyBankName && $legacyAccountNumber && $legacyAccountHolder) {
            $banks = [[
                'id' => Str::uuid()->toString(),
                'bank_name' => $legacyBankName,
                'account_number' => $legacyAccountNumber,
                'account_holder' => $legacyAccountHolder,
                'is_active' => true,
            ]];
            
            // Save to new format
            Setting::set(self::KEY_TRANSFER_BANKS, json_encode($banks));
            
            return $banks;
        }

        return [];
    }

    /**
     * Get only active bank accounts
     */
    public function getActiveBankAccounts(): array
    {
        $banks = $this->getBankAccounts();
        return array_values(array_filter($banks, fn($bank) => $bank['is_active'] ?? true));
    }

    /**
     * Add a new bank account
     */
    public function addBankAccount(array $bankData): array
    {
        $banks = $this->getBankAccounts();
        
        $newBank = [
            'id' => Str::uuid()->toString(),
            'bank_name' => trim($bankData['bank_name']),
            'account_number' => trim($bankData['account_number']),
            'account_holder' => trim($bankData['account_holder']),
            'is_active' => $bankData['is_active'] ?? true,
        ];
        
        $banks[] = $newBank;
        
        Setting::set(self::KEY_TRANSFER_BANKS, json_encode($banks));
        $this->clearCache();
        
        return $newBank;
    }

    /**
     * Update an existing bank account
     */
    public function updateBankAccount(string $bankId, array $bankData): ?array
    {
        $banks = $this->getBankAccounts();
        
        foreach ($banks as &$bank) {
            if ($bank['id'] === $bankId) {
                $bank['bank_name'] = trim($bankData['bank_name'] ?? $bank['bank_name']);
                $bank['account_number'] = trim($bankData['account_number'] ?? $bank['account_number']);
                $bank['account_holder'] = trim($bankData['account_holder'] ?? $bank['account_holder']);
                
                if (isset($bankData['is_active'])) {
                    $bank['is_active'] = (bool) $bankData['is_active'];
                }
                
                Setting::set(self::KEY_TRANSFER_BANKS, json_encode($banks));
                $this->clearCache();
                
                return $bank;
            }
        }
        
        return null;
    }

    /**
     * Delete a bank account
     */
    public function deleteBankAccount(string $bankId): bool
    {
        $banks = $this->getBankAccounts();
        $originalCount = count($banks);
        
        $banks = array_values(array_filter($banks, fn($bank) => $bank['id'] !== $bankId));
        
        if (count($banks) < $originalCount) {
            Setting::set(self::KEY_TRANSFER_BANKS, json_encode($banks));
            $this->clearCache();
            return true;
        }
        
        return false;
    }

    /**
     * Toggle bank account active status
     */
    public function toggleBankAccount(string $bankId): ?array
    {
        $banks = $this->getBankAccounts();
        
        foreach ($banks as &$bank) {
            if ($bank['id'] === $bankId) {
                $bank['is_active'] = !($bank['is_active'] ?? true);
                
                Setting::set(self::KEY_TRANSFER_BANKS, json_encode($banks));
                $this->clearCache();
                
                return $bank;
            }
        }
        
        return null;
    }

    /**
     * Get list of enabled payment methods
     */
    public function getEnabledMethods(): array
    {
        $config = $this->getAll();
        $methods = [];

        if ($config['cash_enabled']) {
            $methods[] = [
                'id' => self::METHOD_CASH,
                'name' => 'Tunai',
                'icon' => 'cash',
            ];
        }

        if ($config['transfer_enabled']) {
            $methods[] = [
                'id' => self::METHOD_TRANSFER,
                'name' => 'Transfer Bank',
                'icon' => 'bank',
            ];
        }

        if ($config['qris_enabled']) {
            $methods[] = [
                'id' => self::METHOD_QRIS,
                'name' => 'QRIS',
                'icon' => 'qr-code',
            ];
        }

        return $methods;
    }

    /**
     * Check if a specific payment method is enabled
     */
    public function isMethodEnabled(string $method): bool
    {
        $config = $this->getAll();

        return match ($method) {
            self::METHOD_CASH => $config['cash_enabled'],
            self::METHOD_TRANSFER => $config['transfer_enabled'],
            self::METHOD_QRIS => $config['qris_enabled'],
            default => false,
        };
    }

    /**
     * Get transfer bank details (returns all active banks)
     */
    public function getTransferDetails(): ?array
    {
        $config = $this->getAll();

        if (!$config['transfer_enabled']) {
            return null;
        }

        $activeBanks = $this->getActiveBankAccounts();
        
        if (empty($activeBanks)) {
            return null;
        }

        return $activeBanks;
    }

    /**
     * Check if transfer has at least one active bank
     */
    public function hasActiveBankAccount(): bool
    {
        $activeBanks = $this->getActiveBankAccounts();
        return !empty($activeBanks);
    }

    /**
     * Get QRIS image URL
     */
    public function getQrisImageUrl(): ?string
    {
        $config = $this->getAll();

        if (!$config['qris_enabled'] || empty($config['qris_image'])) {
            return null;
        }

        $imagePath = $config['qris_image'];

        if (Storage::disk('public')->exists($imagePath)) {
            return Storage::disk('public')->url($imagePath);
        }

        return null;
    }

    /**
     * Save payment configuration
     */
    public function saveConfiguration(array $data): void
    {
        // Save cash enabled
        if (isset($data['cash_enabled'])) {
            Setting::set(self::KEY_CASH_ENABLED, $data['cash_enabled'] ? '1' : '0');
        }

        // Save transfer enabled
        if (isset($data['transfer_enabled'])) {
            Setting::set(self::KEY_TRANSFER_ENABLED, $data['transfer_enabled'] ? '1' : '0');
        }

        // Save banks array if provided
        if (isset($data['transfer_banks'])) {
            Setting::set(self::KEY_TRANSFER_BANKS, json_encode($data['transfer_banks']));
        }

        // Save QRIS enabled and image
        if (isset($data['qris_enabled'])) {
            Setting::set(self::KEY_QRIS_ENABLED, $data['qris_enabled'] ? '1' : '0');
        }

        if (isset($data['qris_image'])) {
            Setting::set(self::KEY_QRIS_IMAGE, $data['qris_image']);
        }

        // Invalidate cache immediately after save
        $this->clearCache();
    }

    /**
     * Clear payment configuration cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Convert string value to boolean
     */
    protected function toBool(string $value): bool
    {
        return $value === '1' || $value === 'true';
    }
}
