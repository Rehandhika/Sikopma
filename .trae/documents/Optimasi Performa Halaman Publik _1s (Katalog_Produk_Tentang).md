## Target 1s: Realistisnya Apa

* 1s sangat mungkin untuk **warm cache** (kunjungan kedua) dan untuk jaringan cepat.

* Untuk **cold start**, 1s biasanya butuh kombinasi: payload JS kecil, caching HTTP agresif, dan mengurangi request setelah load (atau server-side inject data awal).

## Temuan Utama (Bottleneck)

* Halaman publik saat ini **full reload** antar route (Laravel routing), dan setiap load melakukan beberapa fetch API (banners/categories/products/about/product). Ini menambah latency.

* Entry publik [main.jsx](file:///c:/laragon/www/Kopma/resources/js/react/main.jsx) mengimpor page secara eager sehingga **kode Katalog+Produk+Tentang+Carousel ikut masuk bundle awal**.

* Backend sudah pakai Laravel Cache TTL 300s untuk endpoint public, tapi browser tetap request ulang karena belum ada strategi HTTP caching.

* StoreStatus polling 10s berjalan terus di navbar.

## Rencana Implementasi

## 1) Ukur Baseline dan Tetapkan Budget

* Tambahkan pengukuran sederhana:

  * TTFB (server), FCP/LCP/INP (client) untuk `/`, `/about`, `/products/{slug}`.

  * Build report ukuran chunk (untuk memastikan bundle splitting benar-benar menurunkan initial JS).

* Target budget (awal): initial JS untuk tiap page publik 150KB gzip (ideal), request API maksimal 1 setelah first paint.

## 2) Kurangi Initial JS dengan Code Splitting yang Agresif

* Ubah [main.jsx](file:///c:/laragon/www/Kopma/resources/js/react/main.jsx) agar setiap page di-load **lazy** berdasarkan `data-page`:

  * `React.lazy(() => import('./pages/HomePage'))`, dst + `Suspense` fallback minimal.

  * Dampak: Katalog tidak perlu memuat code Tentang/Detail produk.

* Pecah fitur berat di Katalog:

  * Lazy-load `BannerCarousel` (Embla) hanya saat banner ada dan HomePage aktif.

* Evaluasi `manualChunks` untuk publik:

  * Pisahkan `embla-carousel-*` dan `@radix-ui/*` ke chunk terpisah (agar cache antar halaman bagus, dan initial load lebih kecil per page).

## 3) Pangkas Request Setelah Load (Big Win untuk 1s)

Pilihan yang paling cepat hasilnya:

* **Inject data awal dari server ke Blade** untuk halaman yang sering diakses:

  * Katalog: `banners + categories + products page=1` disediakan di HTML (dataset/script tag JSON) sehingga React render langsung tanpa 23 roundtrip.

  * Tentang: `about` data disediakan dari server.

  * Produk detail: `product + about` disediakan dari server.

* Karena backend sudah cache 300s, inject ini bisa sangat ringan di server.

## 4) HTTP Caching untuk API Public (Agar Reload Cepat)

* Tambahkan header untuk endpoint `/api/public/*` (yang sudah cached):

  * `Cache-Control: public, max-age=60, stale-while-revalidate=300` (atau sesuai kebutuhan)

  * `ETag` / `Last-Modified` (agar 304 Not Modified saat reload)

* Ini langsung meningkatkan performa navigasi full reload (karena browser bisa re-use response).

## 5) Optimasi Gambar dan Rendering

* Banner sudah lazy-load + skeleton, lanjutkan:

  * Pastikan banner menggunakan `aspect-[16/9]` (sudah).

* Produk grid:

  * Pastikan gambar produk memakai `loading="lazy"`, `decoding="async"`, dan size yang sesuai (hindari download gambar besar di grid).

  * Pertimbangkan `srcset` untuk thumbnail bila backend menyediakan.

## 6) Kurangi Beban Polling Store Status

* Ubah strategi `StoreStatusPopover`:

  * Polling hanya saat popover dibuka, atau

  * Pause saat tab hidden (`document.visibilityState`), atau

  * Turunkan frekuensi (mis. 30s) + `requestIdleCallback` untuk refresh.

* Tujuan: mengurangi request background yang bisa mengganggu LCP/INP.

##

## Deliverables yang Akan Dihasilkan

* Page-level lazy loading + suspense fallback.

* Server-injected initial data untuk katalog/produk/tentang.

* HTTP caching headers untuk endpoint public.

* Store-status polling yang lebih hemat.

* Laporan ringkas sebelum/sesudah: ukuran bundle + metrik (FCP/LCP/TTFB).

