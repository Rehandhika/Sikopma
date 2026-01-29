<?php

namespace App\Services\Storage\DTOs;

/**
 * DTO untuk statistik storage.
 */
class StorageStats
{
    public function __construct(
        public readonly int $totalBytes,
        public readonly int $totalFiles,
        public readonly array $byType,
        public readonly int $availableBytes,
        public readonly float $usagePercentage,
        public readonly \DateTimeInterface $generatedAt,
    ) {}

    /**
     * Get total size in KB.
     */
    public function getTotalKB(): float
    {
        return round($this->totalBytes / 1024, 2);
    }

    /**
     * Get total size in MB.
     */
    public function getTotalMB(): float
    {
        return round($this->totalBytes / (1024 * 1024), 2);
    }

    /**
     * Get total size in GB.
     */
    public function getTotalGB(): float
    {
        return round($this->totalBytes / (1024 * 1024 * 1024), 2);
    }

    /**
     * Get available space in GB.
     */
    public function getAvailableGB(): float
    {
        return round($this->availableBytes / (1024 * 1024 * 1024), 2);
    }

    /**
     * Check if usage is above warning threshold.
     */
    public function isWarning(float $threshold = 80): bool
    {
        return $this->usagePercentage >= $threshold;
    }

    /**
     * Check if usage is above critical threshold.
     */
    public function isCritical(float $threshold = 95): bool
    {
        return $this->usagePercentage >= $threshold;
    }

    /**
     * Get stats for specific type.
     */
    public function getTypeStats(string $type): ?array
    {
        return $this->byType[$type] ?? null;
    }

    /**
     * Get type with most storage usage.
     */
    public function getLargestType(): ?string
    {
        if (empty($this->byType)) {
            return null;
        }

        $largest = null;
        $maxBytes = 0;

        foreach ($this->byType as $type => $stats) {
            if (($stats['bytes'] ?? 0) > $maxBytes) {
                $maxBytes = $stats['bytes'];
                $largest = $type;
            }
        }

        return $largest;
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'total_bytes' => $this->totalBytes,
            'total_mb' => $this->getTotalMB(),
            'total_gb' => $this->getTotalGB(),
            'total_files' => $this->totalFiles,
            'by_type' => $this->byType,
            'available_bytes' => $this->availableBytes,
            'available_gb' => $this->getAvailableGB(),
            'usage_percentage' => $this->usagePercentage,
            'generated_at' => $this->generatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
