# Standard Operating Procedure (SOP) Produksi

Dokumen ini menjelaskan prosedur standar untuk pemeliharaan, backup, deployment, dan monitoring sistem SIKOPMA di lingkungan produksi.

## 1. Strategi Backup & Recovery

### Konfigurasi Backup
*   **Tools:** `spatie/laravel-backup`
*   **Jadwal (RPO - Recovery Point Objective):**
    *   **Database:** Harian pukul 01:30 WIB (Full Backup).
    *   **File Storage:** Harian pukul 01:00 WIB (Incremental/Sync).
*   **Retensi:**
    *   Harian: 7 hari terakhir.
    *   Mingguan: 4 minggu terakhir.
    *   Bulanan: 6 bulan terakhir.
*   **Lokasi:**
    *   Lokal: `storage/app/Laravel` (untuk restore cepat).
    *   Cloud (Wajib): S3 / Google Drive (untuk bencana fisik server).

### Prosedur Recovery (RTO - Recovery Time Objective < 2 Jam)
1.  **Persiapan:** Pastikan server baru memiliki PHP, Composer, dan MySQL sesuai `composer.json`.
2.  **Restore Database:**
    ```bash
    # Unzip backup terbaru
    unzip backup-202X-XX-XX.zip
    
    # Import SQL
    mysql -u root -p sikopma_db < db-dump.sql
    ```
3.  **Restore Files:**
    Copy folder `storage/app/public` dari backup ke server baru.
    Jalankan `php artisan storage:link`.
4.  **Verifikasi:**
    Login sebagai admin dan cek data transaksi terakhir.

## 2. Manajemen Sumber Daya

### Storage Monitoring
*   **Threshold:** Peringatan jika disk usage > 80%.
*   **Tindakan:**
    *   Cek folder `storage/logs` dan hapus log lama (`*.log`).
    *   Jalankan `php artisan queue:flush` jika antrian macet.
    *   Hapus file temp Livewire: `rm -rf storage/app/livewire-tmp/*`.

### Database Optimization
*   **Jadwal:** Otomatis setiap hari pukul 02:00 WIB via Scheduler.
*   **Pembersihan Manual:**
    ```bash
    # Hapus Activity Log > 6 bulan
    php artisan Tinker --execute="App\Models\ActivityLog::where('created_at', '<', now()->subMonths(6))->delete();"
    ```

## 3. Prosedur Update & Deployment

### Langkah Deployment (Zero Downtime Strategy)
1.  **Maintenance Mode (Opsional):**
    `php artisan down --secret="dev-ops"`

2.  **Pull Code:**
    `git pull origin main`

3.  **Install Dependencies:**
    `composer install --no-dev --optimize-autoloader`
    `npm ci && npm run build`

4.  **Database Migration:**
    `php artisan migrate --force`
    *Note: Pastikan backup database SEBELUM migrasi.*

5.  **Optimization:**
    `php artisan optimize`
    `php artisan view:cache`
    `php artisan event:cache`
    `php artisan config:cache`

6.  **Restart Services:**
    `php artisan queue:restart`
    `supervisorctl restart all` (jika pakai supervisor)

7.  **Live:**
    `php artisan up`

### Rollback Strategy
Jika terjadi critical bug setelah deploy:
1.  **Revert Code:**
    `git checkout vPrev.Tag` atau `git reset --hard HEAD~1`
2.  **Rollback Database (Hati-hati):**
    `php artisan migrate:rollback --step=1` (Hanya jika migrasi menyebabkan error)
3.  **Re-optimize:**
    `composer install` dan `php artisan optimize`.

## 4. Monitoring & Alerting

*   **App Logs:** Cek `storage/logs/laravel.log` setiap hari.
*   **Queue Worker:** Pastikan process `queue:work` berjalan via Supervisor/PM2.
*   **Scheduler:** Pastikan cron job berjalan: `* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1`.

---
**Kontak Darurat:**
*   DevOps Lead: [Nama/Email]
*   Backend Lead: [Nama/Email]
