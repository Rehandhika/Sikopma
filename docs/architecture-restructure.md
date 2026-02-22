# Dokumentasi Restrukturisasi Arsitektur

Dokumen ini menjelaskan perubahan arsitektur utama yang dilakukan untuk meningkatkan modularitas, skalabilitas, dan kemudahan maintenance proyek SIKOPMA.

## 1. Analisis Awal (Technical Debt)

### Masalah Utama
*   **Logika Ganda (Duplikasi):** Perhitungan poin SHU terdapat di `PosEntry` (Livewire) dan `AwardPointsAction` (Action).
*   **Fat Livewire Component:** `PosEntry` mengurus semua logika database, validasi, dan transaksi, menjadikannya sulit di-test.
*   **Inkonsistensi Service:** Service `ShuPointService` tidak digunakan secara konsisten oleh Livewire.
*   **Kurangnya Otomasi:** Tidak ada scheduler untuk membersihkan data lama.

## 2. Rencana Restrukturisasi

### Fase 1: Standardisasi Layer (Completed)
*   **Livewire:** Fokus UI State & Validasi Input.
*   **Actions:** Menangani Write Operations (Create, Update, Delete).
*   **Services:** Menangani Business Rules & Read Operations.

### Implementasi Perubahan
1.  **Refactoring POS (`PosEntry`):**
    *   Memindahkan logika batch insert dari `PosEntry::processBatchInsert` ke `App\Actions\Sales\ProcessBatchTransactionsAction`.
    *   Livewire kini hanya memvalidasi input dan memanggil Action.

2.  **Unifikasi Logika SHU (`ShuPointService`):**
    *   `AwardPointsAction` diperbarui untuk menggunakan rumus perhitungan dari `ShuPointService::computeEarnedPoints`.
    *   Menghapus duplikasi logika perhitungan poin.

3.  **Scheduler (`routes/console.php`):**
    *   Menambahkan jadwal backup database harian.
    *   Menambahkan jadwal pembersihan data lama (Activity Log > 6 bulan, Notification > 3 bulan).

## 3. Struktur Folder Baru

```
app/
├── Actions/
│   ├── Sales/
│   │   ├── ProcessTransactionAction.php (Single Trx)
│   │   └── ProcessBatchTransactionsAction.php (Bulk Trx - NEW)
│   └── Shu/
│       └── AwardPointsAction.php (Updated)
├── Livewire/
│   └── Cashier/
│       └── PosEntry.php (Refactored - Thin UI)
├── Services/
│   └── ShuPointService.php (Single Source of Truth)
```

## 4. Panduan Pengembangan Masa Depan

### Menambah Fitur Baru
1.  **Buat Action:** Selalu bungkus logika perubahan data (create/update) dalam Action class.
2.  **Gunakan Service:** Jangan tulis ulang logika bisnis (misal: hitung diskon) di Controller/Livewire. Panggil Service.
3.  **Test:** Buat Unit Test untuk Action dan Feature Test untuk Livewire.

### Maintenance
*   Setiap migrasi database baru harus mempertimbangkan performa (index) dan kebutuhan backup.
*   Gunakan `ProcessBatchTransactionsAction` untuk impor data massal, jangan gunakan loop di Controller.
