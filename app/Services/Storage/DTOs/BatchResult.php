<?php

namespace App\Services\Storage\DTOs;

/**
 * DTO untuk hasil batch processing.
 */
class BatchResult
{
    public function __construct(
        public readonly int $total,
        public readonly int $success,
        public readonly int $failed,
        public readonly array $results,
        public readonly array $errors,
    ) {}

    /**
     * Create from processing results.
     *
     * @param array $results Array of ['path' => string, 'success' => bool, 'error' => ?string]
     */
    public static function fromResults(array $results): self
    {
        $success = 0;
        $failed = 0;
        $errors = [];
        $processedResults = [];

        foreach ($results as $result) {
            if ($result['success']) {
                $success++;
            } else {
                $failed++;
                if (isset($result['error'])) {
                    $errors[$result['path']] = $result['error'];
                }
            }
            $processedResults[] = $result;
        }

        return new self(
            total: count($results),
            success: $success,
            failed: $failed,
            results: $processedResults,
            errors: $errors
        );
    }

    /**
     * Check if all items were successful.
     */
    public function isAllSuccessful(): bool
    {
        return $this->failed === 0;
    }

    /**
     * Check if any items failed.
     */
    public function hasFailures(): bool
    {
        return $this->failed > 0;
    }

    /**
     * Get success rate as percentage.
     */
    public function getSuccessRate(): float
    {
        if ($this->total === 0) {
            return 100.0;
        }

        return round(($this->success / $this->total) * 100, 2);
    }

    /**
     * Get successful paths.
     */
    public function getSuccessfulPaths(): array
    {
        return array_filter(
            array_column($this->results, 'path', 'success'),
            fn($success) => $success === true,
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Get failed paths.
     */
    public function getFailedPaths(): array
    {
        return array_keys($this->errors);
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'success' => $this->success,
            'failed' => $this->failed,
            'success_rate' => $this->getSuccessRate(),
            'results' => $this->results,
            'errors' => $this->errors,
        ];
    }
}
