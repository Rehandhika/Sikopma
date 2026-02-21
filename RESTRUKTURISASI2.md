# Rencana Restrukturisasi Proyek DEPLOY SIKOPMA

Dokumen ini berisi proposal restrukturisasi menyeluruh untuk proyek DEPLOY SIKOPMA. Tujuannya adalah mentransformasi basis kode dari gaya "vibe coding" (tidak terstruktur) menjadi arsitektur tingkat industri yang profesional, maintainable, dan scalable.

## 1. Analisis Situasi Saat Ini

Berdasarkan pemindaian mendalam terhadap codebase, ditemukan beberapa isu kritikal:

*   **Fat Livewire Components:** Logika bisnis kompleks (transaksi POS, perhitungan denda) tertanam langsung di komponen UI (`app/Livewire/Cashier/Pos.php` memiliki >600 baris).
*   **Model Pollution:** Model Eloquent (`User`, `Sale`) mengandung logika bisnis, routing, dan side-effects yang seharusnya ada di Service/Action layer.
*   **Monolithic Routes:** File `routes/web.php` mencampur aduk rute admin, publik, API, dan redirect legacy, membuatnya sulit dipelihara.
*   **Technical Debt & Naming Violations:** Penggunaan kolom database yang tidak semestinya (contoh: kolom `percentage_bps` dipakai untuk menyimpan nominal konversi Rupiah) dengan alasan menghindari migrasi.
*   **Magic Strings & Lack of Type Safety:** Penggunaan string literal ('earn', 'redeem', 'admin') alih-alih Enums, serta kurangnya penggunaan DTO (Data Transfer Objects).

## 2. Arsitektur Target: Domain-Oriented Modular

Kita akan mengadopsi pendekatan **Service-Action-Enum-DTO** yang memisahkan *concern* dengan tegas.

### Struktur Direktori Baru

```text
app/
├── Actions/                 # Single Responsibility Classes (Business Logic)
│   ├── Auth/
│   ├── Sales/               # ProcessSaleAction, CalculateChangeAction
│   ├── Inventory/           # AdjustStockAction
│   └── Shu/                 # AwardPointsAction, RedeemPointsAction
├── Data/                    # Data Transfer Objects (DTOs)
│   ├── Sales/               # SaleData, CartItemData
│   └── Reports/
├── Enums/                   # PHP 8.1+ Enums
│   ├── UserRole.php         # Gantikan string 'admin', 'staff'
│   ├── TransactionType.php  # Gantikan 'earn', 'redeem'
│   └── OrderStatus.php
├── Http/
│   ├── Controllers/         # Thin Controllers (hanya untuk HTTP layer)
│   │   ├── Admin/
│   │   ├── Public/
│   │   └── Api/
│   ├── Requests/            # FormRequests untuk validasi
│   └── Resources/           # API Resources
├── Livewire/                # UI Logic Only (View Models)
│   └── ...
├── Models/                  # Thin Models (Relations, Scopes, Casts only)
├── Services/                # Orchestration Layer (jika Action terlalu granular)
└── Support/                 # Helpers, Traits, Value Objects
```

## 3. Rencana Eksekusi Bertahap

### Fase 0: Pembersihan Kode Mati & Redundan (Cleanup)
Sebelum memulai restrukturisasi fitur, kita akan membersihkan file sampah untuk mengurangi *noise*.

*   **Hapus Migrasi Kosong/Duplikat:**
    *   `database/migrations/2025_11_08_015823_add_missing_status_column_to_users_table.php` (Kosong)
    *   `database/migrations/2025_11_08_015938_fix_missing_status_column_in_users_table.php` (Kosong)
*   **Hapus Command Redundan:**
    *   `app/Console/Commands/CheckMissedSchedules.php` (Duplikasi logika dari `ProcessAbsencesJob` yang lebih robust).
*   **Hapus Test Boilerplate:**
    *   `tests/Feature/ExampleTest.php`
    *   `tests/Unit/ExampleTest.php`
*   **Renaming & Refactoring:**
    *   `app/Console/Commands/ProcessAbsencesJob.php` → `ProcessAbsencesCommand.php` (Naming convention fix).
    *   Pindahkan logika migrasi data (`2026_02_14_000000_reset_permissions.php`) ke `DatabaseSeeder`.

### Fase 1: Fondasi & Standarisasi (Foundation)
*   **Enums:** Membuat Enum untuk semua status dan peran yang hardcoded.
*   **DTO Setup:** Menginstall `spatie/laravel-data` atau membuat base DTO class untuk transfer data yang aman antar layer.
*   **Route Splitting:** Memecah `web.php` menjadi:
    *   `routes/admin.php` (Dashboard & Manajemen)
    *   `routes/public.php` (Landing page & Katalog)
    *   `routes/auth.php` (Login/Logout)
    *   `routes/legacy.php` (Redirects)

### Fase 2: Perbaikan Database & Technical Debt (Cleanup)
*   **Schema Correction:** Membuat migrasi untuk memperbaiki kolom yang salah nama.
    *   `shu_point_transactions`: Rename `percentage_bps` -> `conversion_rate_amount`.
    *   Standarisasi nama kolom menjadi full English (snake_case).
*   **Model Cleanup:** Menghapus logic dari Model. Memindahkan logic seperti `calculateTotal()` dari model `Sale` ke `PriceCalculatorService`.

### Fase 3: Refactoring Logika Bisnis (Refactor)
*   **Ekstraksi POS Logic:** Memecah `Pos.php` Livewire component.
    *   Logika pembayaran -> `App\Actions\Sales\ProcessTransactionAction`.
    *   Logika keranjang -> `App\Services\CartService`.
*   **Service Refactoring:** Menulis ulang `ShuPointService` menggunakan Enums dan kolom DB yang benar.

### Fase 4: Quality Assurance (QA)
*   **Type Hinting:** Menambahkan strict type declarations (`declare(strict_types=1);`) di semua file baru.
*   **Testing:** Menambahkan Feature Test untuk flow kritis (Checkout, Penukaran Poin).

## 4. Peta Perubahan Struktur (Mapping)

| Komponen Lama | Lokasi Baru / Pengganti | Keterangan |
| :--- | :--- | :--- |
| `app/Livewire/Cashier/Pos.php` (Logic) | `app/Actions/Sales/ProcessSaleAction.php` | Logic dipisah dari UI |
| `app/Models/Sale.php` (Logic) | `app/Services/Sales/SaleService.php` | Model hanya untuk Eloquent |
| `app/Services/ShuPointService.php` | `app/Actions/Shu/AwardPointsAction.php` | Dipecah menjadi single-action classes |
| `routes/web.php` (Admin Group) | `routes/admin.php` | File route terpisah |
| String `'earn'`, `'redeem'` | `app/Enums/ShuTransactionType.php` | Type safety |
| Column `percentage_bps` | Column `conversion_rate` | Perbaikan nama kolom DB |
| `ProcessAbsencesJob.php` | `ProcessAbsencesCommand.php` | Fix Naming Convention |

## 5. Instruksi Selanjutnya

Jika rencana ini disetujui, saya akan memulai eksekusi dari **Fase 0** (Cleanup) kemudian berlanjut ke **Fase 1** (Fondasi) secara berurutan.

Mohon konfirmasi untuk memulai eksekusi.
