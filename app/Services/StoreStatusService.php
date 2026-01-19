<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\StoreSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StoreStatusService
{
    protected DateTimeSettingsService $dateTimeService;

    public function __construct(DateTimeSettingsService $dateTimeService)
    {
        $this->dateTimeService = $dateTimeService;
    }

    /**
     * Get current time using system timezone
     */
    protected function now(): Carbon
    {
        return $this->dateTimeService->now();
    }

    /**
     * Get today's date using system timezone
     */
    protected function today(): Carbon
    {
        return $this->now()->startOfDay();
    }

    /**
     * Update store status based on priority logic:
     * 1. Manual Mode (highest priority)
     * 2. Manual Close with Duration
     * 3. Manual Open Override
     * 4. Auto Mode (default)
     */
    public function updateStoreStatus(): void
    {
        $setting = StoreSetting::firstOrCreate(
            ['id' => 1],
            [
                'is_open' => false,
                'auto_status' => true,
                'manual_mode' => false,
                'operating_hours' => $this->getDefaultOperatingHours(),
            ]
        );

        // Priority 1: Manual Mode - Admin has full control
        if ($setting->manual_mode) {
            $shouldBeOpen = $setting->manual_is_open;
            $reason = $setting->manual_close_reason ?? 'Mode manual aktif';
            
            if ($shouldBeOpen && !$setting->is_open) {
                $this->openStore($setting, $reason);
            } elseif (!$shouldBeOpen && $setting->is_open) {
                $this->closeStore($setting, $reason);
            }
            return;
        }

        // Priority 2: Manual Close with Duration - Temporary close
        if ($setting->manual_close_until && $setting->manual_close_until->isFuture()) {
            if ($setting->is_open) {
                $reason = $setting->manual_close_reason ?? 'Tutup sementara';
                $this->closeStore($setting, $reason);
            }
            return;
        }

        // Priority 2.1: Manual Close Expired - Reset to auto mode
        if ($setting->manual_close_until && $setting->manual_close_until->isPast()) {
            $setting->update([
                'manual_close_until' => null,
                'manual_close_reason' => null,
            ]);
        }

        // Priority 3 & 4: Auto Mode with optional Manual Open Override
        $now = $this->now();
        $dayOfWeek = strtolower($now->format('l'));
        
        // Check operating day (Monday-Thursday only)
        $operatingHours = $setting->operating_hours ?? $this->getDefaultOperatingHours();
        $todaySchedule = $operatingHours[$dayOfWeek] ?? null;
        
        // If not an operating day and no manual override
        if (!$todaySchedule || !$todaySchedule['is_open']) {
            if (!$setting->manual_open_override) {
                if ($setting->is_open) {
                    $this->closeStore($setting, 'Koperasi hanya buka Senin - Kamis');
                }
                return;
            }
        }

        // Check operating hours
        if ($todaySchedule && $todaySchedule['is_open']) {
            $openTime = Carbon::parse($todaySchedule['open'], $this->dateTimeService->getTimezone());
            $closeTime = Carbon::parse($todaySchedule['close'], $this->dateTimeService->getTimezone());
            
            $isWithinHours = $now->between($openTime, $closeTime);
            
            // If outside operating hours and no manual override
            if (!$isWithinHours && !$setting->manual_open_override) {
                if ($setting->is_open) {
                    $this->closeStore($setting, 'Di luar jam operasional');
                }
                return;
            }
        }

        // Check active attendances
        $activeAttendances = $this->getActiveAttendances();
        
        if ($activeAttendances->isNotEmpty()) {
            $attendeeNames = $activeAttendances->pluck('user.name')->toArray();
            $reason = 'Dijaga oleh: ' . implode(', ', $attendeeNames);
            
            if (!$setting->is_open) {
                $this->openStore($setting, $reason);
            } elseif ($setting->status_reason !== $reason) {
                // Update reason if attendees changed
                $setting->update(['status_reason' => $reason]);
            }
        } else {
            if ($setting->is_open) {
                $this->closeStore($setting, 'Tidak ada pengurus yang bertugas');
            }
        }
    }

    /**
     * Get active attendances (checked in but not checked out today)
     */
    protected function getActiveAttendances(): Collection
    {
        return Attendance::query()
            ->whereDate('date', $this->today())
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->with('user:id,name')
            ->get();
    }

    /**
     * Open the store
     */
    protected function openStore(StoreSetting $setting, string $reason): void
    {
        $setting->update([
            'is_open' => true,
            'status_reason' => $reason,
            'last_status_change' => $this->now(),
        ]);

        Cache::forget('store_status');
        
        Log::channel('store')->info('Store OPENED', [
            'reason' => $reason,
            'timestamp' => $this->now()->toDateTimeString(),
        ]);

        // Dispatch event for real-time updates
        event(new \App\Events\StoreStatusChanged(
            true,
            $reason,
            $this->getActiveAttendances()->pluck('user.name')->toArray()
        ));
    }

    /**
     * Close the store
     */
    protected function closeStore(StoreSetting $setting, string $reason): void
    {
        $setting->update([
            'is_open' => false,
            'status_reason' => $reason,
            'last_status_change' => $this->now(),
        ]);

        Cache::forget('store_status');
        
        Log::channel('store')->info('Store CLOSED', [
            'reason' => $reason,
            'timestamp' => $this->now()->toDateTimeString(),
        ]);

        // Dispatch event for real-time updates
        event(new \App\Events\StoreStatusChanged(
            false,
            $reason,
            []
        ));
    }

    /**
     * Get current store status with attendees
     */
    public function getStatus(): array
    {
        return Cache::remember('store_status', 30, function () {
            $setting = StoreSetting::first();
            
            if (!$setting) {
                return [
                    'is_open' => false,
                    'reason' => 'Sistem belum dikonfigurasi',
                    'attendees' => [],
                    'mode' => 'auto',
                    'next_open_time' => null,
                ];
            }

            $attendees = [];
            if ($setting->is_open) {
                $attendees = $this->getActiveAttendances()
                    ->pluck('user.name')
                    ->toArray();
            }

            $mode = 'auto';
            if ($setting->manual_mode) {
                $mode = 'manual';
            } elseif ($setting->manual_close_until && $setting->manual_close_until->isFuture()) {
                $mode = 'temporary_close';
            } elseif ($setting->manual_open_override) {
                $mode = 'override';
            }

            return [
                'is_open' => $setting->is_open,
                'reason' => $this->getStatusReason($setting),
                'attendees' => $attendees,
                'mode' => $mode,
                'next_open_time' => $this->getNextOpenTime($setting),
            ];
        });
    }

    /**
     * Get human-readable status reason
     */
    protected function getStatusReason(StoreSetting $setting): string
    {
        if ($setting->status_reason) {
            return $setting->status_reason;
        }

        if ($setting->is_open) {
            return 'Koperasi sedang buka';
        }

        return 'Koperasi sedang tutup';
    }

    /**
     * Calculate next opening time
     */
    protected function getNextOpenTime(StoreSetting $setting): ?string
    {
        if ($setting->is_open) {
            return null;
        }

        $now = $this->now();
        $operatingHours = $setting->operating_hours ?? $this->getDefaultOperatingHours();
        $locale = $this->dateTimeService->getLocale();

        // If temporarily closed, return when it expires
        if ($setting->manual_close_until && $setting->manual_close_until->isFuture()) {
            return $setting->manual_close_until->locale($locale)->isoFormat('dddd, D MMM YYYY [pukul] HH:mm');
        }

        // If in manual mode, no predictable next open time
        if ($setting->manual_mode) {
            return null;
        }

        $dayOfWeek = strtolower($now->format('l'));
        $todaySchedule = $operatingHours[$dayOfWeek] ?? null;

        // If today is an operating day
        if ($todaySchedule && $todaySchedule['is_open']) {
            $openTime = Carbon::parse($todaySchedule['open'], $this->dateTimeService->getTimezone());
            $closeTime = Carbon::parse($todaySchedule['close'], $this->dateTimeService->getTimezone());

            // If before opening time today
            if ($now->lt($openTime)) {
                return $openTime->locale($locale)->isoFormat('dddd, D MMM YYYY [pukul] HH:mm');
            }

            // If after closing time today, find next operating day
        }

        // Find next operating day
        $daysToCheck = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $currentDayIndex = array_search($dayOfWeek, $daysToCheck);

        for ($i = 1; $i <= 7; $i++) {
            $nextDayIndex = ($currentDayIndex + $i) % 7;
            $nextDay = $daysToCheck[$nextDayIndex];
            $nextSchedule = $operatingHours[$nextDay] ?? null;

            if ($nextSchedule && $nextSchedule['is_open']) {
                $nextDate = $now->copy()->addDays($i);
                $nextOpenTime = Carbon::parse($nextSchedule['open'], $this->dateTimeService->getTimezone())->setDateFrom($nextDate);
                return $nextOpenTime->locale($locale)->isoFormat('dddd, D MMM YYYY [pukul] HH:mm');
            }
        }

        return null;
    }

    /**
     * Force immediate status update
     */
    public function forceUpdate(): void
    {
        Cache::forget('store_status');
        $this->updateStoreStatus();
    }

    /**
     * Manually close the store for a specified duration
     * 
     * @param string $reason Reason for closing
     * @param Carbon|null $until When the temporary close expires (null = indefinite)
     */
    public function manualClose(string $reason, ?Carbon $until = null): void
    {
        $setting = StoreSetting::firstOrCreate(
            ['id' => 1],
            [
                'is_open' => false,
                'auto_status' => true,
                'manual_mode' => false,
                'operating_hours' => $this->getDefaultOperatingHours(),
            ]
        );

        $setting->update([
            'manual_close_reason' => $reason,
            'manual_close_until' => $until,
            'manual_set_by' => auth()->id(),
            'manual_set_at' => $this->now(),
        ]);

        Log::channel('store')->info('Manual close activated', [
            'admin' => auth()->user()?->name ?? 'System',
            'reason' => $reason,
            'until' => $until?->toDateTimeString() ?? 'indefinite',
            'timestamp' => $this->now()->toDateTimeString(),
        ]);

        $this->forceUpdate();
    }

    /**
     * Enable or disable manual open override
     * Allows opening outside normal operating days/hours if staff attendance exists
     * 
     * @param bool $enable True to enable override, false to disable
     */
    public function manualOpenOverride(bool $enable): void
    {
        $setting = StoreSetting::firstOrCreate(
            ['id' => 1],
            [
                'is_open' => false,
                'auto_status' => true,
                'manual_mode' => false,
                'operating_hours' => $this->getDefaultOperatingHours(),
            ]
        );

        $setting->update([
            'manual_open_override' => $enable,
            'manual_set_by' => auth()->id(),
            'manual_set_at' => $this->now(),
        ]);

        Log::channel('store')->info('Manual open override ' . ($enable ? 'enabled' : 'disabled'), [
            'admin' => auth()->user()?->name ?? 'System',
            'timestamp' => $this->now()->toDateTimeString(),
        ]);

        $this->forceUpdate();
    }

    /**
     * Toggle manual mode for full admin control
     * In manual mode, status is completely controlled by admin, ignoring attendance
     * 
     * @param bool $isOpen The desired status (true = open, false = closed)
     * @param string|null $reason Optional reason for the status
     */
    public function toggleManualMode(bool $isOpen, ?string $reason = null): void
    {
        $setting = StoreSetting::firstOrCreate(
            ['id' => 1],
            [
                'is_open' => false,
                'auto_status' => true,
                'manual_mode' => false,
                'operating_hours' => $this->getDefaultOperatingHours(),
            ]
        );

        $setting->update([
            'manual_mode' => true,
            'manual_is_open' => $isOpen,
            'manual_close_reason' => $reason,
            'manual_set_by' => auth()->id(),
            'manual_set_at' => $this->now(),
        ]);

        Log::channel('store')->info('Manual mode activated', [
            'admin' => auth()->user()?->name ?? 'System',
            'status' => $isOpen ? 'OPEN' : 'CLOSED',
            'reason' => $reason ?? 'No reason provided',
            'timestamp' => $this->now()->toDateTimeString(),
        ]);

        $this->forceUpdate();
    }

    /**
     * Return to automatic mode, clearing all manual settings
     * Status will be recalculated based on attendance and operating hours
     */
    public function backToAutoMode(): void
    {
        $setting = StoreSetting::firstOrCreate(
            ['id' => 1],
            [
                'is_open' => false,
                'auto_status' => true,
                'manual_mode' => false,
                'operating_hours' => $this->getDefaultOperatingHours(),
            ]
        );

        $setting->update([
            'manual_mode' => false,
            'manual_is_open' => false,
            'manual_close_reason' => null,
            'manual_close_until' => null,
            'manual_open_override' => false,
            'manual_set_by' => auth()->id(),
            'manual_set_at' => $this->now(),
        ]);

        Log::channel('store')->info('Returned to auto mode', [
            'admin' => auth()->user()?->name ?? 'System',
            'timestamp' => $this->now()->toDateTimeString(),
        ]);

        $this->forceUpdate();
    }

    /**
     * Get default operating hours
     */
    protected function getDefaultOperatingHours(): array
    {
        return [
            'monday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
            'tuesday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
            'wednesday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
            'thursday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
            'friday' => ['open' => null, 'close' => null, 'is_open' => false],
            'saturday' => ['open' => null, 'close' => null, 'is_open' => false],
            'sunday' => ['open' => null, 'close' => null, 'is_open' => false],
        ];
    }
}
