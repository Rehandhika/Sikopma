# Empty State Component

## Overview

The Empty State component provides a consistent way to display empty states throughout the application. It's designed to be used in tables, lists, search results, and any other context where data might be empty or unavailable.

## Component Location

`resources/views/components/layout/empty-state.blade.php`

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `icon` | string | `'inbox'` | The Heroicon name to display |
| `title` | string | `'Tidak ada data'` | The main heading text |
| `description` | string\|null | `null` | Optional description text below the title |

## Slots

| Slot | Required | Description |
|------|----------|-------------|
| `action` | No | Optional slot for CTA buttons or actions |

## Usage Examples

### Basic Usage (Default)

```blade
<x-layout.empty-state />
```

### Custom Icon and Title

```blade
<x-layout.empty-state 
    icon="users"
    title="Tidak ada pengguna"
/>
```

### With Description

```blade
<x-layout.empty-state 
    icon="document-text"
    title="Tidak ada dokumen"
    description="Belum ada dokumen yang tersedia. Mulai dengan menambahkan dokumen pertama Anda."
/>
```

### With Action Button

```blade
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
```

### In Table Context

```blade
<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
        </tr>
    </thead>
    <tbody class="bg-white">
        @if($users->isEmpty())
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
        @else
            @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->status }}</td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>
```

### In List Context

```blade
<div class="border border-gray-200 rounded-lg">
    @if($tasks->isEmpty())
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
    @else
        @foreach($tasks as $task)
            <!-- Task items -->
        @endforeach
    @endif
</div>
```

### Search Results Context

```blade
<div class="mb-4">
    <input type="text" placeholder="Cari produk..." wire:model.live="search">
</div>

@if($products->isEmpty())
    <x-layout.empty-state 
        icon="magnifying-glass"
        title="Tidak ada hasil pencarian"
        description="Tidak dapat menemukan produk yang sesuai dengan '{{ $search }}'. Coba kata kunci lain."
    >
        <x-slot:action>
            <x-ui.button variant="ghost" size="sm" wire:click="clearSearch">
                Hapus Filter
            </x-ui.button>
        </x-slot:action>
    </x-layout.empty-state>
@else
    <!-- Display products -->
@endif
```

### Multiple Action Buttons

```blade
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
```

### Compact Version

```blade
<x-layout.empty-state 
    icon="inbox"
    title="Tidak ada pesan"
    class="py-6"
/>
```

### Custom Styling

```blade
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
```

## Common Use Cases

### 1. Empty Notifications

```blade
<x-layout.empty-state 
    icon="bell"
    title="Tidak ada notifikasi"
    class="py-8"
/>
```

### 2. Empty Shopping Cart

```blade
<x-layout.empty-state 
    icon="shopping-cart"
    title="Keranjang kosong"
    description="Belum ada produk di keranjang Anda."
>
    <x-slot:action>
        <x-ui.button variant="primary" href="{{ route('products.index') }}">
            Mulai Belanja
        </x-ui.button>
    </x-slot:action>
</x-layout.empty-state>
```

### 3. Empty Favorites

```blade
<x-layout.empty-state 
    icon="heart"
    title="Tidak ada favorit"
    description="Anda belum menambahkan item favorit."
/>
```

### 4. No Data Available (Error State)

```blade
<x-layout.empty-state 
    icon="exclamation-triangle"
    title="Tidak ada data tersedia"
    description="Terjadi kesalahan saat memuat data. Silakan coba lagi nanti."
>
    <x-slot:action>
        <x-ui.button variant="secondary" size="sm" wire:click="retry">
            Muat Ulang
        </x-ui.button>
    </x-slot:action>
</x-layout.empty-state>
```

## Available Icons

Common icons for empty states:
- `inbox` - General empty state
- `users` / `user-group` - No users/members
- `document-text` - No documents
- `folder-plus` - No folders/projects
- `clipboard-list` - No tasks/items
- `magnifying-glass` - No search results
- `photo` - No images
- `bell` - No notifications
- `shopping-cart` - Empty cart
- `heart` - No favorites
- `calendar` - No events
- `exclamation-triangle` - Error/warning state

## Styling

The component uses Tailwind CSS utility classes:
- Default padding: `py-12` (can be overridden with `class` attribute)
- Icon: `h-12 w-12 text-gray-400`
- Title: `text-sm font-medium text-gray-900`
- Description: `text-sm text-gray-500`
- Action slot: `mt-6` spacing

## Accessibility

- Uses semantic HTML structure
- Icon is decorative (handled by icon component)
- Title uses `<h3>` for proper heading hierarchy
- Description uses `<p>` for proper text semantics

## Responsive Behavior

The component is responsive by default:
- Center-aligned text
- Icon and text stack vertically
- Action buttons can be made responsive using flex utilities

## Best Practices

1. **Choose appropriate icons**: Select icons that match the context (e.g., `users` for empty user lists)
2. **Write clear titles**: Keep titles short and descriptive
3. **Add helpful descriptions**: Explain why the state is empty and what users can do
4. **Provide actions when possible**: Give users a clear next step with action buttons
5. **Adjust padding for context**: Use `class="py-6"` for compact spaces, `class="py-16"` for larger areas
6. **Use in conditional rendering**: Always wrap in `@if` checks to show only when data is empty

## Testing

Test file available at: `resources/views/components/layout/empty-state-test.blade.php`

Access via: `http://127.0.0.1:8000/demo/empty-state`

## Requirements Satisfied

- ✅ 1.2: Component follows design system with consistent variants
- ✅ 1.3: Uses Tailwind utility classes exclusively
- ✅ 6.5: Provides empty state component for various contexts

## Related Components

- `x-ui.icon` - Used for displaying icons
- `x-ui.button` - Used in action slots
- `x-layout.page-header` - Often used together for page layouts
- `x-data.table` - Commonly contains empty states

## Migration Notes

When refactoring existing views:

**Before:**
```blade
@if($items->isEmpty())
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400">...</svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
        <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan item baru.</p>
        <div class="mt-6">
            <button class="btn btn-primary">Tambah Item</button>
        </div>
    </div>
@endif
```

**After:**
```blade
@if($items->isEmpty())
    <x-layout.empty-state 
        icon="inbox"
        title="Tidak ada data"
        description="Mulai dengan menambahkan item baru."
    >
        <x-slot:action>
            <x-ui.button variant="primary">Tambah Item</x-ui.button>
        </x-slot:action>
    </x-layout.empty-state>
@endif
```
