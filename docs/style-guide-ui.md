# Style Guide UI (Public Pages)

Dokumen ini menjadi referensi konsistensi UI untuk halaman publik (Katalog, Detail Produk, Tentang).

## Stack UI
- Tailwind CSS + CSS Variables (shadcn/ui)
- Komponen UI: `resources/js/components/ui/*` (shadcn/ui)
- Layout publik React: `resources/js/react/components/PublicLayout.jsx`

## Desain Dasar
### Warna (Mode Gelap)
- Background utama: `bg-slate-950`
- Surface/card: `bg-slate-900/60` (glass) atau `bg-slate-900/40` (lebih ringan)
- Border: `border-white/10` untuk card utama, `border-white/5` untuk card ringan
- Aksen utama: `primary` (indigo) via token shadcn (`--primary`) dan utilitas Tailwind `indigo-*`
- Teks:
  - Judul: `text-white`
  - Body: `text-slate-300`
  - Muted: `text-slate-400` / `text-slate-500`

### Tipografi
- Judul halaman: `text-4xl md:text-5xl font-bold tracking-tight`
- Subjudul: `text-base md:text-lg text-slate-400`
- Judul section/card: `text-2xl font-bold text-white`
- Body: `text-sm text-slate-300 leading-relaxed`
- Label kecil: `text-xs uppercase tracking-wider text-slate-500 font-bold`

### Radius, Spacing, Shadow
- Radius besar: `rounded-3xl` untuk section utama
- Radius sedang: `rounded-2xl` untuk card/thumbnail
- Spacing container: `max-w-7xl mx-auto px-4` (tambahkan `sm:px-6 lg:px-8` untuk halaman text-heavy)
- Shadow: gunakan seperlunya (mis. card utama `shadow-xl` atau `shadow-2xl`)

## Layout & Navigasi
### Struktur Halaman Publik
- Gunakan `PublicLayout` untuk semua halaman publik agar navbar/footer/background konsisten.
- Konten utama ditempatkan di dalam container `max-w-7xl mx-auto`.

### Navigasi
- Navbar (desktop): link `Katalog`, `Tentang`, dan CTA `Login`.
- Navbar (mobile): gunakan `Sheet` dengan item menu yang sama.
- Indikator status toko: gunakan `StoreStatusPopover` pada navbar.

## Komponen & Pola Interaksi
### Button
- Pakai shadcn `Button` untuk semua tombol/CTA.
- Utamakan varian:
  - Primary CTA: default (`variant` default)
  - Secondary action: `variant="secondary"` atau `variant="outline"`
  - Link/toolbar: `variant="ghost"`

### Input / Filter
- Gunakan shadcn `Input` untuk pencarian.
- Gunakan shadcn `Select` untuk filter kategori.
- Hindari dropdown custom baru kecuali ada kebutuhan khusus.

### Card
- Gunakan shadcn `Card` untuk card produk, card informasi, dan section konten.
- Untuk “glass card” gunakan kelas tambahan: `bg-slate-900/60 backdrop-blur-xl border-white/10 rounded-3xl`.

### Badge
- Gunakan shadcn `Badge` untuk status:
  - Featured: kuning (`bg-yellow-500/20 text-yellow-300 border-yellow-500/30`)
  - Stok habis: merah (`bg-red-500/20 text-red-300 border-red-500/30`)
  - Stok menipis: oranye (`bg-orange-500/20 text-orange-300 border-orange-500/30`)
  - Stok tersedia: hijau (`bg-green-500/20 text-green-300 border-green-500/30`)

### Responsiveness (Breakpoint)
- Mobile-first.
- Grid umum:
  - 1 kolom di mobile
  - `md:grid-cols-3` untuk grid info
  - `lg:grid-cols-12` untuk halaman detail (visual + info)

## Checklist Konsistensi
- Semua halaman publik memakai `PublicLayout`.
- Semua tombol memakai shadcn `Button`.
- Semua input/filter memakai shadcn `Input`/`Select`.
- Semua card memakai shadcn `Card`.
- Hierarki tipografi mengikuti skala di atas.
- Kontras dan border konsisten (`border-white/10` vs `border-white/5`).

