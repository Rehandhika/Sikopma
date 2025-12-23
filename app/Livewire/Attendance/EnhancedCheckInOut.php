<?php

namespace App\Livewire\Attendance;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ScheduleAssignment;
use App\Services\AttendanceService;
use App\Http\Requests\AttendanceCheckInRequest;
use App\Exceptions\GeofenceException;
use App\Exceptions\BusinessException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EnhancedCheckInOut extends Component
{
    use WithFileUploads;

    public $latitude;
    public $longitude;
    public $locationAccuracy;
    public $photoProof;
    public $notes;
    public $currentSchedule;
    public $canCheckIn = false;
    public $canCheckOut = false;
    public $isGettingLocation = false;
    public $isCapturingPhoto = false;
    public $locationStatus = 'pending'; // pending, success, error, out_of_range
    public $photoCaptured = false;
    public $checkInDistance;
    public $maxDistance = 100; // meters
    public $cameraStream = null;
    public $showCameraModal = false;
    public $capturedPhotoData = null;

    protected $attendanceService;
    protected $rules = [
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
        'photoProof' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'notes' => 'nullable|string|max:500',
    ];

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount()
    {
        $this->loadTodaySchedule();
        $this->getCurrentLocation();
    }

    public function render()
    {
        return view('livewire.attendance.enhanced-check-in-out', [
            'currentSchedule' => $this->currentSchedule,
            'canCheckIn' => $this->canCheckIn,
            'canCheckOut' => $this->canCheckOut,
            'locationStatus' => $this->locationStatus,
            'photoCaptured' => $this->photoCaptured,
            'checkInDistance' => $this->checkInDistance,
        ])->layout('layouts.app')->title('Absensi Enhanced');
    }

    protected function loadTodaySchedule()
    {
        try {
            $this->currentSchedule = ScheduleAssignment::where('user_id', auth()->id())
                ->where('date', today())
                ->where('status', 'scheduled')
                ->with(['schedule'])
                ->first();

            if ($this->currentSchedule) {
                $this->determineCheckStatus();
            }

        } catch (\Exception $e) {
            Log::error('Failed to load today schedule', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            $this->dispatch('error', 'Gagal memuat jadwal hari ini');
        }
    }

    protected function determineCheckStatus()
    {
        if (!$this->currentSchedule) {
            return;
        }

        $now = now();
        $scheduleTime = $this->currentSchedule->date->setTimeFromFormat('H:i', $this->getScheduleStartTime());
        $endTime = $this->currentSchedule->date->setTimeFromFormat('H:i', $this->getScheduleEndTime());

        // Check if user can check in (30 minutes before schedule)
        $checkInWindowStart = $scheduleTime->copy()->subMinutes(30);
        $checkInWindowEnd = $scheduleTime->copy()->addMinutes(30);
        
        $this->canCheckIn = $now->between($checkInWindowStart, $checkInWindowEnd) && 
                           !$this->currentSchedule->attendance?->check_in;

        // Check if user can check out (after check in and before end of day)
        $this->canCheckOut = $this->currentSchedule->attendance?->check_in && 
                            !$this->currentSchedule->attendance?->check_out &&
                            $now->gt($scheduleTime);
    }

    protected function getScheduleStartTime(): string
    {
        $sessionTimes = [
            1 => '07:30',
            2 => '10:20', 
            3 => '13:30',
        ];

        return $sessionTimes[$this->currentSchedule->session] ?? '07:30';
    }

    protected function getScheduleEndTime(): string
    {
        $sessionTimes = [
            1 => '10:00',
            2 => '12:50',
            3 => '16:00',
        ];

        return $sessionTimes[$this->currentSchedule->session] ?? '10:00';
    }

    public function getCurrentLocation()
    {
        $this->isGettingLocation = true;
        $this->dispatch('requestLocation');
    }

    public function locationReceived($data)
    {
        try {
            $this->latitude = $data['latitude'];
            $this->longitude = $data['longitude'];
            $this->locationAccuracy = $data['accuracy'] ?? null;
            
            // Validate geofence
            $this->validateGeofence();
            
            $this->isGettingLocation = false;
            
            Log::info('Location received', [
                'user_id' => auth()->id(),
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'accuracy' => $this->locationAccuracy,
                'status' => $this->locationStatus,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process location', [
                'user_id' => auth()->id(),
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            $this->locationStatus = 'error';
            $this->isGettingLocation = false;
            $this->dispatch('error', 'Gagal mendapatkan lokasi Anda');
        }
    }

    protected function validateGeofence()
    {
        if (!$this->latitude || !$this->longitude) {
            $this->locationStatus = 'error';
            return;
        }

        $allowedLat = config('sikopma.geofence.latitude');
        $allowedLng = config('sikopma.geofence.longitude');
        $radius = config('sikopma.geofence.radius_meters', $this->maxDistance);
        
        // Calculate distance using Haversine formula
        $distance = $this->calculateDistance($this->latitude, $this->longitude, $allowedLat, $allowedLng);
        $this->checkInDistance = round($distance, 2);
        
        if ($distance <= $radius) {
            $this->locationStatus = 'success';
        } else {
            $this->locationStatus = 'out_of_range';
        }
    }

    protected function calculateDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371000; // meters
        
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lng2);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        return $angle * $earthRadius;
    }

    public function openCamera()
    {
        $this->showCameraModal = true;
        $this->dispatch('initCamera');
    }

    public function closeCamera()
    {
        $this->showCameraModal = false;
        $this->dispatch('stopCamera');
    }

    public function capturePhoto()
    {
        $this->isCapturingPhoto = true;
        $this->dispatch('capturePhoto');
    }

    public function photoCaptured($photoData)
    {
        try {
            $this->capturedPhotoData = $photoData;
            $this->photoCaptured = true;
            $this->isCapturingPhoto = false;
            $this->closeCamera();
            
            Log::info('Photo captured', [
                'user_id' => auth()->id(),
                'photo_size' => strlen($photoData)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to process captured photo', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            $this->isCapturingPhoto = false;
            $this->dispatch('error', 'Gagal memproses foto');
        }
    }

    public function retakePhoto()
    {
        $this->capturedPhotoData = null;
        $this->photoCaptured = false;
        $this->openCamera();
    }

    public function checkIn()
    {
        try {
            $this->validate();

            if ($this->locationStatus !== 'success') {
                throw new GeofenceException('Lokasi Anda berada di luar area yang diizinkan');
            }

            if (!$this->photoCaptured && config('sikopma.attendance.require_photo', true)) {
                $this->dispatch('error', 'Foto wajah diperlukan untuk check-in');
                return;
            }

            // Sanitize notes
            $sanitizedNotes = $this->notes ? strip_tags(trim($this->notes)) : null;

            $attendance = $this->attendanceService->checkIn(
                auth()->id(),
                $this->currentSchedule->id,
                $this->latitude,
                $this->longitude,
                $sanitizedNotes
            );

            // Store photo if captured
            if ($this->capturedPhotoData) {
                $this->storeAttendancePhoto($attendance->id, $this->capturedPhotoData);
            }

            $this->dispatch('success', 'Check-in berhasil!');
            
            // Reset form
            $this->reset(['notes', 'photoProof', 'capturedPhotoData', 'photoCaptured']);
            
            // Reload schedule
            $this->loadTodaySchedule();

            Log::info('Check-in completed', [
                'user_id' => auth()->id(),
                'attendance_id' => $attendance->id,
                'location' => ['lat' => $this->latitude, 'lng' => $this->longitude],
                'has_photo' => $this->photoCaptured,
            ]);

        } catch (GeofenceException $e) {
            $this->dispatch('error', $e->getMessage());
        } catch (BusinessException $e) {
            $this->dispatch('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Check-in failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('error', 'Terjadi kesalahan saat check-in. Silakan coba lagi.');
        }
    }

    public function checkOut()
    {
        try {
            $attendance = $this->currentSchedule->attendance;
            
            if (!$attendance) {
                throw new BusinessException('Data check-in tidak ditemukan');
            }

            $updatedAttendance = $this->attendanceService->checkOut(
                $attendance->id,
                $this->notes ? strip_tags(trim($this->notes)) : null
            );

            $this->dispatch('success', 'Check-out berhasil!');
            $this->reset(['notes']);
            $this->loadTodaySchedule();

            Log::info('Check-out completed', [
                'user_id' => auth()->id(),
                'attendance_id' => $updatedAttendance->id,
            ]);

        } catch (BusinessException $e) {
            $this->dispatch('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Check-out failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            $this->dispatch('error', 'Terjadi kesalahan saat check-out. Silakan coba lagi.');
        }
    }

    protected function storeAttendancePhoto(int $attendanceId, string $photoData): bool
    {
        try {
            // Convert base64 to image
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $photoData));
            
            $filename = 'attendance_' . $attendanceId . '_' . time() . '.jpg';
            $path = 'attendance/photos/' . date('Y/m/d') . '/' . $filename;
            
            // Store using Laravel's storage
            \Storage::disk('public')->put($path, $imageData);
            
            // Update attendance record
            \App\Models\Attendance::where('id', $attendanceId)->update([
                'photo_proof' => $path,
            ]);
            
            Log::info('Attendance photo stored', [
                'attendance_id' => $attendanceId,
                'path' => $path,
                'size' => strlen($imageData)
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to store attendance photo', [
                'attendance_id' => $attendanceId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function refreshLocation()
    {
        $this->getCurrentLocation();
    }

    public function getLocationStatusMessage(): string
    {
        switch ($this->locationStatus) {
            case 'success':
                return "Lokasi valid. Jarak: {$this->checkInDistance}m";
            case 'out_of_range':
                return "Lokasi di luar area. Jarak: {$this->checkInDistance}m (Maks: {$this->maxDistance}m)";
            case 'error':
                return "Gagal mendapatkan lokasi. Pastikan GPS aktif.";
            case 'pending':
                return "Mendapatkan lokasi...";
            default:
                return "Status lokasi tidak diketahui";
        }
    }

    public function getLocationStatusColor(): string
    {
        switch ($this->locationStatus) {
            case 'success':
                return 'text-green-600';
            case 'out_of_range':
                return 'text-red-600';
            case 'error':
                return 'text-red-600';
            case 'pending':
                return 'text-yellow-600';
            default:
                return 'text-gray-600';
        }
    }

    public function getScheduleStatus()
    {
        if (!$this->currentSchedule) {
            return [
                'status' => 'no_schedule',
                'message' => 'Tidak ada jadwal hari ini',
                'color' => 'text-gray-600'
            ];
        }

        $now = now();
        $scheduleTime = $this->currentSchedule->date->setTimeFromFormat('H:i', $this->getScheduleStartTime());
        
        if ($this->currentSchedule->attendance?->check_in) {
            if ($this->currentSchedule->attendance?->check_out) {
                return [
                    'status' => 'completed',
                    'message' => 'Sudah check-in dan check-out',
                    'color' => 'text-green-600'
                ];
            } else {
                return [
                    'status' => 'checked_in',
                    'message' => 'Sudah check-in, menunggu check-out',
                    'color' => 'text-blue-600'
                ];
            }
        }

        if ($now->lt($scheduleTime->copy()->subMinutes(30))) {
            return [
                'status' => 'too_early',
                'message' => 'Terlalu awal untuk check-in',
                'color' => 'text-gray-600'
            ];
        }

        if ($now->gt($scheduleTime->copy()->addMinutes(30))) {
            return [
                'status' => 'late',
                'message' => 'Terlambat untuk check-in',
                'color' => 'text-red-600'
            ];
        }

        return [
            'status' => 'ready',
            'message' => 'Waktunya check-in',
            'color' => 'text-green-600'
        ];
    }
}
