<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Log Sistem</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ Carbon\Carbon::parse($dateFrom)->format('d M Y') }}
                @if($dateFrom !== $dateTo) - {{ Carbon\Carbon::parse($dateTo)->format('d M Y') }} @endif
            </p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex border-b border-gray-200">
        <button wire:click="setLogType('activity')" 
            class="px-4 py-2 text-sm font-medium border-b-2 transition-colors {{ $logType === 'activity' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            Aktivitas User
        </button>
        <button wire:click="setLogType('audit')" 
            class="px-4 py-2 text-sm font-medium border-b-2 transition-colors {{ $logType === 'audit' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            Audit Perubahan (Teknis)
        </button>
    </div>

    {{-- Quick Date Presets --}}
    <div class="flex flex-wrap gap-2">
        @foreach(['today' => 'Hari Ini', 'yesterday' => 'Kemarin', 'week' => '7 Hari', 'month' => '30 Hari'] as $key => $label)
            <button wire:click="setDatePreset('{{ $key }}')" 
                class="px-3 py-1.5 text-sm rounded-lg transition-colors {{ $datePreset === $key ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Aktivitas</p>
                    <p class="text-2xl font-bold text-primary-600 mt-1">{{ number_format($this->stats['total']) }}</p>
                </div>
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                    <x-ui.icon name="clipboard-document-list" class="w-5 h-5 text-primary-600" />
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">User Aktif</p>
                    <p class="text-2xl font-bold text-success-600 mt-1">{{ number_format($this->stats['unique_users']) }}</p>
                </div>
                <div class="w-10 h-10 bg-success-100 rounded-lg flex items-center justify-center">
                    <x-ui.icon name="user-group" class="w-5 h-5 text-success-600" />
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl p-4 border border-gray-200">
        <div class="flex flex-col lg:flex-row gap-4">
            {{-- Date Range --}}
            <div class="grid grid-cols-2 gap-2 lg:w-auto">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Dari</label>
                    <input type="date" wire:model.live="dateFrom" 
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Sampai</label>
                    <input type="date" wire:model.live="dateTo" 
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>
            
            {{-- User Filter --}}
            <div class="flex-1 lg:max-w-xs">
                <label class="block text-xs font-medium text-gray-500 mb-1">User</label>
                <select wire:model.live="userId" 
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Semua User</option>
                    @foreach($this->users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->nim }})</option>
                    @endforeach
                </select>
            </div>
            
            {{-- Search --}}
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Cari Aktivitas</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" wire:model.live.debounce.300ms="search" 
                        placeholder="Cari deskripsi aktivitas..." 
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>
        </div>
    </div>

    {{-- Activity Log List --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {{-- Desktop Table --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                        @if($logType === 'activity')
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aktivitas</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Info Tambahan</th>
                        @else
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi & Model</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Perubahan</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($activities as $log)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $log->created_at->format('d/m/Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $log->created_at->format('H:i:s') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->user)
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($log->user->name, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $log->user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $log->user->nim ?? '-' }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400 italic">User Dihapus</span>
                                @endif
                            </td>
                            @if($logType === 'activity')
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">{{ $log->activity }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-gray-500 space-y-1">
                                        @if($log->ip_address)
                                            <div class="flex items-center gap-1">
                                                <x-ui.icon name="globe-alt" class="w-3.5 h-3.5" />
                                                {{ $log->ip_address }}
                                            </div>
                                        @endif
                                        @if($log->metadata)
                                            <div class="mt-2 bg-gray-50 p-2 rounded text-[10px] font-mono overflow-hidden">
                                                @foreach($log->metadata as $key => $value)
                                                    <div><span class="text-primary-600">{{ $key }}:</span> {{ is_array($value) ? json_encode($value) : $value }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            @else
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full w-fit {{ 
                                            match($log->action) {
                                                'create' => 'bg-success-100 text-success-700',
                                                'update' => 'bg-blue-100 text-blue-700',
                                                'delete' => 'bg-danger-100 text-danger-700',
                                                default => 'bg-gray-100 text-gray-700'
                                            }
                                        }}">
                                            {{ $log->action }}
                                        </span>
                                        <span class="text-xs font-mono text-gray-600 mt-1">{{ class_basename($log->model) }} #{{ $log->model_id }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->action === 'update')
                                        <div class="text-[10px] space-y-1 max-w-xs overflow-hidden">
                                            @foreach($log->new_values ?? [] as $key => $val)
                                                @if($key !== 'updated_at')
                                                    <div class="truncate">
                                                        <span class="font-semibold text-gray-700">{{ $key }}:</span>
                                                        <span class="text-danger-600 line-through">{{ is_array($log->old_values[$key] ?? '') ? 'json' : ($log->old_values[$key] ?? 'null') }}</span>
                                                        <span class="mx-1 text-gray-400">→</span>
                                                        <span class="text-success-600">{{ is_array($val) ? 'json' : $val }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @elseif($log->action === 'create')
                                        <span class="text-xs text-gray-500 italic">Data Baru Dibuat</span>
                                    @elseif($log->action === 'delete')
                                        <span class="text-xs text-danger-500 italic">Data Dihapus</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="font-medium">Tidak ada log aktivitas</p>
                                <p class="text-sm mt-1">Belum ada aktivitas yang tercatat untuk periode yang dipilih</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="lg:hidden divide-y divide-gray-200">
            @forelse($activities as $activity)
                <div class="p-4 hover:bg-gray-50 transition">
                    <div class="flex items-start gap-3">
                        @if($activity->user)
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-bold text-sm shrink-0">
                                {{ strtoupper(substr($activity->user->name, 0, 2)) }}
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                @if($activity->user)
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $activity->user->name }}</p>
                                @else
                                    <p class="text-sm text-gray-400 italic">User Dihapus</p>
                                @endif
                                <span class="text-xs text-gray-500 whitespace-nowrap">{{ $activity->created_at->format('H:i') }}</span>
                            </div>
                            @if($activity->user)
                                <p class="text-xs text-gray-500">{{ $activity->user->nim ?? '-' }}</p>
                            @endif
                            <p class="text-sm text-gray-700 mt-1">{{ $activity->activity }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $activity->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="font-medium">Tidak ada log aktivitas</p>
                    <p class="text-sm mt-1">Belum ada aktivitas yang tercatat untuk periode yang dipilih</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Pagination --}}
    @if($activities->hasPages())
        <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
            {{ $activities->links() }}
        </div>
    @endif
</div>
