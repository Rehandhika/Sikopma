<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * SHU Point Transaction Model
 *
 * Represents a transaction of SHU points (earn or redeem).
 *
 * @property int $id
 * @property int $student_id
 * @property int|null $sale_id
 * @property string $type Transaction type: 'earn', 'redeem', or 'adjust'
 * @property int $amount The purchase amount (in rupiah) that generated the points
 * @property int|null $conversion_rate The rupiah amount required to earn 1 point (formerly percentage_bps)
 *                                    e.g., value of 10000 means 1 point per Rp 10,000 purchase
 * @property int $points Number of points earned, redeemed, or adjusted
 * @property int|null $cash_amount Cash amount for redemption (in rupiah)
 * @property string|null $notes
 * @property int|null $created_by User ID who created this transaction
 * @property \Carbon\Carbon $created_at
 */
class ShuPointTransaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'sale_id',
        'type',
        'amount',
        'conversion_rate', // Stores conversion amount (rupiah per point)
        'points',
        'cash_amount',
        'notes',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'conversion_rate' => 'integer',
        'points' => 'integer',
        'cash_amount' => 'integer',
        'created_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
