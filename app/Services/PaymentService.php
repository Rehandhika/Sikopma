<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class PaymentService
{
    /**
     * Process payment based on method
     */
    public function processPayment(array $paymentData): array
    {
        try {
            switch ($paymentData['method']) {
                case 'cash':
                    return $this->processCashPayment($paymentData);
                case 'transfer':
                    return $this->processTransferPayment($paymentData);
                case 'ewallet':
                    return $this->processEWalletPayment($paymentData);
                case 'card':
                    return $this->processCardPayment($paymentData);
                default:
                    throw new BusinessException('Metode pembayaran tidak valid', 'INVALID_PAYMENT_METHOD');
            }

        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'payment_data' => $paymentData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Process cash payment
     */
    protected function processCashPayment(array $paymentData): array
    {
        if ($paymentData['amount_paid'] < $paymentData['amount']) {
            return [
                'success' => false,
                'message' => 'Pembayaran tunai tidak mencukupi',
                'reference' => null,
            ];
        }

        $reference = 'CASH_' . time() . '_' . mt_rand(1000, 9999);

        return [
            'success' => true,
            'message' => 'Pembayaran tunai berhasil',
            'reference' => $reference,
            'change' => $paymentData['amount_paid'] - $paymentData['amount'],
        ];
    }

    /**
     * Process bank transfer payment
     */
    protected function processTransferPayment(array $paymentData): array
    {
        // In real implementation, integrate with bank API
        // For now, simulate successful transfer
        $reference = 'TRF_' . time() . '_' . mt_rand(1000, 9999);

        // Simulate bank API call
        $bankResponse = $this->simulateBankTransfer([
            'amount' => $paymentData['amount'],
            'reference' => $reference,
            'customer_name' => $paymentData['customer_name'] ?? 'Customer',
        ]);

        if (!$bankResponse['success']) {
            return [
                'success' => false,
                'message' => $bankResponse['message'],
                'reference' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Transfer bank berhasil',
            'reference' => $reference,
            'bank_reference' => $bankResponse['bank_reference'],
        ];
    }

    /**
     * Process e-wallet payment
     */
    protected function processEWalletPayment(array $paymentData): array
    {
        // In real implementation, integrate with e-wallet providers (OVO, GoPay, DANA, etc.)
        $reference = 'EW_' . time() . '_' . mt_rand(1000, 9999);

        $ewalletResponse = $this->simulateEWalletPayment([
            'amount' => $paymentData['amount'],
            'reference' => $reference,
            'customer_phone' => $paymentData['customer_phone'] ?? null,
        ]);

        if (!$ewalletResponse['success']) {
            return [
                'success' => false,
                'message' => $ewalletResponse['message'],
                'reference' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Pembayaran e-wallet berhasil',
            'reference' => $reference,
            'ewallet_reference' => $ewalletResponse['transaction_id'],
        ];
    }

    /**
     * Process credit/debit card payment
     */
    protected function processCardPayment(array $paymentData): array
    {
        // In real implementation, integrate with payment gateway (Stripe, Midtrans, etc.)
        $reference = 'CARD_' . time() . '_' . mt_rand(1000, 9999);

        $cardResponse = $this->simulateCardPayment([
            'amount' => $paymentData['amount'],
            'reference' => $reference,
            'customer_name' => $paymentData['customer_name'] ?? 'Customer',
        ]);

        if (!$cardResponse['success']) {
            return [
                'success' => false,
                'message' => $cardResponse['message'],
                'reference' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Pembayaran kartu berhasil',
            'reference' => $reference,
            'card_reference' => $cardResponse['authorization_code'],
        ];
    }

    /**
     * Simulate bank transfer API call
     */
    protected function simulateBankTransfer(array $data): array
    {
        // Simulate API delay
        usleep(500000); // 0.5 seconds

        // Simulate random success/failure (90% success rate)
        if (mt_rand(1, 100) <= 90) {
            return [
                'success' => true,
                'message' => 'Transfer berhasil diproses',
                'bank_reference' => 'BANK' . time() . mt_rand(1000, 9999),
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Transfer gagal: Saldo tidak mencukupi',
            ];
        }
    }

    /**
     * Simulate e-wallet API call
     */
    protected function simulateEWalletPayment(array $data): array
    {
        // Simulate API delay
        usleep(300000); // 0.3 seconds

        // Validate phone number for e-wallet
        if (empty($data['customer_phone'])) {
            return [
                'success' => false,
                'message' => 'Nomor telepon diperlukan untuk pembayaran e-wallet',
            ];
        }

        // Simulate random success/failure (95% success rate)
        if (mt_rand(1, 100) <= 95) {
            return [
                'success' => true,
                'message' => 'Pembayaran e-wallet berhasil',
                'transaction_id' => 'EW' . time() . mt_rand(10000, 99999),
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Pembayaran e-wallet gagal: Saldo tidak mencukupi',
            ];
        }
    }

    /**
     * Simulate card payment API call
     */
    protected function simulateCardPayment(array $data): array
    {
        // Simulate API delay
        usleep(700000); // 0.7 seconds

        // Simulate random success/failure (85% success rate)
        if (mt_rand(1, 100) <= 85) {
            return [
                'success' => true,
                'message' => 'Pembayaran kartu berhasil',
                'authorization_code' => 'AUTH' . time() . mt_rand(1000, 9999),
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Pembayaran kartu ditolak: Kartu tidak valid',
            ];
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(string $reference): array
    {
        try {
            // In real implementation, query payment gateway API
            // For now, simulate status check

            $prefix = substr($reference, 0, 3);
            
            switch ($prefix) {
                case 'CASH':
                    return [
                        'status' => 'completed',
                        'message' => 'Pembayaran tunai selesai',
                    ];
                case 'TRF':
                    return [
                        'status' => 'completed',
                        'message' => 'Transfer bank selesai',
                    ];
                case 'EW_':
                    return [
                        'status' => 'completed',
                        'message' => 'Pembayaran e-wallet selesai',
                    ];
                case 'CAR':
                    return [
                        'status' => 'completed',
                        'message' => 'Pembayaran kartu selesai',
                    ];
                default:
                    return [
                        'status' => 'unknown',
                        'message' => 'Status pembayaran tidak diketahui',
                    ];
            }

        } catch (\Exception $e) {
            Log::error('Failed to check payment status', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);
            return [
                'status' => 'error',
                'message' => 'Gagal memeriksa status pembayaran',
            ];
        }
    }

    /**
     * Process refund
     */
    public function processRefund(string $reference, float $amount, string $reason): array
    {
        try {
            // In real implementation, call payment gateway refund API
            $refundReference = 'REF_' . time() . '_' . mt_rand(1000, 9999);

            // Simulate refund processing
            usleep(500000);

            // Simulate random success/failure (95% success rate)
            if (mt_rand(1, 100) <= 95) {
                Log::info('Refund processed successfully', [
                    'original_reference' => $reference,
                    'refund_reference' => $refundReference,
                    'amount' => $amount,
                    'reason' => $reason,
                ]);

                return [
                    'success' => true,
                    'message' => 'Refund berhasil diproses',
                    'refund_reference' => $refundReference,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Refund gagal: Batas waktu refund terlampaui',
                ];
            }

        } catch (\Exception $e) {
            Log::error('Refund processing failed', [
                'reference' => $reference,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Gagal memproses refund',
            ];
        }
    }

    /**
     * Get payment method limits
     */
    public function getPaymentMethodLimits(): array
    {
        return [
            'cash' => [
                'min_amount' => 0,
                'max_amount' => 999999999,
                'description' => 'Tunai',
            ],
            'transfer' => [
                'min_amount' => 1000,
                'max_amount' => 50000000,
                'description' => 'Transfer Bank',
            ],
            'ewallet' => [
                'min_amount' => 1000,
                'max_amount' => 20000000,
                'description' => 'E-Wallet',
            ],
            'card' => [
                'min_amount' => 1000,
                'max_amount' => 100000000,
                'description' => 'Kartu Kredit/Debit',
            ],
        ];
    }

    /**
     * Validate payment amount
     */
    public function validatePaymentAmount(string $method, float $amount): array
    {
        $limits = $this->getPaymentMethodLimits();
        
        if (!isset($limits[$method])) {
            return [
                'valid' => false,
                'message' => 'Metode pembayaran tidak valid',
            ];
        }

        $limit = $limits[$method];
        
        if ($amount < $limit['min_amount']) {
            return [
                'valid' => false,
                'message' => "Minimal pembayaran {$limit['min_amount']}",
            ];
        }

        if ($amount > $limit['max_amount']) {
            return [
                'valid' => false,
                'message' => "Maksimal pembayaran {$limit['max_amount']}",
            ];
        }

        return [
            'valid' => true,
            'message' => 'Jumlah pembayaran valid',
        ];
    }

    /**
     * Generate payment QR code for e-wallet
     */
    public function generateQRCode(string $method, float $amount, string $reference): array
    {
        try {
            if ($method !== 'ewallet') {
                return [
                    'success' => false,
                    'message' => 'QR code hanya tersedia untuk e-wallet',
                ];
            }

            // In real implementation, generate actual QR code
            $qrData = [
                'type' => 'payment',
                'method' => $method,
                'amount' => $amount,
                'reference' => $reference,
                'merchant' => config('app.name'),
                'timestamp' => now()->toISOString(),
            ];

            $qrString = json_encode($qrData);
            $qrCode = base64_encode($qrString); // Simulate QR code generation

            return [
                'success' => true,
                'qr_code' => $qrCode,
                'expires_at' => now()->addMinutes(15),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate QR code', [
                'method' => $method,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Gagal generate QR code',
            ];
        }
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, ?string $method = null): array
    {
        try {
            // In real implementation, query payment gateway API
            // For now, return simulated data
            
            return [
                'transactions' => [],
                'total_amount' => 0,
                'total_count' => 0,
                'success_rate' => 0,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get transaction history', [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            return [
                'transactions' => [],
                'total_amount' => 0,
                'total_count' => 0,
                'success_rate' => 0,
            ];
        }
    }
}
