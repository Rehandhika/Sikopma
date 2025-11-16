<div>
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Buat Permintaan Tukar Shift</h1>
                <p class="text-gray-600 mt-1">Ajukan permintaan tukar shift dengan pengguna lain</p>
            </div>
            <div class="flex items-center space-x-2">
                <div class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">
                    <i class="fas fa-clock mr-1"></i>
                    Deadline: 24 jam sebelum shift
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Steps -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center flex-1">
                <div class="flex items-center {{ $selectedAssignment ? 'text-blue-600' : 'text-gray-400' }}">
                    <div class="w-8 h-8 rounded-full {{ $selectedAssignment ? 'bg-blue-600' : 'bg-gray-300' }} text-white flex items-center justify-center text-sm font-medium">
                        1
                    </div>
                    <span class="ml-2 text-sm font-medium">Pilih Shift Anda</span>
                </div>
            </div>
            <div class="flex-1 border-t-2 {{ $selectedAssignment ? 'border-blue-600' : 'border-gray-300' }} mx-4"></div>
            <div class="flex items-center flex-1">
                <div class="flex items-center {{ ($targetDate && $targetSession) ? 'text-blue-600' : 'text-gray-400' }}">
                    <div class="w-8 h-8 rounded-full {{ ($targetDate && $targetSession) ? 'bg-blue-600' : 'bg-gray-300' }} text-white flex items-center justify-center text-sm font-medium">
                        2
                    </div>
                    <span class="ml-2 text-sm font-medium">Pilih Target Shift</span>
                </div>
            </div>
            <div class="flex-1 border-t-2 {{ ($targetDate && $targetSession) ? 'border-blue-600' : 'border-gray-300' }} mx-4"></div>
            <div class="flex items-center flex-1">
                <div class="flex items-center {{ $selectedTarget ? 'text-blue-600' : 'text-gray-400' }}">
                    <div class="w-8 h-8 rounded-full {{ $selectedTarget ? 'bg-blue-600' : 'bg-gray-300' }} text-white flex items-center justify-center text-sm font-medium">
                        3
                    </div>
                    <span class="ml-2 text-sm font-medium">Pilih Target User</span>
                </div>
            </div>
            <div class="flex-1 border-t-2 {{ $selectedTarget ? 'border-blue-600' : 'border-gray-300' }} mx-4"></div>
            <div class="flex items-center flex-1">
                <div class="flex items-center {{ $reason ? 'text-blue-600' : 'text-gray-400' }}">
                    <div class="w-8 h-8 rounded-full {{ $reason ? 'bg-blue-600' : 'bg-gray-300' }} text-white flex items-center justify-center text-sm font-medium">
                        4
                    </div>
                    <span class="ml-2 text-sm font-medium">Alasan & Konfirmasi</span>
                </div>
            </div>
        </div>
    </div>

    <form wire:submit="validateSwapRequest">
        <!-- Step 1: Pilih Shift Anda -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-calendar-check mr-2 text-blue-600"></i>
                Step 1: Pilih Shift Anda yang Ingin Ditukar
            </h2>
            
            @if($myAssignments->isEmpty())
                <div class="text-center py-8 bg-gray-50 rounded-lg">
                    <i class="fas fa-calendar-times text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-600">Anda tidak memiliki shift yang akan datang</p>
                    <p class="text-sm text-gray-500 mt-1">Hubungi admin untuk penugasan shift</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($myAssignments as $assignment)
                        <div class="border rounded-lg p-4 cursor-pointer transition-all hover:shadow-md {{ 
                            $selectedAssignment == $assignment->id 
                                ? 'border-blue-500 bg-blue-50' 
                                : 'border-gray-200 hover:border-gray-300' 
                        }}" wire:click="$set('selectedAssignment', {{ $assignment->id }})">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-calendar-day text-blue-600 mr-2"></i>
                                        <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($assignment->date)->locale('id')->format('l, d F Y') }}</span>
                                    </div>
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-clock text-gray-500 mr-2"></i>
                                        <span class="text-sm text-gray-600">{{ $assignment->time_start }} - {{ $assignment->time_end }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-layer-group text-gray-500 mr-2"></i>
                                        <span class="text-sm text-gray-600">Sesi {{ $assignment->session }}</span>
                                    </div>
                                    @if($assignment->schedule)
                                        <div class="mt-2">
                                            <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">
                                                {{ $assignment->schedule->day }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                @if($selectedAssignment == $assignment->id)
                                    <div class="ml-2">
                                        <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Deadline Warning -->
                            @php
                                $deadline = \Carbon\Carbon::parse($assignment->date . ' ' . $assignment->time_start)->subHours(24);
                                $isNearDeadline = now()->greaterThan($deadline->copy()->subHours(12));
                                $isPastDeadline = now()->greaterThan($deadline);
                            @endphp
                            
                            @if($isPastDeadline)
                                <div class="mt-3 bg-red-50 border border-red-200 rounded p-2">
                                    <p class="text-xs text-red-700">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Deadline terlewati
                                    </p>
                                </div>
                            @elseif($isNearDeadline)
                                <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded p-2">
                                    <p class="text-xs text-yellow-700">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        Deadline: {{ $deadline->diffForHumans() }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                
                @error('selectedAssignment')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            @endif
        </div>

        <!-- Step 2: Pilih Target Shift -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-search mr-2 text-blue-600"></i>
                Step 2: Pilih Target Shift yang Diinginkan
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-1"></i>
                        Tanggal Target
                    </label>
                    <input 
                        type="date" 
                        wire:model.live="targetDate"
                        min="{{ today()->format('Y-m-d') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        {{ !$selectedAssignment ? 'disabled' : '' }}
                    >
                    @error('targetDate')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-layer-group mr-1"></i>
                        Sesi Target
                    </label>
                    <select 
                        wire:model.live="targetSession"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        {{ !$targetDate ? 'disabled' : '' }}
                    >
                        <option value="">Pilih Sesi</option>
                        @if(isset($sessionOptions))
                            @foreach($sessionOptions as $key => $option)
                                <option value="{{ $key }}">{{ $option }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('targetSession')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Available Targets Preview -->
            @if($targetDate && $targetSession)
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        Menampilkan {{ $availableTargets->count() }} pengguna yang tersedia untuk 
                        {{ \Carbon\Carbon::parse($targetDate)->locale('id')->format('l, d F Y') }} - Sesi {{ $targetSession }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Step 3: Pilih Target User -->
        @if($targetDate && $targetSession)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-users mr-2 text-blue-600"></i>
                    Step 3: Pilih Target User
                </h2>
                
                @if($availableTargets->isEmpty())
                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                        <i class="fas fa-user-slash text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-600">Tidak ada pengguna yang tersedia untuk shift ini</p>
                        <p class="text-sm text-gray-500 mt-1">Coba pilih tanggal atau sesi yang lain</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($availableTargets as $target)
                            <div class="border rounded-lg p-4 cursor-pointer transition-all hover:shadow-md {{ 
                                $selectedTarget == $target['id'] 
                                    ? 'border-blue-500 bg-blue-50' 
                                    : 'border-gray-200 hover:border-gray-300' 
                            }}" wire:click="$set('selectedTarget', {{ $target['id'] }})">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-blue-600"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="font-medium text-gray-900">{{ $target['name'] }}</p>
                                            <p class="text-sm text-gray-500">{{ $target['nim'] }}</p>
                                        </div>
                                    </div>
                                    @if($selectedTarget == $target['id'])
                                        <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @error('selectedTarget')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                @endif
            </div>
        @endif

        <!-- Step 4: Alasan & Konfirmasi -->
        @if($selectedTarget)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-comment-alt mr-2 text-blue-600"></i>
                    Step 4: Alasan Permintaan
                </h2>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan Tukar Shift <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        wire:model="reason"
                        rows="4"
                        placeholder="Jelaskan alasan mengapa Anda ingin menukar shift..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    ></textarea>
                    <div class="mt-1 flex justify-between">
                        <p class="text-xs text-gray-500">Minimal 10 karakter</p>
                        <p class="text-xs text-gray-500">{{ strlen($reason) ?? 0 }}/500 karakter</p>
                    </div>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        @endif

        <!-- Summary & Submit -->
        @if($selectedAssignment && $selectedTarget && $reason)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-clipboard-check mr-2 text-blue-600"></i>
                    Ringkasan Permintaan
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <h3 class="font-medium text-gray-900">Shift Anda</h3>
                        @php
                            $myAssignment = $myAssignments->where('id', $selectedAssignment)->first();
                        @endphp
                        @if($myAssignment)
                            <div class="bg-gray-50 rounded p-3">
                                <p class="text-sm font-medium">{{ \Carbon\Carbon::parse($myAssignment->date)->locale('id')->format('l, d F Y') }}</p>
                                <p class="text-sm text-gray-600">{{ $myAssignment->time_start }} - {{ $myAssignment->time_end }} (Sesi {{ $myAssignment->session }})</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="space-y-3">
                        <h3 class="font-medium text-gray-900">Shift Target</h3>
                        @php
                            $targetUser = $availableTargets->where('id', $selectedTarget)->first();
                        @endphp
                        @if($targetUser)
                            <div class="bg-blue-50 rounded p-3">
                                <p class="text-sm font-medium">{{ \Carbon\Carbon::parse($targetDate)->locale('id')->format('l, d F Y') }}</p>
                                <p class="text-sm text-gray-600">Sesi {{ $targetSession }}</p>
                                <p class="text-sm text-blue-700 mt-1">Dengan: {{ $targetUser['name'] }} ({{ $targetUser['nim'] }})</p>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Penting:</strong> Permintaan akan dikirim ke {{ $targetUser['name'] ?? 'target user' }} untuk persetujuan. 
                        Setelah disetujui, admin akan melakukan persetujuan final.
                    </p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-3">
                <button 
                    type="button"
                    wire:click="resetForm"
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    <i class="fas fa-redo mr-2"></i>
                    Reset Form
                </button>
                <button 
                    type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>
                        <i class="fas fa-paper-plane mr-2"></i>
                        Kirim Permintaan
                    </span>
                    <span wire:loading>
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Memproses...
                    </span>
                </button>
            </div>
        @endif
    </form>

    <!-- Confirmation Modal -->
    @if($showConfirmation)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-question-circle text-blue-600 mr-2"></i>
                    Konfirmasi Permintaan Tukar Shift
                </h3>
                
                <div class="space-y-3 mb-6">
                    @php
                        $myAssignment = $myAssignments->where('id', $selectedAssignment)->first();
                        $targetUser = $availableTargets->where('id', $selectedTarget)->first();
                    @endphp
                    
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Shift Anda:</span>
                        <span class="font-medium">{{ $myAssignment ? \Carbon\Carbon::parse($myAssignment->date)->locale('id')->format('d/m/Y') : '' }} {{ $myAssignment->time_start ?? '' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Shift Target:</span>
                        <span class="font-medium">{{ \Carbon\Carbon::parse($targetDate)->locale('id')->format('d/m/Y') }} Sesi {{ $targetSession }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Target User:</span>
                        <span class="font-medium">{{ $targetUser['name'] ?? '' }}</span>
                    </div>
                    <div class="pt-3 border-t">
                        <p class="text-sm text-gray-600"><strong>Alasan:</strong></p>
                        <p class="text-sm mt-1">{{ $reason }}</p>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button 
                        wire:click="$set('showConfirmation', false)"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                    >
                        Batal
                    </button>
                    <button 
                        wire:click="createSwapRequest"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>Ya, Kirim Permintaan</span>
                        <span wire:loading>
                            <i class="fas fa-spinner fa-spin mr-1"></i>
                            Mengirim...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
