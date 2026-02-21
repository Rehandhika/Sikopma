<div>
    <!-- Page Header -->
    <x-layout.page-header 
        title="Buat Permintaan Tukar Shift"
        description="Ajukan permintaan tukar shift dengan pengguna lain">
        <x-slot:actions>
            <x-ui.badge variant="info" size="md">
                <x-ui.icon name="clock" class="w-4 h-4 mr-1" />
                Deadline: 24 jam sebelum shift
            </x-ui.badge>
        </x-slot:actions>
    </x-layout.page-header>

    <!-- Progress Steps -->
    <x-ui.card class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center flex-1">
                <div class="flex items-center {{ $selectedAssignment ? 'text-primary-600' : 'text-gray-400' }}">
                    <div class="w-8 h-8 rounded-full {{ $selectedAssignment ? 'bg-primary-600' : 'bg-gray-300' }} text-white flex items-center justify-center text-sm font-medium">
                        1
                    </div>
                    <span class="ml-2 text-sm font-medium">Pilih Shift Anda</span>
                </div>
            </div>
            <div class="flex-1 border-t-2 {{ $selectedAssignment ? 'border-primary-600' : 'border-gray-300' }} mx-4"></div>
            <div class="flex items-center flex-1">
                <div class="flex items-center {{ ($targetDate && $targetSession) ? 'text-primary-600' : 'text-gray-400' }}">
                    <div class="w-8 h-8 rounded-full {{ ($targetDate && $targetSession) ? 'bg-primary-600' : 'bg-gray-300' }} text-white flex items-center justify-center text-sm font-medium">
                        2
                    </div>
                    <span class="ml-2 text-sm font-medium">Pilih Target Shift</span>
                </div>
            </div>
            <div class="flex-1 border-t-2 {{ ($targetDate && $targetSession) ? 'border-primary-600' : 'border-gray-300' }} mx-4"></div>
            <div class="flex items-center flex-1">
                <div class="flex items-center {{ $selectedTarget ? 'text-primary-600' : 'text-gray-400' }}">
                    <div class="w-8 h-8 rounded-full {{ $selectedTarget ? 'bg-primary-600' : 'bg-gray-300' }} text-white flex items-center justify-center text-sm font-medium">
                        3
                    </div>
                    <span class="ml-2 text-sm font-medium">Pilih Target User</span>
                </div>
            </div>
            <div class="flex-1 border-t-2 {{ $selectedTarget ? 'border-primary-600' : 'border-gray-300' }} mx-4"></div>
            <div class="flex items-center flex-1">
                <div class="flex items-center {{ $reason ? 'text-primary-600' : 'text-gray-400' }}">
                    <div class="w-8 h-8 rounded-full {{ $reason ? 'bg-primary-600' : 'bg-gray-300' }} text-white flex items-center justify-center text-sm font-medium">
                        4
                    </div>
                    <span class="ml-2 text-sm font-medium">Alasan & Konfirmasi</span>
                </div>
            </div>
        </div>
    </x-ui.card>

    <form wire:submit="validateSwapRequest">
        <!-- Step 1: Pilih Shift Anda -->
        <x-ui.card class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <x-ui.icon name="calendar" class="w-5 h-5 mr-2 text-primary-600" />
                Step 1: Pilih Shift Anda yang Ingin Ditukar
            </h2>
            
            @if($myAssignments->isEmpty())
                <x-layout.empty-state 
                    icon="calendar" 
                    title="Anda tidak memiliki shift yang akan datang"
                    description="Hubungi admin untuk penugasan shift" />
            @else
                <x-layout.grid cols="3" gap="4">
                    @foreach($myAssignments as $assignment)
                        <div class="border rounded-lg p-4 cursor-pointer transition-all hover:shadow-md {{ 
                            $selectedAssignment == $assignment->id 
                                ? 'border-primary-500 bg-primary-50' 
                                : 'border-gray-200 hover:border-gray-300' 
                        }}" wire:click="$set('selectedAssignment', {{ $assignment->id }})">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <x-ui.icon name="calendar" class="w-4 h-4 mr-2 text-primary-600" />
                                        <span class="font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($assignment->date)->locale('id')->format('l, d F Y') }}</span>
                                    </div>
                                    <div class="flex items-center mb-2">
                                        <x-ui.icon name="clock" class="w-4 h-4 mr-2 text-gray-500" />
                                        <span class="text-sm text-gray-600">{{ $assignment->time_start }} - {{ $assignment->time_end }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <x-ui.icon name="squares-2x2" class="w-4 h-4 mr-2 text-gray-500" />
                                        <span class="text-sm text-gray-600">Sesi {{ $assignment->session }}</span>
                                    </div>
                                    @if($assignment->schedule)
                                        <div class="mt-2">
                                            <x-ui.badge variant="gray" size="sm">
                                                {{ $assignment->schedule->day }}
                                            </x-ui.badge>
                                        </div>
                                    @endif
                                </div>
                                @if($selectedAssignment == $assignment->id)
                                    <div class="ml-2">
                                        <x-ui.icon name="check-circle" class="w-6 h-6 text-primary-600" />
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Deadline Warning -->
                            @php
                                $deadline = $assignment->date->copy()->setTimeFromTimeString($assignment->time_start)->subHours(24);
                                $isNearDeadline = now()->greaterThan($deadline->copy()->subHours(12));
                                $isPastDeadline = now()->greaterThan($deadline);
                            @endphp
                            
                            @if($isPastDeadline)
                                <div class="mt-3">
                                    <x-ui.badge variant="danger" size="sm" class="w-full justify-center">
                                        <x-ui.icon name="exclamation-triangle" class="w-3 h-3 mr-1" />
                                        Deadline terlewati
                                    </x-ui.badge>
                                </div>
                            @elseif($isNearDeadline)
                                <div class="mt-3">
                                    <x-ui.badge variant="warning" size="sm" class="w-full justify-center">
                                        <x-ui.icon name="exclamation-circle" class="w-3 h-3 mr-1" />
                                        Deadline: {{ $deadline->diffForHumans() }}
                                    </x-ui.badge>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </x-layout.grid>
                
                @error('selectedAssignment')
                    <p class="mt-2 text-sm text-danger-600 flex items-center">
                        <x-ui.icon name="exclamation-circle" class="w-4 h-4 mr-1" />
                        {{ $message }}
                    </p>
                @enderror
            @endif
        </x-ui.card>

        <!-- Step 2: Pilih Target Shift -->
        <x-ui.card class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <x-ui.icon name="magnifying-glass" class="w-5 h-5 mr-2 text-primary-600" />
                Step 2: Pilih Target Shift yang Diinginkan
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-ui.input 
                    type="date" 
                    wire:model.live="targetDate"
                    label="Tanggal Target"
                    name="targetDate"
                    :min="today()->format('Y-m-d')"
                    :disabled="!$selectedAssignment"
                    :error="$errors->first('targetDate')"
                    icon="calendar" />
                
                <x-ui.select 
                    wire:model.live="targetSession"
                    label="Sesi Target"
                    name="targetSession"
                    :disabled="!$targetDate"
                    :error="$errors->first('targetSession')"
                    icon="squares-2x2">
                    <option value="">Pilih Sesi</option>
                    @if(isset($sessionOptions))
                        @foreach($sessionOptions as $key => $option)
                            <option value="{{ $key }}">{{ $option }}</option>
                        @endforeach
                    @endif
                </x-ui.select>
            </div>
            
            <!-- Available Targets Preview -->
            @if($targetDate && $targetSession)
                <div class="mt-6">
                    <x-ui.badge variant="info" size="md" class="w-full justify-center">
                        <x-ui.icon name="information-circle" class="w-4 h-4 mr-1" />
                        Menampilkan {{ $availableTargets->count() }} pengguna yang tersedia untuk 
                        {{ \Carbon\Carbon::parse($targetDate)->locale('id')->format('l, d F Y') }} - Sesi {{ $targetSession }}
                    </x-ui.badge>
                </div>
            @endif
        </x-ui.card>

        <!-- Step 3: Pilih Target User -->
        @if($targetDate && $targetSession)
            <x-ui.card class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <x-ui.icon name="users" class="w-5 h-5 mr-2 text-primary-600" />
                    Step 3: Pilih Target User
                </h2>
                
                @if($availableTargets->isEmpty())
                    <x-layout.empty-state 
                        icon="user" 
                        title="Tidak ada pengguna yang tersedia untuk shift ini"
                        description="Coba pilih tanggal atau sesi yang lain" />
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($availableTargets as $target)
                            <div class="border rounded-lg p-4 cursor-pointer transition-all hover:shadow-md {{ 
                                $selectedTarget == $target['id'] 
                                    ? 'border-primary-500 bg-primary-50' 
                                    : 'border-gray-200 hover:border-gray-300' 
                            }}" wire:click="$set('selectedTarget', {{ $target['id'] }})">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <x-ui.avatar 
                                            :name="$target['name']" 
                                            size="md" />
                                        <div class="ml-3">
                                            <p class="font-medium text-gray-900">{{ $target['name'] }}</p>
                                            <p class="text-sm text-gray-500">{{ $target['nim'] }}</p>
                                        </div>
                                    </div>
                                    @if($selectedTarget == $target['id'])
                                        <x-ui.icon name="check-circle" class="w-6 h-6 text-primary-600" />
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @error('selectedTarget')
                        <p class="mt-2 text-sm text-danger-600 flex items-center">
                            <x-ui.icon name="exclamation-circle" class="w-4 h-4 mr-1" />
                            {{ $message }}
                        </p>
                    @enderror
                @endif
            </x-ui.card>
        @endif

        <!-- Step 4: Alasan & Konfirmasi -->
        @if($selectedTarget)
            <x-ui.card class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <x-ui.icon name="chat-bubble-left" class="w-5 h-5 mr-2 text-primary-600" />
                    Step 4: Alasan Permintaan
                </h2>
                
                <x-ui.textarea 
                    wire:model="reason"
                    label="Alasan Tukar Shift"
                    name="reason"
                    rows="4"
                    placeholder="Jelaskan alasan mengapa Anda ingin menukar shift..."
                    :required="true"
                    :error="$errors->first('reason')"
                    help="Minimal 10 karakter, maksimal 500 karakter ({{ strlen($reason) ?? 0 }}/500)" />
            </x-ui.card>
        @endif

        <!-- Summary & Submit -->
        @if($selectedAssignment && $selectedTarget && $reason)
            <x-ui.card class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <x-ui.icon name="clipboard-document-check" class="w-5 h-5 mr-2 text-primary-600" />
                    Ringkasan Permintaan
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <h3 class="font-medium text-gray-900">Shift Anda</h3>
                        @php
                            $myAssignment = $myAssignments->where('id', $selectedAssignment)->first();
                        @endphp
                        @if($myAssignment)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($myAssignment->date)->locale('id')->format('l, d F Y') }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ $myAssignment->time_start }} - {{ $myAssignment->time_end }} (Sesi {{ $myAssignment->session }})</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="space-y-3">
                        <h3 class="font-medium text-gray-900">Shift Target</h3>
                        @php
                            $targetUser = $availableTargets->where('id', $selectedTarget)->first();
                        @endphp
                        @if($targetUser)
                            <div class="bg-primary-50 rounded-lg p-4">
                                <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($targetDate)->locale('id')->format('l, d F Y') }}</p>
                                <p class="text-sm text-gray-600 mt-1">Sesi {{ $targetSession }}</p>
                                <p class="text-sm text-primary-700 mt-2 font-medium">Dengan: {{ $targetUser['name'] }} ({{ $targetUser['nim'] }})</p>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="mt-6">
                    <x-ui.badge variant="warning" size="md" class="w-full justify-start">
                        <x-ui.icon name="exclamation-triangle" class="w-4 h-4 mr-2" />
                        <span><strong>Penting:</strong> Permintaan akan dikirim ke {{ $targetUser['name'] ?? 'target user' }} untuk persetujuan. 
                        Setelah disetujui, admin akan melakukan persetujuan final.</span>
                    </x-ui.badge>
                </div>
            </x-ui.card>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-3">
                <x-ui.button 
                    type="button"
                    wire:click="resetForm"
                    variant="white"
                    icon="arrow-path">
                    Reset Form
                </x-ui.button>
                <x-ui.button 
                    type="submit"
                    variant="primary"
                    :loading="true"
                    icon="paper-airplane">
                    Kirim Permintaan
                </x-ui.button>
            </div>
        @endif
    </form>

    <!-- Confirmation Modal -->
    @if($showConfirmation)
        <x-ui.modal name="swap-confirmation" title="Konfirmasi Permintaan Tukar Shift" maxWidth="md">
            <div class="space-y-3">
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
                <div class="pt-3 border-t border-gray-200">
                    <p class="text-sm font-medium text-gray-900">Alasan:</p>
                    <p class="text-sm text-gray-700 mt-1">{{ $reason }}</p>
                </div>
            </div>

            <x-slot:footer>
                <x-ui.button 
                    wire:click="$set('showConfirmation', false)"
                    variant="white">
                    Batal
                </x-ui.button>
                <x-ui.button 
                    wire:click="createSwapRequest"
                    variant="primary"
                    :loading="true">
                    Ya, Kirim Permintaan
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>
