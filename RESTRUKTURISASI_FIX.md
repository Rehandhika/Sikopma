# RENCANA RESTRUKTURISASI FINAL: PROJECT DEPLOY SIKOPMA
**Status Dokumen:** FINAL PLAN (Gabungan Komprehensif)
**Tanggal**: 21 Februari 2026
**Target Arsitektur**: Modular Domain-Driven (Service-Action-Enum-DTO)

---

## 1. Ringkasan Eksekutif
Dokumen ini adalah cetak biru final untuk mentransformasi kode "DEPLOY SIKOPMA". Rencana ini menggabungkan analisis struktural mendalam (dari `RESTRUKTURISASI.md`) dengan perbaikan arsitektur fundamental (dari `RESTRUKTURISASI2.md`).

**Tujuan Akhir:**
1.  **Zero "Vibe Coding":** Menghapus semua kode mati, stub kosong, dan file non-standar.
2.  **Explicit Naming:** Mengubah nama file generik (`Index.php`, `Pos.php`) menjadi deskriptif (`AttendanceList.php`, `PointOfSale.php`).
3.  **Separation of Concerns:** Memisahkan Logika Bisnis (Actions/Services) dari UI (Livewire) dan Data (Models).
4.  **Type Safety:** Mengganti *magic strings* dengan Enums dan array liar dengan DTOs.

---

## 2. Struktur Direktori Baru (Target)

Kita akan mengadopsi struktur hibrida modern Laravel:

```text
app/
├── Actions/                 # [BARU] Single Responsibility Business Logic
│   ├── Auth/
│   ├── Sales/               # ProcessTransactionAction, CalculateChangeAction
│   ├── Inventory/           # AdjustStockAction
│   └── Shu/                 # AwardPointsAction (ex ShuPointService)
├── Console/
│   └── Commands/            # [CLEANUP] Hanya command aktif
├── Data/                    # [BARU] Data Transfer Objects (DTO)
│   ├── Sales/               # CartItemData, TransactionData
│   └── Reports/
├── Enums/                   # [BARU] Pengganti Hardcoded Strings
│   ├── UserRole.php
│   ├── TransactionType.php
│   └── OrderStatus.php
├── Http/
│   ├── Controllers/         # Thin Controllers (API & Auth)
│   │   ├── Api/
│   │   └── Auth/
│   ├── Requests/            # Form Validation
│   └── Resources/           # API Transformers
├── Livewire/                # UI Logic Only (View Models)
│   ├── Admin/
│   ├── Attendance/          # AttendanceList, AttendanceHistory (Bukan Index/History)
│   ├── Cashier/             # PointOfSale (Bukan Pos)
│   └── ... (Lihat Tabel Renaming)
├── Models/                  # Thin Models (Relations & Casts Only)
├── Services/                # Orchestration Layer (Shared Logic)
│   ├── ReportService.php
│   └── SystemService.php
└── Support/                 # Helpers, Traits
```

---

## 3. Rencana Eksekusi Bertahap

### FASE 0: THE PURGE (Pembersihan Total)
*Fokus: Menghapus "noise" sebelum menyentuh logika inti.*

1.  **Hapus Direktori/File Non-Standar:**
    -   `hapus` folder `-p/`
    -   `hapus` file `CHEATSHEET.txt` (Pindahkan isi penting ke `docs/`)
    -   `hapus` file `PERMISSIONREPORT.md` (Pindahkan ke `docs/`)
    -   `hapus` file `PANDUAN.md` (Pindahkan ke `docs/`)

2.  **Hapus Kode Mati & Stub Kosong (Dikonfirmasi Aman):**
    -   `app/Livewire/Schedule/AvailabilityInput.php` (Stub 250 bytes)
    -   `app/Livewire/Purchase/PurchaseList.php` (Stub 240 bytes)
    -   `app/Livewire/User/UserManagement.php` (Stub 236 bytes)
    -   `app/Console/Commands/CheckMissedSchedules.php` (Redundan/Duplikat)
    -   `app/Console/Commands/AutoGenerateSchedule.php` (Tidak terpakai)
    -   `app/Console/Commands/ResetPenaltyPoints.php` (Tidak terpakai)
    -   `app/Console/Commands/SendAvailabilityReminder.php` (Tidak terpakai)

3.  **Hapus File Sampah di Production:**
    -   `app/Livewire/Schedule/TestAvailability.php`
    -   `resources/views/layouts/app-layout-test.blade.php`
    -   `resources/views/public/test.blade.php`
    -   `resources/views/livewire/attendance/REFACTORING-SUMMARY.md`

4.  **Hapus Migrasi Bermasalah:**
    -   `2025_11_08_015823_add_missing_status_column_to_users_table.php` (Body kosong)
    -   `2025_11_08_015938_fix_missing_status_column_in_users_table.php` (Body kosong)

