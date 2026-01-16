## Konteks Saat Ini

* Proyek sudah memakai shadcn/ui dan carousel berbasis Embla di [carousel.jsx](file:///c:/laragon/www/Kopma/resources/js/components/ui/carousel.jsx).

* HomePage sudah punya `BannerSection` yang menggunakan komponen Carousel, tetapi:

  * Rasio banner masih `aspect-[16/5]` (bukan 16:9).

  * Belum ada autoplay terkonfigurasi, dot indicators, dan komponen reusable khusus banner.

* Codebase React saat ini dominan JS/JSX dan **belum ada TypeScript setup** (tidak ada `tsconfig.json`, tidak ada file `.ts/.tsx`).

## 1) Analisis Kebutuhan

### Komponen UI yang Akan Dibangun/Disesuaikan

* **BannerCarousel** (baru): wrapper di atas shadcn `Carousel` untuk banner.

* **Kontrol**:

  * Tombol Prev/Next (shadcn `Button`) yang overlay di kiri/kanan.

  * Dot indicators (button kecil) yang bisa diklik.

* **Image layer**:

  * `<picture>` + `<img>` (lazy loading + alt).

  * Skeleton/fallback saat loading.

  * Fallback jika image error.

* **Overlay content**:

  * Slot/renderer untuk teks/CTA di atas gambar (opsional per-slide).

* **Animasi**:

  * Transisi slide “smooth” via Embla.

  * Animasi overlay masuk/keluar yang bisa dikustomisasi via props (kelas Tailwind/transition duration).

### Variabel/Props yang Dibutuhkan

* `autoplayIntervalMs` (interval auto slide)

* `transitionDuration` (Embla `duration` 20–60, semakin besar semakin lambat)

* `loop` (default true)

* `showArrows`, `showDots`

* `overlay` per slide atau `renderOverlay(slide, state)`

* `animationClassName` / `overlayClassName` untuk kustom animasi

## 2) Desain Sistem

### Skema Responsif dan Rasio 16:9

* Gunakan container ratio `aspect-[16/9]` agar konsisten di mobile/tablet/desktop.

* Gunakan `object-cover` untuk memastikan gambar tetap penuh dan crop halus.

### Strategi Autoplay

* Gunakan plugin resmi Embla `embla-carousel-autoplay` agar:

  * interval mudah dikonfigurasi (`delay` ms)

  * stop/resume lebih natural saat user interaksi (drag/focus/hover)

* Fallback (jika diperlukan): implementasi manual `setInterval` menggunakan Embla API `scrollNext()`.

### Indikator Posisi (Dots)

* Ambil `scrollSnapList()` untuk jumlah slide.

* Track `selectedScrollSnap()` untuk state index.

* Klik dot memanggil `api.scrollTo(index)`.

### Swipe Gesture

* Sudah otomatis ditangani Embla melalui `embla-carousel-react` (drag/touch).

## 3) Implementasi Teknis

### 3.1 Tambahkan Fondasi TypeScript (Minimal & Aman)

* Tambahkan `tsconfig.json` dengan:

  * `jsx: react-jsx`

  * `allowJs: true` (biar file JS lama tetap aman)

  * `baseUrl` + `paths` untuk alias `@/*` agar typecheck/IDE paham

* Tambahkan `env.d.ts` untuk Vite/JSX typings.

* Tambahkan devDependencies: `typescript`, `@types/react`, `@types/react-dom` (dan opsional `@types/node`).

* (Opsional) tambah script `typecheck: tsc --noEmit`.

### 3.2 Buat Komponen Reusable BannerCarousel (TypeScript)

Buat file misalnya:

* `resources/js/react/components/BannerCarousel.tsx`

Desain API:

* `type BannerSlide = { id: string|number; images: { default: string; [key: string]: string }; title?: string; alt?: string; href?: string; overlay?: React.ReactNode }`

* `type BannerCarouselProps = { slides: BannerSlide[]; autoplayIntervalMs?: number; transitionDuration?: number; showArrows?: boolean; showDots?: boolean; className?: string; overlayClassName?: string; renderOverlay?: (slide: BannerSlide) => React.ReactNode }`

Implementasi fitur:

* Bangun dengan shadcn `Carousel`, `CarouselContent`, `CarouselItem`, `CarouselPrevious`, `CarouselNext`.

* Pass `opts={{ loop: true, duration: transitionDuration }}`.

* Gunakan plugin `embla-carousel-autoplay` dengan `delay: autoplayIntervalMs`.

* Tambahkan dots:

  * state `selectedIndex`, `snapCount`

  * subscribe `api.on('select', ...)`.

* Image:

  * `<picture>` dengan `source` untuk breakpoints bila tersedia.

  * `<img loading="lazy" decoding="async" alt={alt||title||'Banner'}>`.

  * Skeleton (shadcn `Skeleton`) tampil sampai `onLoad`.

  * onError → tampilkan fallback panel.

* Overlay:

  * Render di layer absolute di atas gambar.

  * Animasi overlay via className prop (`transition-opacity/translate-y`, dst).

### 3.3 Integrasi ke HomePage

* Ubah `BannerSection` di [HomePage.jsx](file:///c:/laragon/www/Kopma/resources/js/react/pages/HomePage.jsx) menjadi:

  * Fetch banners tetap dari `/api/public/banners`.

  * Map hasil API → `slides` untuk `BannerCarousel`.

  * Set rasio 16:9.

  * Set default config:

    * `autoplayIntervalMs` mis. 5000

    * `transitionDuration` mis. 40

    * `showArrows: true`, `showDots: true`

  * Pastikan alt text aksesibel (gunakan `banner.title` jika ada).

### 3.4 Styling & Aksesibilitas

* Tombol prev/next:

  * `aria-label` yang jelas.

  * focus ring default shadcn.

* Dots:

  * gunakan `button` dengan `aria-label="Ke slide X"` dan `aria-current` saat aktif.

* Pastikan kontras overlay (tambahkan gradient scrim ringan).

<br />

## 4) Deliverables

* Komponen TS: `BannerCarousel.tsx`.

* Setup TS minimal: `tsconfig.json` + typings.

* Update HomePage agar memakai komponen baru.

* Dokumentasi singkat penggunaan props (README kecil di `docs/style-guide-ui.md` bagian banner).

Jika Anda setuju, saya lanjut implementasi sesuai rencana ini (termasuk penambahan TypeScript setup dan dependency autoplay).
