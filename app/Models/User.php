<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'nim',
        'password',
        'phone',
        'address',
        'photo',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'cashier_id');
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }

    public function scheduleAssignments()
    {
        return $this->hasMany(ScheduleAssignment::class);
    }

    public function swapRequestsAsSender()
    {
        return $this->hasMany(SwapRequest::class, 'requester_id');
    }

    public function swapRequestsAsReceiver()
    {
        return $this->hasMany(SwapRequest::class, 'target_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function penalties()
    {
        return $this->hasMany(Penalty::class);
    }

    public function penaltyHistory()
    {
        return $this->hasMany(PenaltyHistory::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Check if user can login
     */
    public function canLogin(): bool
    {
        return $this->isActive() && !$this->trashed();
    }

    /**
     * Get user's primary role
     */
    public function getPrimaryRole(): ?string
    {
        return $this->getRoleNames()->first();
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Get user's dashboard route based on role
     */
    public function getDashboardRoute(): string
    {
        $role = $this->getPrimaryRole();
        
        // You can customize dashboard routes based on roles
        return match($role) {
            'super_admin' => 'dashboard',
            'ketua' => 'dashboard',
            'wakil_ketua' => 'dashboard',
            'bendahara' => 'dashboard',
            'sekretaris' => 'dashboard',
            'anggota' => 'dashboard',
            default => 'dashboard',
        };
    }
}
