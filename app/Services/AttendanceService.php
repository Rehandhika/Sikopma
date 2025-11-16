<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ScheduleAssignment;
use App\Models\Penalty;
use App\Repositories\AttendanceRepository;
use App\Exceptions\GeofenceException;
use App\Exceptions\BusinessException;
use App\Exceptions\ScheduleConflictException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    protected AttendanceRepository $repository;

    public function __construct(AttendanceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Process check-in with validation and penalty calculation
     *
     * @param int $userId
     * @param int $scheduleAssignmentId
     * @param float $latitude
     * @param float $longitude
     * @param string|null $notes
     * @return Attendance
     * @throws \Exception
     */
    public function checkIn(int $userId, int $scheduleAssignmentId, float $latitude, float $longitude, ?string $notes = null): Attendance
    {
        try {
            // Validate schedule exists
            $schedule = ScheduleAssignment::findOrFail($scheduleAssignmentId);
            
            if ($schedule->user_id !== $userId) {
                Log::warning('Unauthorized check-in attempt', [
                    'user_id' => $userId,
                    'schedule_id' => $scheduleAssignmentId,
                    'schedule_user_id' => $schedule->user_id
                ]);
                throw new BusinessException('Jadwal tidak sesuai dengan user.', 'UNAUTHORIZED_SCHEDULE');
            }

            // Check if already checked in
            $existing = Attendance::where('user_id', $userId)
                ->where('schedule_assignment_id', $scheduleAssignmentId)
                ->first();

            if ($existing && $existing->check_in) {
                throw new BusinessException('Anda sudah check-in untuk jadwal ini.', 'ALREADY_CHECKED_IN');
            }

            // Validate geofence
            if (config('sikopma.attendance.require_geolocation', true)) {
                if (!$this->isWithinGeofence($latitude, $longitude)) {
                    throw new GeofenceException('Lokasi Anda berada di luar area yang diizinkan untuk check-in.');
                }
            }

            $checkInTime = now();
            $status = $this->determineStatus($checkInTime, $schedule);
            
            // Create attendance record within transaction
            return DB::transaction(function () use ($userId, $scheduleAssignmentId, $latitude, $longitude, $notes, $checkInTime, $status, $schedule) {
                $attendance = $this->repository->create([
                    'user_id' => $userId,
                    'schedule_assignment_id' => $scheduleAssignmentId,
                    'date' => today(),
                    'check_in' => $checkInTime,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'status' => $status,
                    'notes' => $notes,
                ]);

                // Apply penalty if late
                if ($status === 'late') {
                    $this->applyLatePenalty($userId, $attendance, $schedule);
                }

                // Log audit
                log_audit('check_in', $attendance);

                Log::info('User checked in successfully', [
                    'user_id' => $userId,
                    'attendance_id' => $attendance->id,
                    'status' => $status,
                    'location' => ['lat' => $latitude, 'lng' => $longitude]
                ]);

                return $attendance;
            });

        } catch (BusinessException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Check-in failed', [
                'user_id' => $userId,
                'schedule_assignment_id' => $scheduleAssignmentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new BusinessException('Terjadi kesalahan saat melakukan check-in. Silakan coba lagi.', 'CHECK_IN_FAILED');
        }
    }

    /**
     * Process check-out
     *
     * @param int $attendanceId
     * @param string|null $notes
     * @return Attendance
     * @throws \Exception
     */
    public function checkOut(int $attendanceId, ?string $notes = null): Attendance
    {
        $attendance = Attendance::findOrFail($attendanceId);

        if (!$attendance->check_in) {
            throw new \Exception('Belum check-in.');
        }

        if ($attendance->check_out) {
            throw new \Exception('Sudah check-out.');
        }

        $attendance->update([
            'check_out' => now(),
            'notes' => $notes ?? $attendance->notes,
        ]);

        // Log audit
        log_audit('check_out', $attendance);

        return $attendance->fresh();
    }

    /**
     * Determine attendance status based on check-in time
     *
     * @param Carbon $checkInTime
     * @param ScheduleAssignment $schedule
     * @return string
     */
    protected function determineStatus(Carbon $checkInTime, ScheduleAssignment $schedule): string
    {
        $scheduleStart = Carbon::parse($schedule->date . ' ' . $schedule->time_start);
        $lateThreshold = config('sikopma.late_threshold_minutes', 15);
        
        $minutesLate = $checkInTime->diffInMinutes($scheduleStart, false);

        if ($minutesLate > $lateThreshold) {
            return 'late';
        }

        return 'present';
    }

    /**
     * Apply penalty for late attendance
     *
     * @param int $userId
     * @param Attendance $attendance
     * @param ScheduleAssignment $schedule
     * @return void
     */
    protected function applyLatePenalty(int $userId, Attendance $attendance, ScheduleAssignment $schedule): void
    {
        $scheduleStart = Carbon::parse($schedule->date . ' ' . $schedule->time_start);
        $minutesLate = $attendance->check_in->diffInMinutes($scheduleStart);

        // Calculate penalty points (e.g., 1 point per 15 minutes late)
        $penaltyPoints = ceil($minutesLate / 15) * 5;

        Penalty::create([
            'user_id' => $userId,
            'type' => 'late',
            'points' => $penaltyPoints,
            'reason' => "Terlambat {$minutesLate} menit pada " . $attendance->check_in->format('d/m/Y H:i'),
            'date' => today(),
            'status' => 'active',
        ]);
    }

    /**
     * Mark absent for users who didn't check in
     *
     * @param Carbon $date
     * @return int Number of users marked absent
     */
    public function markAbsent(Carbon $date): int
    {
        $scheduledUsers = ScheduleAssignment::where('date', $date)
            ->pluck('user_id', 'id');

        $checkedInUsers = Attendance::whereDate('check_in', $date)
            ->pluck('user_id');

        $absentUserIds = $scheduledUsers->keys()->diff($checkedInUsers);

        $count = 0;
        foreach ($absentUserIds as $scheduleId) {
            $userId = $scheduledUsers[$scheduleId];
            
            Attendance::create([
                'user_id' => $userId,
                'schedule_assignment_id' => $scheduleId,
                'date' => $date,
                'status' => 'absent',
            ]);

            // Apply absent penalty
            Penalty::create([
                'user_id' => $userId,
                'type' => 'absent',
                'points' => 20, // Higher penalty for absence
                'reason' => 'Tidak hadir pada ' . $date->format('d/m/Y'),
                'date' => $date,
                'status' => 'active',
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * Check if coordinates are within geofence
     */
    private function isWithinGeofence(float $latitude, float $longitude): bool
    {
        $allowedLat = config('sikopma.geofence.latitude');
        $allowedLng = config('sikopma.geofence.longitude');
        $radius = config('sikopma.geofence.radius_meters', 100);
        
        // Haversine formula to calculate distance
        $earthRadius = 6371000; // meters
        
        $latFrom = deg2rad($allowedLat);
        $lonFrom = deg2rad($allowedLng);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        $distance = $angle * $earthRadius;
        
        return $distance <= $radius;
    }

    /**
     * Get attendance summary for a user
     *
     * @param int $userId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array
     */
    public function getUserSummary(int $userId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        return $this->repository->getUserStats($userId, $startDate, $endDate);
    }

    /**
     * Calculate late minutes with grace period
     */
    protected function calculateLateMinutes(\Carbon\Carbon $checkInTime, ScheduleAssignment $schedule): int
    {
        $scheduleStartTime = $this->getScheduleStartTime($schedule);
        $gracePeriod = config('sikopma.penalty.grace_period_minutes', 5);
        
        $effectiveStartTime = $scheduleStartTime->copy()->addMinutes($gracePeriod);
        
        if ($checkInTime->lte($effectiveStartTime)) {
            return 0;
        }
        
        return $checkInTime->diffInMinutes($effectiveStartTime);
    }

    /**
     * Calculate penalty amount based on late minutes
     */
    protected function calculatePenaltyAmount(int $lateMinutes): int
    {
        $baseAmount = config('sikopma.penalty.base_amount', 5000);
        $incrementAmount = config('sikopma.penalty.increment_amount', 1000);
        $incrementInterval = config('sikopma.penalty.increment_interval_minutes', 15);
        
        if ($lateMinutes <= $incrementInterval) {
            return $baseAmount;
        }
        
        $additionalIntervals = ceil($lateMinutes / $incrementInterval) - 1;
        return $baseAmount + ($additionalIntervals * $incrementAmount);
    }

    /**
     * Determine penalty type based on severity
     */
    protected function determinePenaltyType(int $lateMinutes): string
    {
        $thresholds = config('sikopma.penalty.thresholds', [
            'minor' => 15,
            'moderate' => 30,
            'major' => 60,
        ]);

        if ($lateMinutes <= $thresholds['minor']) {
            return 'minor';
        } elseif ($lateMinutes <= $thresholds['moderate']) {
            return 'moderate';
        } elseif ($lateMinutes <= $thresholds['major']) {
            return 'major';
        } else {
            return 'severe';
        }
    }

    /**
     * Get schedule start time
     */
    protected function getScheduleStartTime(ScheduleAssignment $schedule): \Carbon\Carbon
    {
        $sessionTimes = [
            1 => '08:00',
            2 => '12:00',
            3 => '16:00',
        ];

        $timeString = $sessionTimes[$schedule->session] ?? '08:00';
        return $schedule->date->copy()->setTimeFromFormat('H:i', $timeString);
    }

    /**
     * Get attendance analytics
     */
    public function getAttendanceAnalytics(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, ?int $userId = null): array
    {
        $query = $this->repository->query()
            ->whereBetween('date', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $attendances = $query->get();

        return [
            'total_days' => $startDate->diffInDays($endDate) + 1,
            'total_attendances' => $attendances->count(),
            'present_count' => $attendances->where('status', 'present')->count(),
            'late_count' => $attendances->where('status', 'late')->count(),
            'absent_count' => $attendances->where('status', 'absent')->count(),
            'attendance_rate' => $attendances->count() > 0 
                ? round(($attendances->whereIn('status', ['present', 'late'])->count() / $attendances->count()) * 100, 2)
                : 0,
            'total_penalties' => $attendances->sum(function ($attendance) {
                return $attendance->penalties()->sum('amount');
            }),
            'average_late_minutes' => $attendances->where('status', 'late')->avg('late_minutes') ?? 0,
        ];
    }

    /**
     * Validate photo proof
     */
    public function validatePhotoProof(string $photoPath): bool
    {
        try {
            if (!\Storage::disk('public')->exists($photoPath)) {
                return false;
            }

            $fileInfo = pathinfo($photoPath);
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            
            if (!in_array(strtolower($fileInfo['extension']), $allowedExtensions)) {
                return false;
            }

            $fileSize = \Storage::disk('public')->size($photoPath);
            $maxSize = 2 * 1024 * 1024; // 2MB

            if ($fileSize > $maxSize) {
                return false;
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to validate photo proof', [
                'photo_path' => $photoPath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
