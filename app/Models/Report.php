<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'start_date',
        'end_date',
        'data',
        'file_path',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who generated this report
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for pending reports
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed reports
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed reports
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for specific report type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for sales reports
     */
    public function scopeSales($query)
    {
        return $query->where('type', 'sales');
    }

    /**
     * Scope for purchase reports
     */
    public function scopePurchases($query)
    {
        return $query->where('type', 'purchases');
    }

    /**
     * Scope for inventory reports
     */
    public function scopeInventory($query)
    {
        return $query->where('type', 'inventory');
    }

    /**
     * Scope for financial reports
     */
    public function scopeFinancial($query)
    {
        return $query->where('type', 'financial');
    }

    /**
     * Check if report is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if report is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if report has failed
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark report as completed
     */
    public function markAsCompleted(?string $filePath = null): void
    {
        $this->update([
            'status' => 'completed',
            'file_path' => $filePath ?? $this->file_path,
        ]);
    }

    /**
     * Mark report as failed
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }
}
