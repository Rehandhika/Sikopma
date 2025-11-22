<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empty State Component Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Empty State Component Tests</h1>

        <!-- Test 1: Basic empty state (default) -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Test 1: Basic Empty State (Default)</h2>
            <x-layout.empty-state />
        </div>

        <!-- Test 2: Custom icon and title -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Test 2: Custom Icon and Title</h2>
            <x-layout.empty-state 
                icon="users"
                title="Tidak ada pengguna"
            />
        </div>

        <!-- Test 3: With description -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Test 3: With Description</h2>
            <x-layout.empty-state 
                icon="document-text"
                title="Tidak ada dokumen"
                description="Belum ada dokumen yang tersedia. Mulai dengan menambahkan dokumen pertama Anda."
            />
        </div>

        <!-- Test 4: With action button -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Test 4: With Action Button</h2>
            <x-layout.empty-state 
                icon="folder-plus"
                title="Tidak ada proyek"
                description="Anda belum memiliki proyek. Buat proyek pertama Anda untuk memulai."
            >
                <x-slot:action>
                    <x-ui.button variant="primary">
                        Buat Proyek Baru
                    </x-ui.button>
                </x-slot:action>
            </x-layout.empty-state>
        </div>

        <!-- Test 5: In table context -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <h2 class="text-xl font-semibold p-6 pb-4 text-gray-700">Test 5: In Table Context</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <tr>
                            <td colspan="3">
                                <x-layout.empty-state 
                                    icon="user-group"
                                    title="Tidak ada data karyawan"
                                    description="Belum ada karyawan yang terdaftar dalam sistem."
                                >
                                    <x-slot:action>
                                        <x-ui.button variant="primary" size="sm">
                                            Tambah Karyawan
                                        </x-ui.button>
                                    </x-slot:action>
                                </x-layout.empty-state>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Test 6: In list context -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Test 6: In List Context</h2>
            <div class="border border-gray-200 rounded-lg">
                <x-layout.empty-state 
                    icon="clipboard-list"
                    title="Tidak ada tugas"
                    description="Daftar tugas Anda kosong. Tambahkan tugas baru untuk memulai."
                >
                    <x-slot:action>
                        <x-ui.button variant="outline" size="sm">
                            Tambah Tugas
                        </x-ui.button>
                    </x-slot:action>
                </x-layout.empty-state>
            </div>
        </div>

        <!-- Test 7: Search results context -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Test 7: Search Results Context</h2>
            <div class="mb-4">
                <input type="text" placeholder="Cari produk..." class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="xyz123">
            </div>
            <x-layout.empty-state 
                icon="magnifying-glass"
                title="Tidak ada hasil pencarian"
                description="Tidak dapat menemukan produk yang sesuai dengan 'xyz123'. Coba kata kunci lain."
            >
                <x-slot:action>
                    <x-ui.button variant="ghost" size="sm">
                        Hapus Filter
                    </x-ui.button>
                </x-slot:action>
            </x-layout.empty-state>
        </div>

        <!-- Test 8: Multiple action buttons -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Test 8: Multiple Action Buttons</h2>
            <x-layout.empty-state 
                icon="photo"
                title="Tidak ada gambar"
                description="Galeri Anda masih kosong. Upload gambar atau pilih dari library."
            >
                <x-slot:action>
                    <div class="flex items-center justify-center space-x-3">
                        <x-ui.button variant="primary" size="sm">
                            Upload Gambar
                        </x-ui.button>
                        <x-ui.button variant="outline" size="sm">
                            Pilih dari Library
                        </x-ui.button>
                    </div>
                </x-slot:action>
            </x-layout.empty-state>
        </div>

        <!-- Test 9: Different icon variations -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Test 9: Different Icon Variations</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="border border-gray-200 rounded-lg p-4">
                    <x-layout.empty-state 
                        icon="bell"
                        title="Tidak ada notifikasi"
                        class="py-8"
                    />
                </div>
                <div class="border border-gray-200 rounded-lg p-4">
                    <x-layout.empty-state 
                        icon="shopping-cart"
                        title="Keranjang kosong"
                        class="py-8"
                    />
                </div>
                <div class="border border-gray-200 rounded-lg p-4">
                    <x-layout.empty-state 
                        icon="heart"
                        title="Tidak ada favorit"
                        class="py-8"
                    />
                </div>
            </div>
        </div>

        <!-- Test 10: Custom styling with attributes -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Test 10: Custom Styling</h2>
            <x-layout.empty-state 
                icon="exclamation-triangle"
                title="Tidak ada data tersedia"
                description="Terjadi kesalahan saat memuat data. Silakan coba lagi nanti."
                class="py-16 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300"
            >
                <x-slot:action>
                    <x-ui.button variant="secondary" size="sm">
                        Muat Ulang
                    </x-ui.button>
                </x-slot:action>
            </x-layout.empty-state>
        </div>

        <!-- Test 11: Compact version -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Test 11: Compact Version</h2>
            <x-layout.empty-state 
                icon="inbox"
                title="Tidak ada pesan"
                class="py-6"
            />
        </div>

    </div>
</body>
</html>
