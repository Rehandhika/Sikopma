<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Notifikasi</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $stats['unread'] }} notifikasi belum dibaca</p>
        </div>
        <div class="flex items-center space-x-2">
            @if($stats['unread'] > 0)
                <button wire:click="markAllAsRead" class="btn btn-white text-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Tandai Semua Dibaca
                </button>
            @endif
            @if($stats['total'] > 0)
                <button wire:click="deleteAll" wire:confirm="Yakin ingin menghapus semua notifikasi?" class="btn btn-white text-sm text-red-600">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Hapus Semua
                </button>
            @endif
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex space-x-2">
            <button wire:click="$set('filter', 'all')" 
                    class="px-4 py-2 rounded-md text-sm font-medium {{ $filter === 'all' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-100' }}">
                Semua ({{ $stats['total'] }})
            </button>
            <button wire:click="$set('filter', 'unread')" 
                    class="px-4 py-2 rounded-md text-sm font-medium {{ $filter === 'unread' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-100' }}">
                Belum Dibaca ({{ $stats['unread'] }})
            </button>
            <button wire:click="$set('filter', 'read')" 
                    class="px-4 py-2 rounded-md text-sm font-medium {{ $filter === 'read' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-100' }}">
                Sudah Dibaca
            </button>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="space-y-3">
        @forelse($notifications as $notification)
            <div class="bg-white rounded-lg shadow {{ $notification->read_at ? 'opacity-75' : '' }} hover:shadow-md transition-shadow">
                <div class="p-4">
                    <div class="flex items-start">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center
                                {{ $notification->type === 'success' ? 'bg-green-100' : 
                                   ($notification->type === 'warning' ? 'bg-yellow-100' : 
                                   ($notification->type === 'error' ? 'bg-red-100' : 'bg-blue-100')) }}">
                                @if($notification->type === 'success')
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @elseif($notification->type === 'warning')
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                @elseif($notification->type === 'error')
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @endif
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="ml-4 flex-1">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-gray-900">{{ $notification->title }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $notification->message }}</p>
                                    <p class="text-xs text-gray-400 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex items-center space-x-2 ml-4">
                                    @if(!$notification->read_at)
                                        <button wire:click="markAsRead({{ $notification->id }})" 
                                                class="text-indigo-600 hover:text-indigo-800"
                                                title="Tandai sudah dibaca">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                    <button wire:click="delete({{ $notification->id }})" 
                                            wire:confirm="Yakin ingin menghapus notifikasi ini?"
                                            class="text-red-600 hover:text-red-800"
                                            title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Link if exists -->
                            @if($notification->link)
                                <a href="{{ $notification->link }}" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 mt-2">
                                    Lihat Detail
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <p class="text-gray-500">
                    @if($filter === 'unread')
                        Tidak ada notifikasi belum dibaca
                    @elseif($filter === 'read')
                        Tidak ada notifikasi yang sudah dibaca
                    @else
                        Tidak ada notifikasi
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    <div>{{ $notifications->links() }}</div>
</div>
