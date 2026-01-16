## Analisis Kebutuhan
### Komponen UI yang Harus Responsif terhadap Tema
- Layout publik: background halaman, ambient background, navbar glass, footer.
- Typography: warna judul/teks utama, muted text, link/hover state, selection.
- Surface: card (utama & ringan), popover, sheet (mobile menu), separator.
- Controls: button (primary/outline/ghost/secondary), input (search), select (filter), pagination.
- Status/Badge: featured, stok (habis/menipis/tersedia), status toko (open/closed) + indikator dot.
- Media: placeholder gambar produk, border image container.
- Ikon: warna default dan hover/focus state.

### Inventaris Warna yang Saat Ini Hardcoded (Perlu Disesuaikan)
- Di React public pages masih banyak kelas `bg-slate-*`, `text-slate-*`, `border-white/*`, `text-white`, serta gradient spesifik.
- HTML template publik saat ini memaksa mode gelap via `class="dark"` di [react.blade.php](file:///c:/laragon/www/Kopma/resources/views/public/react.blade.php#L1-L26), sehingga light mode belum mungkin.
- Token shadcn sudah ada di [app.css](file:///c:/laragon/www/Kopma/resources/css/app.css) (semantic vars `--background`, `--foreground`, `--card`, dst) dengan `:root` (light) dan `.dark` (dark).

### Daftar Variabel Warna (Semantic Tokens) yang Akan Dipakai (Kedua Mode)
Mengikuti shadcn/ui + Tailwind semantic:
- `--background`, `--foreground`
- `--card`, `--card-foreground`
- `--popover`, `--popover-foreground`
- `--primary`, `--primary-foreground`
- `--secondary`, `--secondary-foreground`
- `--muted`, `--muted-foreground`
- `--accent`, `--accent-foreground`
- `--destructive`, `--destructive-foreground`
- `--border`, `--input`, `--ring`
Tambahan untuk kebutuhan “glass” (agar konsisten dan tidak hardcode):
- `--glass-bg`, `--glass-border`, `--glass-hover-bg`, `--page-bg`, `--page-selection-bg`, `--page-selection-fg` (opsional namun disarankan)

## Desain Sistem
### Skema Warna (Konsisten untuk Dark & Light)
- **Dark Mode (gelap)**: tetap memakai palet yang sudah dipakai sekarang (slate-950 + indigo primary) namun dipetakan ke semantic tokens.
- **Light Mode (terang)**: background terang (mis. slate-50/white), teks gelap (slate-900), card putih, border abu tipis, primary tetap indigo.
- Kontras: target minimal WCAG AA:
  - Teks body ≥ 4.5:1
  - Teks besar (judul) ≥ 3:1
  - UI boundary (border, input) terlihat jelas di kedua mode

### Strategi Penyimpanan Preferensi
- Gunakan `localStorage` key `theme`:
  - `"light"` / `"dark"` / `"system"` (opsional, tapi sangat membantu).
- Fallback: jika tidak ada nilai, default ke `system`.
- Kenapa localStorage: cepat, tidak perlu roundtrip server, cocok untuk preferensi UI.

### Toggle Switch yang Mudah Diakses
- Tempat: di **navbar** (desktop & mobile) supaya selalu tersedia.
- Bentuk: menu tema (Dropdown) atau Switch.
  - Disarankan: **DropdownMenu** berisi pilihan `Light`, `Dark`, `System` karena jelas & accessible.
- Aksesibilitas:
  - `aria-label="Theme"`, state terbaca screen reader.
  - Fokus keyboard jelas (mengandalkan focus ring shadcn).

## Implementasi Teknis
### 1) CSS Variables untuk Kedua Mode
- Tetap gunakan pola shadcn:
  - `:root { ... }` = light
  - `.dark { ... }` = dark
- Tambahkan token tambahan untuk glass/page selection jika diperlukan.
- Pastikan mapping Tailwind semantic (`--color-background`, `--color-foreground`, dst) tetap konsisten.

### 2) Pendekatan Theming
- **Class-based** pada `<html>`:
  - Mode gelap: `<html class="dark">`
  - Mode terang: tanpa class `dark`
- Optional: tambahkan `data-theme="dark|light"` untuk debugging, tapi class-based sudah cukup.

### 3) Anti-flicker (Super Smooth)
- Tambahkan inline script kecil di `<head>` [react.blade.php](file:///c:/laragon/www/Kopma/resources/views/public/react.blade.php#L1-L26) **sebelum** CSS loaded:
  - Baca localStorage `theme`
  - Resolve `system` via `matchMedia('(prefers-color-scheme: dark)')`
  - Set/remove class `dark` di `document.documentElement`
  - Set `color-scheme` (via meta atau `documentElement.style.colorScheme`) agar form controls native ikut sesuai
- Ini mencegah “flash” tema saat load.

### 4) Fungsi JavaScript Manajemen Tema
- Buat module `themeManager` (mis. `resources/js/react/lib/theme.js`) yang menyediakan:
  - `getThemePreference()`
  - `getResolvedTheme()`
  - `applyTheme(preference)`
  - `subscribeToSystemThemeChanges(callback)` (aktif hanya jika preference `system`)
- Semua perubahan tema hanya mengubah class html + localStorage, tidak re-render berat.

### 5) Komponen Toggle Reusable
- Buat `ThemeToggle` (mis. `resources/js/react/components/ThemeToggle.jsx`):
  - Pakai shadcn `DropdownMenu` + `Button` + ikon `Sun/Moon/Laptop`
  - Update tema via `themeManager.applyTheme()`
- Integrasi di `PublicNavbar` dan menu mobile `Sheet`.

### 6) Refactor UI Agar Tidak Hardcode Warna
Target file utama:
- `resources/js/react/components/PublicLayout.jsx`
- `resources/js/react/components/PublicNavbar.jsx`
- `resources/js/react/components/StoreStatusPopover.jsx`
- `resources/js/react/pages/HomePage.jsx`
- `resources/js/react/pages/AboutPage.jsx`
- `resources/js/react/pages/ProductDetailPage.jsx`
Perubahan pola:
- Ganti `bg-slate-950 text-slate-300` → `bg-background text-foreground`
- Ganti `text-slate-400/500` → `text-muted-foreground`
- Ganti `border-white/10` → `border-border`
- Card glass: gunakan token `bg-card/..` atau `bg-background/..` + `backdrop-blur` yang konsisten
- Pastikan komponen shadcn (Button/Input/Card/Select/Popover/Sheet) tetap memakai semantic classes default.

## Pengujian & Kualitas
### Checklist Functional
- Toggle mengubah tema di semua halaman: `/`, `/products`, `/about`, `/products/{slug}`
- Preferensi tersimpan dan dipakai kembali setelah refresh.
- Mode `system` (jika diaktifkan) mengikuti perubahan OS tanpa reload.

### Accessibility
- Uji kontras warna teks & badge di light/dark.
- Fokus keyboard terlihat (ring) di toggle, input, tombol.

### Responsiveness
- Toggle muncul di navbar desktop dan tersedia di mobile sheet.
- Tidak mengganggu layout breakpoint (sm/md/lg).

### Performa
- Tidak ada flicker saat navigasi halaman (anti-flicker script di head).
- Perubahan tema tidak memicu rerender besar (cukup class html).

## Deliverables
- **CSS**: update [app.css](file:///c:/laragon/www/Kopma/resources/css/app.css) untuk mendefinisikan light + dark tokens lengkap (termasuk optional glass tokens).
- **Komponen Toggle**: `ThemeToggle` reusable dan integrasi ke navbar & mobile menu.
- **JS Theme Manager**: module untuk apply/resolve/save theme + listener system.
- **Dokumentasi Developer**: update dokumen style guide (mis. `docs/style-guide-ui.md`) dengan bagian “Theming” (token, cara pakai class semantic, aturan menghindari hardcode warna).

## Catatan Implementasi yang Akan Saya Lakukan Setelah Konfirmasi
- Menghapus pemaksaan `class="dark"` di [react.blade.php](file:///c:/laragon/www/Kopma/resources/views/public/react.blade.php#L1-L26) dan menggantinya dengan script pre-hydration.
- Refactor class hardcoded slate/white-alpha ke semantic token supaya light mode benar-benar konsisten.
- Menambahkan toggle ke navbar dengan UX yang sama di semua halaman.
