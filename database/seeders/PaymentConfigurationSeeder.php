<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\PaymentConfigurationService;
use Illuminate\Database\Seeder;

class PaymentConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds default payment configuration:
     * - Cash: enabled by default
     * - Transfer: disabled by default
     * - QRIS: disabled by default
     */
    public function run(): void
    {
        $settings = [
            // Cash enabled by default (Requirement 4.1)
            [
                'key' => PaymentConfigurationService::KEY_CASH_ENABLED,
                'value' => '1',
            ],
            // Transfer disabled by default
            [
                'key' => PaymentConfigurationService::KEY_TRANSFER_ENABLED,
                'value' => '0',
            ],
            // QRIS disabled by default
            [
                'key' => PaymentConfigurationService::KEY_QRIS_ENABLED,
                'value' => '0',
            ],
            // Bank accounts (empty array by default)
            [
                'key' => PaymentConfigurationService::KEY_TRANSFER_BANKS,
                'value' => '[]',
            ],
            // QRIS image (empty by default)
            [
                'key' => PaymentConfigurationService::KEY_QRIS_IMAGE,
                'value' => '',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
