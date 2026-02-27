<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the trait to hook into Eloquent events.
     */
    protected static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            static::logAudit('create', $model, null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $oldValues = array_intersect_key($model->getOriginal(), $model->getDirty());
            $newValues = $model->getDirty();

            // Only log if there are actual changes in audited fields
            if (!empty($newValues)) {
                static::logAudit('update', $model, $oldValues, $newValues);
            }
        });

        static::deleted(function (Model $model) {
            static::logAudit('delete', $model, $model->getAttributes(), null);
        });
    }

    /**
     * Record the audit log entry.
     */
    protected static function logAudit(string $action, Model $model, ?array $old = null, ?array $new = null): void
    {
        // Don't log if not authenticated (e.g., in console/seeder unless explicitly desired)
        if (!Auth::check() && !app()->runningInConsole()) {
            return;
        }

        // Filter sensitive fields
        $filter = ['password', 'remember_token', 'otp_code'];
        if ($old) $old = array_diff_key($old, array_flip($filter));
        if ($new) $new = array_diff_key($new, array_flip($filter));

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model' => get_class($model),
            'model_id' => $model->id,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
