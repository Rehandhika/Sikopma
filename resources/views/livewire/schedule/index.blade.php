<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
    {{-- Header --}}
    <div class="mb-4 sm:mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Manajemen Jadwal</h1>
                <p class="mt-1 text-sm text-gray-600">Kelola jadwal shift mingguan</p>
            </div>
            <x-ui.button href="{{ route('admin.schedule.create') }}" variant="primary" icon="plus" class="w-full sm:w-auto">
                Buat Jadwal Baru
            </x-ui.button>
        </div>
    </div>

    {{-- Member Availability Section --}}
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-4 sm:mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Status Ketersediaan Minggu Ini</h2>
                <p class="text-sm text-gray-500">{{ $this->currentWeekStart->format('d M') }} - {{ $this->currentWeekEnd->format('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 bg-green-500 rounded-full"></span>
                    <span class="text-gray-600">{{ $this->availabilityStats['submitted'] }} Sudah</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 bg-gray-300 rounded-full"></span>
                    <span class="text-gray-600">{{ $this->availabilityStats['pending'] }} Belum</span>
                </div>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="mb-4">
            <div class="flex items-center justify-between text-sm mb-1">
                <span class="text-gray-600">Progress Pengisian</span>
                <span class="font-medium text-gray-900">{{ $this->availabilityStats['percentage'] }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full transition-all duration-300" style="width: {{ $this->availabilityStats['percentage'] }}%"></div>
            </div>
        </div>

        {{-- Member List --}}
        <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">NIM</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Sesi</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($this->membersWithAvailability as $member)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $member['name'] }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500">{{ $member['nim'] ?? '-' }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($member['has_submitted'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Sudah
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    Belum
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-center text-sm text-gray-600">
                            {{ $member['total_sessions'] }}/12
                        </td>
                        <td class="px-4 py-2 text-center">
                            @if($member['has_submitted'])
                                <button wire:click="viewMemberAvailability({{ $member['id'] }})" 
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Lihat
                                </button>
                            @else
                                <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-4 sm:mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select wire:model.live="filterStatus" class="input w-full">
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                <select wire:model.live="filterMonth" class="input w-full">
                    <option value="">Semua Bulan</option>
                    @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}">{{ \Carbon\Carbon::create()->month($i)->locale('id')->monthName }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                <select wire:model.live="filterYear" class="input w-full">
                    <option value="">Semua Tahun</option>
                    @for($year = now()->year - 1; $year <= now()->year + 1; $year++)
                    <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                <input type="text" wire:model.live.debounce.300ms="search" class="input w-full" placeholder="Cari jadwal...">
            </div>
        </div>
    </div>

    {{-- Schedule List --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        @if($schedules->isEmpty())
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada jadwal</h3>
            <p class="text-gray-600 mb-4">Mulai dengan membuat jadwal baru</p>
            <x-ui.button href="{{ route('admin.schedule.create') }}" variant="primary" icon="plus">
                Buat Jadwal Baru
            </x-ui.button>
        </div>
        @else
        {{-- Desktop View --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statistik</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($schedules as $schedule)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($schedule->week_start_date)->locale('id')->isoFormat('D MMM') }} - 
                                {{ \Carbon\Carbon::parse($schedule->week_end_date)->locale('id')->isoFormat('D MMM YYYY') }}
                            </div>
                            @if($schedule->notes)
                            <div class="text-xs text-gray-500 mt-1">{{ Str::limit($schedule->notes, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($schedule->status === 'published')
                            <x-ui.badge variant="success">Published</x-ui.badge>
                            @elseif($schedule->status === 'draft')
                            <x-ui.badge variant="warning">Draft</x-ui.badge>
                            @else
                            <x-ui.badge variant="gray">Archived</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <span class="font-medium">{{ $schedule->assignments_count }}</span> assignments
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ number_format(($schedule->assignments_count / 12) * 100, 0) }}% coverage
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $schedule->created_at->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $schedule->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <x-ui.button href="{{ route('admin.schedule.edit', $schedule) }}" variant="ghost" size="sm" icon="eye" title="View" />
                                @if($schedule->status === 'draft')
                                <x-ui.button wire:click="publish({{ $schedule->id }})" variant="ghost" size="sm" icon="check" title="Publish" />
                                @endif
                                <x-ui.button wire:click="delete({{ $schedule->id }})" wire:confirm="Yakin ingin menghapus jadwal ini?" variant="ghost" size="sm" icon="trash" class="text-red-600" title="Delete" />
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile View --}}
        <div class="md:hidden divide-y divide-gray-200">
            @foreach($schedules as $schedule)
            <div class="p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($schedule->week_start_date)->locale('id')->isoFormat('D MMM') }} - 
                            {{ \Carbon\Carbon::parse($schedule->week_end_date)->locale('id')->isoFormat('D MMM YYYY') }}
                        </div>
                        @if($schedule->notes)
                        <div class="text-xs text-gray-500 mt-1 truncate">{{ $schedule->notes }}</div>
                        @endif
                    </div>
                    @if($schedule->status === 'published')
                    <x-ui.badge variant="success" class="ml-2">Published</x-ui.badge>
                    @elseif($schedule->status === 'draft')
                    <x-ui.badge variant="warning" class="ml-2">Draft</x-ui.badge>
                    @else
                    <x-ui.badge variant="gray" class="ml-2">Archived</x-ui.badge>
                    @endif
                </div>
                
                <div class="flex items-center justify-between text-sm mb-3">
                    <div>
                        <span class="font-medium text-gray-900">{{ $schedule->assignments_count }}</span>
                        <span class="text-gray-500"> assignments</span>
                    </div>
                    <div class="text-gray-500">
                        {{ number_format(($schedule->assignments_count / 12) * 100, 0) }}% coverage
                    </div>
                </div>
                
                <div class="text-xs text-gray-500 mb-3">
                    Dibuat {{ $schedule->created_at->diffForHumans() }}
                </div>
                
                <div class="flex items-center space-x-2">
                    <x-ui.button href="{{ route('admin.schedule.edit', $schedule) }}" variant="secondary" size="sm" icon="eye" class="flex-1">
                        View
                    </x-ui.button>
                    @if($schedule->status === 'draft')
                    <x-ui.button wire:click="publish({{ $schedule->id }})" variant="primary" size="sm" icon="check" class="flex-1">
                        Publish
                    </x-ui.button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($schedules->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $schedules->links() }}
        </div>
        @endif
        @endif
    </div>

    {{-- Member Availability Modal --}}
    @if($showMemberModal && $selectedMemberAvailability)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeMemberModal"></div>
            
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-4">
                <div class="px-4 pt-5 pb-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Ketersediaan {{ $selectedMemberName }}</h3>
                        <button wire:click="closeMemberModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-700">Hari</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-700">Sesi 1</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-700">Sesi 2</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-700">Sesi 3</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @php
                                    $days = ['monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu', 'thursday' => 'Kamis'];
                                @endphp
                                @foreach($days as $dayKey => $dayLabel)
                                <tr>
                                    <td class="px-3 py-2 font-medium text-gray-900">{{ $dayLabel }}</td>
                                    @for($s = 1; $s <= 3; $s++)
                                        <td class="px-3 py-2 text-center">
                                            @if($selectedMemberAvailability[$dayKey][$s] ?? false)
                                                <span class="inline-flex items-center justify-center w-7 h-7 bg-green-500 text-white rounded-lg">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center justify-center w-7 h-7 bg-gray-100 text-gray-400 rounded-lg">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </span>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 rounded-b-lg">
                    <button wire:click="closeMemberModal" class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
