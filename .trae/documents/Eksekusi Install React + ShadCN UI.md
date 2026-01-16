## Perubahan Utama (Tanpa Versi Paralel)
- Halaman `/` akan dimigrasikan langsung dari Livewire+Blade menjadi halaman React (mount di Blade), memakai shadcn/ui untuk komponen UI.
- Komponen Livewire/Blade lama tidak dipakai lagi di home (boleh tetap ada di repo sebagai referensi, tetapi tidak dijadikan rute).

## 1) Install React + Setup Vite (Best Practice)
- Tambah dependency `react`, `react-dom` dan dev dependency `@vitejs/plugin-react`.
- Update `vite.config.js` untuk plugin React dan entrypoint React (mis. `resources/js/react/main.jsx`) tanpa mengganggu `resources/js/app.js` yang dipakai halaman lain.
- Buat entry React + root component `HomePage`.

## 2) Install shadcn/ui (Resmi) + Konfigurasi
- Jalankan `npx shadcn@latest init` dengan:
  - Tailwind config: `tailwind.config.js`
  - Global CSS: `resources/css/app.css`
  - CSS variables: aktif
  - Alias import: set alias Vite (mis. `@` → `resources/js`)
- Tambahkan komponen yang diperlukan: `button`, `input`, `card`, `badge`, `select`/`dropdown-menu`, `popover`, `sheet`, `carousel`, `pagination`, `separator`, `skeleton`.

## 3) Theme Konsisten dengan Desain Lama
- Sesuaikan token shadcn (CSS variables) agar default tampil dark (slate background, text slate, primary indigo).
- Terapkan strategi dark mode yang konsisten (mis. class `dark` di `<html>`/`<body>` via layout publik).

## 4) Data Layer: Endpoint JSON untuk React
- Buat endpoint JSON untuk:
  - Banners aktif
  - Kategori
  - Produk (search, category, pagination)
  - Store status
- Reuse query logic dari Livewire `Public\Catalog` dan `StoreStatusService` agar hasil sama.

## 5) Implementasi UI Home dengan shadcn
- Navbar + menu mobile → `Button` + `Sheet`
- Status toko → trigger `Button/Badge` + `Popover` (polling tiap 10 detik)
- Banner → `Carousel`
- Search + filter kategori → `Input` + `Select`/`DropdownMenu`
- Grid produk → `Card` + `Badge`
- Pagination → `Pagination`
- Empty state + loading → `Skeleton` (opsional)

## 6) Switch Routing Home
- Ubah route `/` agar me-render Blade mount React (bukan Livewire component).
- Pastikan assets Vite React ter-load pada layout/halaman mount.

## 7) Verifikasi & Hardening
- Jalankan `npm install` dan `npm run build` untuk memastikan bundling aman.
- Smoke test `/`:
  - Search, filter kategori, pagination
  - Banner carousel interaksi
  - Status toko polling + popover
  - Responsif (mobile menu sheet)
- Pastikan halaman Livewire lain tidak rusak (entry `resources/js/app.js` tetap dipakai untuk halaman non-React).
