<div class="max-w-4xl mx-auto space-y-6">
    <!-- Status Card -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Absensi Enhanced</h2>
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 rounded-full {{ $locationStatus === 'success' ? 'bg-green-500' : ($locationStatus === 'out_of_range' ? 'bg-red-500' : 'bg-yellow-500') }} animate-pulse"></div>
                <span class="text-sm {{ $getLocationStatusColor() }}">{{ $getLocationStatusMessage() }}</span>
            </div>
        </div>

        @if($currentSchedule)
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg p-6 text-white mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-semibold mb-2">Jadwal Hari Ini</h3>
                        <p class="text-indigo-100">{{ $currentSchedule->date->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
                        <p class="text-indigo-100">Sesi {{ $currentSchedule->session }} ({{ $getScheduleStartTime() }} - {{ $getScheduleEndTime() }})</p>
                        <p class="text-lg font-medium mt-2">{{ $getScheduleStatus()['message'] }}</p>
                    </div>
                    <div class="text-right">
                        @if($currentSchedule->attendance)
                            @if($currentSchedule->attendance->check_in)
                                <div class="mb-2">
                                    <p class="text-sm text-indigo-100">Check-in:</p>
                                    <p class="font-semibold">{{ $currentSchedule->attendance->check_in->format('H:i:s') }}</p>
                                </div>
                            @endif
                            @if($currentSchedule->attendance->check_out)
                                <div>
                                    <p class="text-sm text-indigo-100">Check-out:</p>
                                    <p class="font-semibold">{{ $currentSchedule->attendance->check_out->format('H:i:s') }}</p>
                                </div>
                            @endif
                        @else
                            <div class="text-center">
                                <svg class="w-16 h-16 mx-auto mb-2 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-sm">Belum absen</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="bg-gray-100 rounded-lg p-6 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Jadwal</h3>
                <p class="text-gray-600">Anda tidak memiliki jadwal kerja hari ini</p>
            </div>
        @endif
    </div>

    <!-- Location & Photo Validation -->
    @if($currentSchedule && ($canCheckIn || $canCheckOut))
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Validasi Absensi</h3>
            
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Location Section -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-700">Lokasi GPS</label>
                        <button wire:click="refreshLocation" 
                                wire:loading.attr="disabled"
                                class="text-sm text-indigo-600 hover:text-indigo-800 transition">
                            <span wire:loading>
                                <svg class="animate-spin h-4 w-4 inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span wire:loading.remove>Refresh</span>
                        </button>
                    </div>
                    
                    @if($latitude && $longitude)
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-gray-600">Latitude:</span>
                                    <span class="font-mono ml-1">{{ number_format($latitude, 6) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Longitude:</span>
                                    <span class="font-mono ml-1">{{ number_format($longitude, 6) }}</span>
                                </div>
                                @if($locationAccuracy)
                                    <div class="col-span-2">
                                        <span class="text-gray-600">Akurasi:</span>
                                        <span class="font-mono ml-1">{{ $locationAccuracy }}m</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <p class="text-sm text-yellow-800">
                                @if($isGettingLocation)
                                    <span class="flex items-center">
                                        <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Mendapatkan lokasi...
                                    </span>
                                @else
                                    Lokasi belum tersedia. Klik "Refresh" untuk mendapatkan lokasi.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Photo Section -->
                @if($canCheckIn && config('sikopma.attendance.require_photo', true))
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Foto Wajah</label>
                        
                        @if($photoCaptured)
                            <div class="bg-gray-50 rounded-lg p-3">
                                <img src="{{ $capturedPhotoData }}" alt="Captured photo" class="w-full h-32 object-cover rounded mb-2">
                                <div class="flex space-x-2">
                                    <button wire:click="retakePhoto" class="flex-1 btn btn-white text-sm">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Ulangi Foto
                                    </button>
                                </div>
                            </div>
                        @else
                            <button wire:click="openCamera" 
                                    class="w-full bg-gray-100 hover:bg-gray-200 border-2 border-dashed border-gray-300 rounded-lg p-4 transition">
                                <div class="text-center">
                                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <p class="text-sm text-gray-600">Klik untuk ambil foto</p>
                                </div>
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Notes Section -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                <textarea wire:model="notes" 
                          rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                          placeholder="Tambahkan catatan jika diperlukan..."></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex space-x-4">
                @if($canCheckIn)
                    <button wire:click="checkIn" 
                            wire:loading.attr="disabled"
                            wire:target="checkIn"
                            class="flex-1 btn btn-indigo-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading>
                            <svg class="animate-spin h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses...
                        </span>
                        <span wire:loading.remove>
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            Check In
                        </span>
                    </button>
                @endif

                @if($canCheckOut)
                    <button wire:click="checkOut" 
                            wire:loading.attr="disabled"
                            wire:target="checkOut"
                            class="flex-1 btn btn-red-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading>
                            <svg class="animate-spin h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses...
                        </span>
                        <span wire:loading.remove>
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Check Out
                        </span>
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Camera Modal -->
@if($showCameraModal)
<div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-2xl w-full mx-4">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Ambil Foto Wajah</h3>
            <button wire:click="closeCamera" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="p-4">
            <div class="relative bg-black rounded-lg overflow-hidden" style="aspect-ratio: 4/3;">
                <video id="camera-stream" class="w-full h-full object-cover" autoplay></video>
                <canvas id="photo-canvas" class="hidden"></canvas>
                
                <!-- Overlay Guide -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="border-2 border-white rounded-full w-32 h-32 opacity-50"></div>
                    <p class="absolute bottom-4 text-white text-sm bg-black bg-opacity-50 px-3 py-1 rounded">
                        Posisikan wajah di dalam lingkaran
                    </p>
                </div>
            </div>
            
            <div class="flex justify-center space-x-4 mt-4">
                <button wire:click="capturePhoto" 
                        wire:loading.attr="disabled"
                        wire:target="capturePhoto"
                        class="btn btn-indigo-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-indigo-700 transition disabled:opacity-50">
                    <span wire:loading>
                        <svg class="animate-spin h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                    <span wire:loading.remove>
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Ambil Foto
                    </span>
                </button>
                <button wire:click="closeCamera" class="btn btn-gray-200 text-gray-800 px-6 py-2 rounded-lg font-medium hover:bg-gray-300 transition">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- JavaScript for Location and Camera -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Location handling
    @this.on('requestLocation', () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    @this.call('locationReceived', {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                    });
                },
                (error) => {
                    console.error('Location error:', error);
                    @this.call('locationReceived', {
                        latitude: null,
                        longitude: null,
                        accuracy: null,
                        error: error.message,
                    });
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0,
                }
            );
        } else {
            @this.call('locationReceived', {
                latitude: null,
                longitude: null,
                accuracy: null,
                error: 'Geolocation not supported',
            });
        }
    });

    // Camera handling
    let stream = null;
    let videoElement = null;

    @this.on('initCamera', () => {
        videoElement = document.getElementById('camera-stream');
        
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'user',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                } 
            })
            .then(function(mediaStream) {
                stream = mediaStream;
                videoElement.srcObject = stream;
            })
            .catch(function(error) {
                console.error('Camera error:', error);
                alert('Tidak dapat mengakses kamera. Pastikan Anda telah memberikan izin kamera.');
            });
        }
    });

    @this.on('stopCamera', () => {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        if (videoElement) {
            videoElement.srcObject = null;
        }
    });

    @this.on('capturePhoto', () => {
        const video = document.getElementById('camera-stream');
        const canvas = document.getElementById('photo-canvas');
        
        if (video && canvas) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0);
            
            const imageData = canvas.toDataURL('image/jpeg', 0.8);
            @this.call('photoCaptured', imageData);
        }
    });
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    @this.call('stopCamera');
});
</script>