### FASE 1: FONDASI ARSITEKTUR
*Fokus: Menyiapkan wadah untuk kode yang akan dipindah.*

1.  **Setup Enums:**
    -   Buat `app/Enums/UserRole.php` (Super Admin, Admin, dll).
    -   Buat `app/Enums/ShuTransactionType.php` (Earn, Redeem).
    -   Buat `app/Enums/AttendanceStatus.php`.

2.  **Setup DTO:**
    -   Install `spatie/laravel-data` (Opsional) atau buat Base DTO class.

3.  **Refactor Config & Routes:**
    -   Rename `config/siwirus.php` -> `config/app-settings.php`.
    -   **Split Routes:** Pecah `web.php` menjadi:
        -   `routes/admin.php` (Dashboard, Management)
        -   `routes/public.php` (Landing, Catalog)
        -   `routes/auth.php` (Login, Logout)
    -   Hapus rute redirect legacy yang memenuhi `web.php`.

### FASE 2: DATABASE & TECHNICAL DEBT FIX
*Fokus: Memperbaiki kesalahan desain database.*

1.  **Fix Kolom `percentage_bps`:**
    -   Buat migrasi: `RenamePercentageBpsToConversionRate`.
    -   Update `ShuPointService` (atau `AwardPointsAction` nanti) untuk menggunakan nama kolom baru.
    -   *Justifikasi:* Nama kolom `percentage_bps` untuk menyimpan nilai Rupiah adalah hutang teknis yang berbahaya.

### FASE 3: RENAMING & REORGANISASI LIVEWIRE
*Fokus: Mengubah nama generik menjadi spesifik (Semantic Naming).*

| Path Lama | Path Baru (Target) |
| :--- | :--- |
| `App\Livewire\Attendance\Index` | `App\Livewire\Attendance\AttendanceList` |
| `App\Livewire\Attendance\History` | `App\Livewire\Attendance\AttendanceHistory` |
| `App\Livewire\Cashier\Pos` | `App\Livewire\Cashier\PointOfSale` |
| `App\Livewire\Leave\Index` | `App\Livewire\Leave\LeaveRequestList` |
| `App\Livewire\Leave\CreateRequest` | `App\Livewire\Leave\CreateLeaveRequest` |
| `App\Livewire\Leave\MyRequests` | `App\Livewire\Leave\UserLeaveRequests` |
| `App\Livewire\Penalty\Index` | `App\Livewire\Penalty\PenaltyList` |
| `App\Livewire\Penalty\MyPenalties` | `App\Livewire\Penalty\UserPenalties` |
| `App\Livewire\Schedule\Index` | `App\Livewire\Schedule\ScheduleList` |
| `App\Livewire\Schedule\MySchedule` | `App\Livewire\Schedule\UserSchedule` |
| `App\Livewire\Swap\Index` | `App\Livewire\Swap\SwapRequestList` |
| `App\Livewire\User\Index` | `App\Livewire\User\UserList` |

*Catatan: Rename juga file Blade view pasangannya.*

### FASE 4: LOGIC EXTRACTION (Refactoring Berat)
*Fokus: Memindahkan logika dari Controller/Livewire/Model ke Actions.*

1.  **Extract `PointOfSale.php` (Ex-Pos.php):**
    -   Logika `processPayment()` dipindah ke `App\Actions\Sales\ProcessTransactionAction`.
    -   Logika kalkulasi cart dipindah ke `App\Services\CartCalculator`.
    -   Livewire hanya bertugas menerima input dan memanggil Action.

2.  **Model Diet (User & Sale):**
    -   Pindahkan `User::getDashboardRoute()` ke `RouteService`.
    -   Pindahkan `Sale::calculateTotal()` ke `SaleService`.
    -   Pastikan Model murni hanya definisi relasi dan casting.

3.  **Fix `ShuPointService`:**
    -   Pecah menjadi `AwardPointsAction` dan `RedeemPointsAction`.
    -   Gunakan `ShuTransactionType` Enum.
    -   Gunakan kolom database baru (`conversion_rate`).

---

## 4. Checklist Pra-Eksekusi

Sebelum saya memulai eksekusi langkah demi langkah:

- [ ] **Backup Database:** Pastikan snapshot database diambil.
- [ ] **Git Branch:** Saya akan bekerja di branch saat ini, pastikan bersih.
- [ ] **Persetujuan:** Anda menyetujui penghapusan file-file yang tercantum di Fase 0.

## 5. Instruksi Selanjutnya

Silakan ketik:
- **"Lanjut Fase 0"**: Untuk memulai pembersihan kode mati dan file sampah.
- **"Lanjut Fase 1"**: Setelah Fase 0 selesai, untuk memulai setup arsitektur.
