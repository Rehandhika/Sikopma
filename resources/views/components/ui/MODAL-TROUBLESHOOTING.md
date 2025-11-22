# Modal Component - Troubleshooting Guide

## Masalah: Modal Tidak Muncul / Tombol Tidak Berfungsi

### Gejala
- Tombol terlihat di halaman tapi tidak ada yang terjadi saat diklik
- Modal tidak muncul
- Tidak ada error di console browser

### Penyebab
Alpine.js tidak ter-load dengan benar di halaman.

### Solusi

#### Untuk Halaman Standalone (Tanpa Livewire)
Jika Anda membuat halaman test atau demo yang tidak menggunakan layout utama atau Livewire, Anda perlu menambahkan Alpine.js secara manual:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modal Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <!-- Your content here -->
    
    <!-- Alpine.js via CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
```

#### Untuk Halaman dengan Livewire
Jika Anda menggunakan Livewire component atau layout utama (`layouts/app.blade.php`), Alpine.js sudah tersedia melalui `@livewireScripts`:

```blade
@extends('layouts.app')

@section('content')
    <x-ui.button @click="$dispatch('open-modal-example')">
        Open Modal
    </x-ui.button>

    <x-ui.modal name="example" title="Example">
        Content here
    </x-ui.modal>
@endsection
```

### Konfigurasi Alpine.js di Proyek Ini

Proyek ini menggunakan **Livewire 3** yang sudah include Alpine.js secara built-in. Berikut konfigurasinya:

#### 1. Package.json
```json
{
    "dependencies": {
        "alpinejs": "^3.15.1",
        "@alpinejs/collapse": "^3.15.1",
        "@alpinejs/focus": "^3.15.1",
        "@alpinejs/intersect": "^3.15.1"
    }
}
```

#### 2. Alpine Init (resources/js/alpine-init.js)
File ini berisi konfigurasi Alpine.js yang akan dijalankan saat Alpine.js di-initialize:

```javascript
document.addEventListener('alpine:init', () => {
    // Global stores
    Alpine.store('sidebar', { ... });
    Alpine.store('notifications', { ... });
    
    // Data components
    Alpine.data('dropdown', () => ({ ... }));
    Alpine.data('modal', () => ({ ... }));
    Alpine.data('tabs', () => ({ ... }));
});
```

#### 3. App.js (resources/js/app.js)
```javascript
import './bootstrap';
import './alpine-init';  // Alpine configuration
```

#### 4. Layout Utama (resources/views/layouts/app.blade.php)
```blade
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <!-- Content -->
    
    @livewireScripts  <!-- This loads Alpine.js -->
</body>
```

### Cara Mengecek Alpine.js Ter-load

Buka browser console (F12) dan ketik:

```javascript
Alpine.version
```

Jika Alpine.js ter-load dengan benar, akan muncul versi Alpine.js (misalnya: "3.15.1").

### Debugging Modal

#### 1. Cek Alpine.js Tersedia
```javascript
// Di browser console
console.log(typeof Alpine);  // Should return "object"
```

#### 2. Cek Event Dispatch
```javascript
// Di browser console, coba dispatch event manual
window.dispatchEvent(new CustomEvent('open-modal-test'));
```

#### 3. Cek x-data Directive
Pastikan modal component memiliki `x-data` directive:
```blade
<div x-data="{ show: false }" ...>
```

#### 4. Cek Console Errors
Buka browser console (F12) dan lihat apakah ada error JavaScript.

### Common Issues

#### Issue 1: "Alpine is not defined"
**Penyebab**: Alpine.js tidak ter-load  
**Solusi**: Tambahkan Alpine.js CDN atau pastikan `@livewireScripts` ada di layout

#### Issue 2: Modal muncul tapi tidak bisa ditutup
**Penyebab**: Event listener tidak berfungsi  
**Solusi**: Pastikan `closeable` prop tidak di-set ke `false`

#### Issue 3: Animasi tidak smooth
**Penyebab**: Tailwind transitions tidak ter-load  
**Solusi**: Pastikan `@vite(['resources/css/app.css', 'resources/js/app.js'])` ada di head

#### Issue 4: Backdrop tidak muncul
**Penyebab**: z-index conflict atau CSS tidak ter-load  
**Solusi**: Cek apakah ada element lain dengan z-index lebih tinggi

### Testing Checklist

Sebelum melaporkan bug, pastikan:

- [ ] Alpine.js ter-load (cek `Alpine.version` di console)
- [ ] Tidak ada error di browser console
- [ ] Modal component memiliki unique `name` prop
- [ ] Button menggunakan `@click="$dispatch('open-modal-{name}')"`
- [ ] Modal component ada di halaman yang sama dengan button
- [ ] Vite assets ter-compile (`npm run build` atau `npm run dev`)

### Contoh Implementasi yang Benar

#### Standalone Page
```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Modal Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-ui.button @click="$dispatch('open-modal-demo')">
        Open Modal
    </x-ui.button>

    <x-ui.modal name="demo" title="Demo Modal">
        <p>Content here</p>
    </x-ui.modal>

    <!-- IMPORTANT: Add Alpine.js for standalone pages -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
```

#### Livewire Component
```blade
<div>
    <x-ui.button wire:click="$dispatch('open-modal-demo')">
        Open Modal
    </x-ui.button>

    <x-ui.modal name="demo" title="Demo Modal">
        <p>Content here</p>
    </x-ui.modal>
</div>
```

#### Blade View with Layout
```blade
@extends('layouts.app')

@section('content')
    <x-ui.button @click="$dispatch('open-modal-demo')">
        Open Modal
    </x-ui.button>

    <x-ui.modal name="demo" title="Demo Modal">
        <p>Content here</p>
    </x-ui.modal>
@endsection
```

### Bantuan Lebih Lanjut

Jika masalah masih berlanjut:

1. Clear browser cache (Ctrl+Shift+Delete)
2. Rebuild assets: `npm run build`
3. Clear Laravel cache: `php artisan cache:clear`
4. Restart development server
5. Cek browser compatibility (gunakan browser modern)

### File Test yang Sudah Diperbaiki

✅ `resources/views/components/ui/modal-test.blade.php` - Sudah include Alpine.js CDN  
✅ `resources/views/components/ui/modal-example.blade.php` - Sudah include Alpine.js CDN

Kedua file ini sekarang bisa diakses langsung tanpa perlu layout Livewire.

---

**Update**: 22 November 2025  
**Status**: Masalah Alpine.js sudah diperbaiki
