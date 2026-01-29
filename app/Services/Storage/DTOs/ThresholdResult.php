<?php

namespace App\Services\Storage\DTOs;

/**
 * DTO untuk hasil pengecekan threshold storage.
 */
class ThresholdResult
{
    public const STATUS_OK = 'ok';

    public const STATUS_WARNING = 'warning';

    public const STATUS_CRITICAL = 'critical';

    public function __construct(
        public readonly string $status,
        public readonly float $usagePercentage,
        public readonly float $warningThreshold,
        public readonly float $criticalThreshold,
        public readonly ?string $message = null,
    ) {}

    /**
     * Check if status is OK.
     */
    public function isOk(): bool
    {
        return $this->status === self::STATUS_OK;
    }

    /**
     * Check if status is warning.
     */
    public function isWarning(): bool
    {
        return $this->status === self::STATUS_WARNING;
    }

    /**
     * Check if status is critical.
     */
    public function isCritical(): bool
    {
        return $this->status === self::STATUS_CRITICAL;
    }

    /**
     * Get status label in Indonesian.
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_OK => 'Normal',
            self::STATUS_WARNING => 'Peringatan',
            self::STATUS_CRITICAL => 'Kritis',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_OK => 'green',
            self::STATUS_WARNING => 'yellow',
            self::STATUS_CRITICAL => 'red',
            default => 'gray',
        };
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'usage_percentage' => $this->usagePercentage,
            'warning_threshold' => $this->warningThreshold,
            'critical_threshold' => $this->criticalThreshold,
            'message' => $this->message,
        ];
    }

    /**
     * Create OK result.
     */
    public static function ok(float $usagePercentage, float $warningThreshold, float $criticalThreshold): self
    {
        return new self(
            status: self::STATUS_OK,
            usagePercentage: $usagePercentage,
            warningThreshold: $warningThreshold,
            criticalThreshold: $criticalThreshold,
            message: 'Penggunaan storage dalam batas normal.'
        );
    }

    /**
     * Create warning result.
     */
    public static function warning(float $usagePercentage, float $warningThreshold, float $criticalThreshold): self
    {
        return new self(
            status: self::STATUS_WARNING,
            usagePercentage: $usagePercentage,
            warningThreshold: $warningThreshold,
            criticalThreshold: $criticalThreshold,
            message: sprintf(
                'Peringatan: Penggunaan storage mencapai %.1f%%, melebihi batas peringatan %.1f%%.',
                $usagePercentage,
                $warningThreshold
            )
        );
    }

    /**
     * Create critical result.
     */
    public static function critical(float $usagePercentage, float $warningThreshold, float $criticalThreshold): self
    {
        return new self(
            status: self::STATUS_CRITICAL,
            usagePercentage: $usagePercentage,
            warningThreshold: $warningThreshold,
            criticalThreshold: $criticalThreshold,
            message: sprintf(
                'KRITIS: Penggunaan storage mencapai %.1f%%, melebihi batas kritis %.1f%%! Segera lakukan pembersihan.',
                $usagePercentage,
                $criticalThreshold
            )
        );
    }
}
