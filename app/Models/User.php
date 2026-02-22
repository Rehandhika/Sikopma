<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'nim',
        'password',
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
        return $this->hasMany(SwapRequest::class, 'user_id');
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

    public function students()
    {
        return $this->hasMany(Student::class, 'nim', 'nim');
    }

    public function createdBanners()
    {
        return $this->hasMany(Banner::class, 'created_by');
    }

    public function createdNews()
    {
        return $this->hasMany(News::class, 'created_by');
    }

    public function reviewedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'reviewed_by');
    }

    public function reviewedPenalties()
    {
        return $this->hasMany(Penalty::class, 'reviewed_by');
    }

    public function createdScheduleTemplates()
    {
        return $this->hasMany(ScheduleTemplate::class, 'created_by');
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Scope to eager load common relationships.
     */
    public function scopeWithCommonRelations($query)
    {
        return $query->with([
            'availabilities',
            'scheduleAssignments',
            'notifications' => fn($q) => $q->whereNull('read_at')->limit(10),
        ]);
    }

    /**
     * Scope to eager load all relationships for detailed view.
     */
    public function scopeWithAllRelations($query)
    {
        return $query->with([
            'attendances',
            'sales',
            'availabilities',
            'scheduleAssignments',
            'leaveRequests',
            'penalties',
            'notifications',
            'createdBanners',
            'createdNews',
            'reviewedLeaveRequests',
            'reviewedPenalties',
        ]);
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
        return $this->isActive() && ! $this->trashed();
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
     * Check if user is a coordinator
     */
    public function isKoordinator(): bool
    {
        return str_starts_with($this->getPrimaryRole() ?? '', 'Koordinator');
    }

    /**
     * Check if user is a treasurer (bendahara)
     */
    public function isBendahara(): bool
    {
        return str_starts_with($this->getPrimaryRole() ?? '', 'Bendahara');
    }

    /**
     * Check if user is in leadership (pimpinan)
     */
    public function isPimpinan(): bool
    {
        return in_array($this->getPrimaryRole(), ['Ketua', 'Wakil Ketua', 'Super Admin']);
    }

    /**
     * Check if user is secretary
     */
    public function isSekretaris(): bool
    {
        return $this->getPrimaryRole() === 'Sekretaris';
    }
}
