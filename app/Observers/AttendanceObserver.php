<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Services\StoreStatusService;
use Illuminate\Support\Facades\Log;

class AttendanceObserver
{
    public function __construct(
        protected StoreStatusService $storeStatusService
    ) {}

    /**
     * Handle the Attendance "created" event.
     * Triggered when a staff member checks in.
     */
    public function created(Attendance $attendance): void
    {
        Log::info('Attendance CHECK-IN', [
            'user' => $attendance->user->name,
            'time' => $attendance->check_in?->toDateTimeString(),
            'date' => $attendance->date?->toDateString(),
        ]);

        $this->storeStatusService->forceUpdate();
    }

    /**
     * Handle the Attendance "updated" event.
     * Triggered when a staff member checks out.
     */
    public function updated(Attendance $attendance): void
    {
        if ($attendance->wasChanged('check_out') && $attendance->check_out) {
            Log::info('Attendance CHECK-OUT', [
                'user' => $attendance->user->name,
                'time' => $attendance->check_out->toDateTimeString(),
                'date' => $attendance->date?->toDateString(),
            ]);

            $this->storeStatusService->forceUpdate();
        }
    }
}
