# 🚀 Panduan Lengkap Migrasi Database Production SIKOPMA

**Tanggal Backup Production:** 5 April 2026  
**Estimasi Waktu:** 30-60 menit  
**Status:** Ready to Deploy

---

## 📋 DAFTAR ISI

1. [Ringkasan Perubahan](#ringkasan-perubahan)
2. [Peringatan Penting](#peringatan-penting)
3. [Langkah-Langkah Migrasi](#langkah-langkah-migrasi)
4. [Daftar Command](#daftar-command)
5. [Validasi](#validasi)
6. [Rollback](#rollback)
7. [Troubleshooting](#troubleshooting)

---

## 📊 RINGKASAN PERUBAHAN

### ✅ Tabel Baru (3)
1. **students** - Data mahasiswa untuk sistem SHU Point
2. **shu_point_transactions** - Transaksi poin SHU mahasiswa
3. **attendances_photo_backup** - Backup path foto (untuk audit trail)

### 🔄 Tabel yang Diubah (6)

#### 1. attendances
- ➕ `late_minutes` (int) - Menit keterlambatan
- ➕ `late_category` (varchar) - Kategori A/B/C
- ➕ `deleted_at` (timestamp) - Soft delete
- ❌ `check_in_photo` - **DIHAPUS PERMANEN**

#### 2. sales
- ➕ `student_id` - Link ke mahasiswa
- ➕ `shu_points_earned` - Poin yang didapat
- ➕ `shu_percentage_bps` - Persentase konversi
- ➕ `deleted_at` - Soft delete

#### 3. activity_logs
- ➕ `metadata` (JSON) - Data tambahan

#### 4. purchase_items
- ➕ `product_variant_id` - Link ke varian produk

#### 5. schedule_assignments
- ➕ Status baru: `in_progress`

#### 6. schedule_change_requests
- ➕ `target_id` - User target
- ➕ `target_assignment_id` - Assignment target

### 📊 Data yang Terpengaruh
- 📸 **483 foto** attendance (PATH di-backup, FILE DIHAPUS PERMANEN)
- 📊 **714 records** attendance
- 💰 **2 records** sales
- 👥 **14 users**
- 📦 **262 products**

---

## ⚠️ PERINGATAN PENTING

### 🔴 FOTO CHECK-IN AKAN DIHAPUS PERMANEN!

**Yang Akan Terjadi:**
1. ❌ Kolom `check_in_photo` di tabel `attendances` akan **DIHAPUS**
2. ❌ **SEMUA FILE FOTO** (483 files) di `storage/app/public/attendance/` akan **DIHAPUS PERMANEN**
3. ❌ Folder `attendance/` akan dibersihkan
4. ✅ Path foto di-backup ke tabel `attendances_photo_backup` (untuk audit trail)
5. ❌ **TIDAK BISA DI-RESTORE** setelah migrasi

**Alasan:**
- Fitur foto check-in tidak akan digunakan lagi
- Mengurangi ukuran storage (~50-100 MB)
- Fokus ke late tracking (minutes & category)

**Jika Ingin Simpan Foto (Optional):**
```bash
# Backup manual sebelum migrasi
tar -czf attendance_photos_backup_$(date +%Y%m%d).tar.gz storage/app/public/attendance/
# Simpan di tempat aman (external drive, cloud)
```

### 🔴 Checklist Sebelum Lanjut
- [ ] Pahami bahwa foto akan dihapus permanen
- [ ] Backup foto terpisah jika diperlukan (optional)
- [ ] Koordinasi dengan tim
- [ ] Test di development dulu
- [ ] Yakin tidak akan butuh foto lama

---

## 🚀 LANGKAH-LANGKAH MIGRASI

### FASE 1: PERSIAPAN (1 Hari Sebelum)

#### 1. Backup Database (WAJIB!)
```bash
# Via command line
mysqldump -u username -p wirus > backup_production_$(date +%Y%m%d_%H%M%S).sql

# Atau via phpMyAdmin: Export > SQL > Save
```

#### 2. Backup Storage (WAJIB!)
```bash
# Backup folder storage
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/public/attendance/
```

#### 3. Verifikasi Backup
```bash
# Cek ukuran file
ls -lh backup_production_*.sql
ls -lh storage_backup_*.tar.gz

# Pastikan tidak kosong (minimal beberapa MB)
```

#### 4. Upload File ke Server
- Upload `migrate-production.sh`
- Upload `rollback-migration.sh`
- Upload kode aplikasi terbaru (via git/FTP)

---

### FASE 2: MIGRASI (Hari H)

#### Opsi A: OTOMATIS (Recommended) ⭐

```bash
# 1. Set permission
chmod +x migrate-production.sh
chmod +x rollback-migration.sh

# 2. Jalankan migrasi
bash migrate-production.sh

# Script akan otomatis:
# - Backup database & storage
# - Set maintenance mode
# - Install dependencies
# - Jalankan migrasi database
# - Backup & hapus foto attendance
# - Migrasi data
# - Validasi hasil
# - Clear & optimize cache
# - Remove maintenance mode
```

#### Opsi B: MANUAL (Jika Script Gagal)

```bash
# 1. Maintenance Mode
php artisan down --message="Migrasi database sedang berlangsung" --retry=60

# 2. Update Kode
git pull origin main
composer install --no-dev --optimize-autoloader

# 3. Migrasi Tabel Baru
php artisan migrate --path=database/migrations/2026_01_31_000001_create_students_table.php --force
php artisan migrate --path=database/migrations/2026_01_31_000002_create_shu_point_transactions_table.php --force
php artisan migrate --path=database/migrations/2026_01_31_000003_add_shu_fields_to_sales_table.php --force

# 4. Migrasi Kolom Baru
php artisan migrate --path=database/migrations/2026_02_24_142246_add_late_details_to_attendances_table.php --force
php artisan migrate --path=database/migrations/2026_02_22_150001_add_soft_deletes_to_transactional_tables.php --force
php artisan migrate --path=database/migrations/2026_02_24_150000_add_metadata_to_activity_logs_table.php --force
php artisan migrate --path=database/migrations/2026_02_12_223106_add_product_variant_id_to_purchase_items_table.php --force
php artisan migrate --path=database/migrations/2026_02_24_142157_add_in_progress_status_to_schedule_assignments_table.php --force
php artisan migrate --path=database/migrations/2026_02_21_230000_add_target_columns_to_schedule_change_requests.php --force

# 5. Migrasi Data (PENTING: Ini akan hapus foto!)
php artisan db:seed --class=MigrateProductionDataSeeder --force
php artisan db:seed --class=MigrateSalesForShuSeeder --force
php artisan db:seed --class=MigrateAttendanceDataSeeder --force

# 6. Validasi
php artisan db:seed --class=ValidationSeeder --force

# 7. Clear Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 8. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Remove Maintenance Mode
php artisan up
```

---

### FASE 3: VALIDASI

#### 1. Validasi Database
```sql
-- Login ke MySQL
mysql -u username -p wirus

-- Cek tabel baru
SHOW TABLES LIKE 'students';
SHOW TABLES LIKE 'shu_point_transactions';
SHOW TABLES LIKE 'attendances_photo_backup';

-- Cek struktur tabel
DESCRIBE attendances;  -- check_in_photo harus TIDAK ADA
DESCRIBE sales;

-- Cek jumlah data
SELECT 'users' as tabel, COUNT(*) as jumlah FROM users
UNION ALL SELECT 'attendances', COUNT(*) FROM attendances
UNION ALL SELECT 'sales', COUNT(*) FROM sales
UNION ALL SELECT 'products', COUNT(*) FROM products;

-- Expected:
-- users: 14
-- attendances: 714
-- sales: 2
-- products: 262

-- Cek backup path foto
SELECT COUNT(*) FROM attendances_photo_backup;
-- Expected: 483

exit;
```

#### 2. Validasi Storage
```bash
# Folder attendance harus kosong atau tidak ada
ls -la storage/app/public/attendance/
# Expected: No such file or directory

# Cek ukuran storage (harus berkurang)
du -sh storage/app/public/
```

#### 3. Validasi Aplikasi
```bash
# Test di browser:
# 1. Login sebagai admin
# 2. Cek dashboard
# 3. Cek halaman attendance (tanpa foto)
# 4. Cek halaman sales
# 5. Test create/update data

# Monitor error log
tail -f storage/logs/laravel.log
```

#### 4. Validasi Otomatis
```bash
php artisan db:seed --class=ValidationSeeder --force
```

---

## 📝 DAFTAR COMMAND

### Backup
```bash
# Database
mysqldump -u username -p wirus > backup_$(date +%Y%m%d).sql

# Storage
tar -czf storage_backup.tar.gz storage/app/public/attendance/

# Verifikasi
ls -lh backup_*.sql storage_backup.tar.gz
```

### Migrasi
```bash
# Otomatis
chmod +x migrate-production.sh
bash migrate-production.sh

# Manual - lihat Fase 2 Opsi B di atas
```

### Validasi
```bash
# Otomatis
php artisan db:seed --class=ValidationSeeder --force

# Manual - lihat Fase 3 di atas
```

### Monitoring
```bash
# Real-time log
tail -f storage/logs/laravel.log

# Last 100 lines
tail -n 100 storage/logs/laravel.log

# Search errors
grep -i "error" storage/logs/laravel.log
```

### Cache Management
```bash
# Clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Maintenance Mode
```bash
# Enable
php artisan down --message="Maintenance" --retry=60

# Disable
php artisan up
```

---

## 🚨 ROLLBACK (Emergency)

### Rollback Otomatis
```bash
bash rollback-migration.sh backups/backup_production_[timestamp].sql
```

### Rollback Manual
```bash
# 1. Maintenance Mode
php artisan down

# 2. Drop & Create Database
mysql -u username -p -e "DROP DATABASE wirus;"
mysql -u username -p -e "CREATE DATABASE wirus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. Restore Backup
mysql -u username -p wirus < backup_production_[timestamp].sql

# 4. Restore Kode (jika perlu)
git checkout [commit_sebelumnya]
composer install --no-dev

# 5. Clear Cache
php artisan cache:clear
php artisan config:clear

# 6. Remove Maintenance Mode
php artisan up
```

**⚠️ CATATAN:** Rollback database akan mengembalikan kolom `check_in_photo`, tapi **FILE FOTO TETAP TERHAPUS** dan tidak bisa dikembalikan!

---

## 🔧 TROUBLESHOOTING

### Problem 1: Foreign Key Error
```sql
SET FOREIGN_KEY_CHECKS=0;
-- Jalankan migrasi
SET FOREIGN_KEY_CHECKS=1;
```

### Problem 2: Column Already Exists
```bash
# Cek status migrasi
php artisan migrate:status

# Skip yang sudah jalan, lanjut berikutnya
```

### Problem 3: Permission Denied
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Problem 4: Composer Error
```bash
composer clear-cache
composer install --no-dev --optimize-autoloader
```

### Problem 5: Database Connection Error
```bash
# Cek .env
cat .env | grep DB_

# Test koneksi
php artisan db:show
```

### Problem 6: File Foto Tidak Terhapus
```bash
# Hapus manual
rm -rf storage/app/public/attendance/*

# Atau via PHP
php artisan tinker
>>> Storage::disk('public')->deleteDirectory('attendance');
>>> exit
```

---

## ✅ CHECKLIST LENGKAP

### Sebelum Migrasi (1 Hari Sebelum)
- [ ] Baca panduan ini sampai selesai
- [ ] **PAHAMI bahwa foto akan dihapus permanen**
- [ ] Test migrasi di development/staging
- [ ] Backup database production
- [ ] Backup folder storage
- [ ] Backup foto terpisah (optional)
- [ ] Simpan backup di 2 tempat berbeda
- [ ] Upload file ke server
- [ ] Set permission script
- [ ] Koordinasi dengan tim
- [ ] Tentukan jadwal maintenance (malam/weekend)
- [ ] Informasikan user tentang maintenance

### Saat Migrasi (Hari H)
- [ ] Pastikan tidak ada user yang login
- [ ] Jalankan backup final
- [ ] Catat timestamp backup
- [ ] Jalankan `bash migrate-production.sh`
- [ ] Monitor output untuk error
- [ ] Tunggu sampai selesai (30-60 menit)
- [ ] Jangan interrupt proses!

### Setelah Migrasi
- [ ] Validasi database (tabel & kolom)
- [ ] Validasi jumlah data
- [ ] Validasi storage (foto terhapus)
- [ ] Jalankan ValidationSeeder
- [ ] Test login aplikasi
- [ ] Test semua fitur utama
- [ ] Monitor error log (1 jam pertama)
- [ ] Informasikan user selesai

### 1 Hari Setelah
- [ ] Monitor error log
- [ ] Terima feedback user
- [ ] Fix bug jika ada
- [ ] Validasi data sekali lagi
- [ ] Dokumentasi hasil migrasi

---

## 🎯 KRITERIA SUKSES

Migrasi dianggap berhasil jika:

- ✅ Semua tabel baru terbuat (students, shu_point_transactions, attendances_photo_backup)
- ✅ Semua kolom baru ada (late_minutes, late_category, student_id, dll)
- ✅ Kolom check_in_photo TIDAK ADA di tabel attendances
- ✅ Jumlah data konsisten (users: 14, attendances: 714, sales: 2, products: 262)
- ✅ Foreign keys valid
- ✅ Path foto tersimpan di backup table (483 records)
- ✅ File foto terhapus dari storage
- ✅ Aplikasi berjalan normal
- ✅ User bisa login dan menggunakan fitur
- ✅ Tidak ada error di log

---

## 📊 TIMELINE REKOMENDASI

### 1 Minggu Sebelum
- Baca panduan lengkap
- Test di development
- Koordinasi dengan tim
- Tentukan jadwal

### 1 Hari Sebelum
- Backup database & storage
- Upload file ke server
- Informasikan user
- Siapkan kontak darurat

### Hari H (Malam/Weekend)
- Backup final
- Jalankan migrasi
- Validasi hasil
- Monitor aplikasi

### 1 Hari Setelah
- Monitor error log
- Terima feedback user
- Fix bug jika ada
- Dokumentasi

---

## 📞 KONTAK DARURAT

**Developer:**
- Nama: _______________
- Phone: _______________
- Email: _______________

**Database Admin:**
- Nama: _______________
- Phone: _______________

**Server Admin:**
- Nama: _______________
- Phone: _______________

---

## 📚 FILE PENTING

### Scripts
- `migrate-production.sh` - Script migrasi otomatis
- `rollback-migration.sh` - Script rollback

### Seeders
- `database/seeders/MigrateProductionDataSeeder.php` - Migrasi data production
- `database/seeders/MigrateSalesForShuSeeder.php` - Migrasi sales SHU
- `database/seeders/MigrateAttendanceDataSeeder.php` - Migrasi attendance (hapus foto)
- `database/seeders/ValidationSeeder.php` - Validasi hasil

### Backup
- `backups/backup_production_[timestamp].sql` - Backup database
- `backups/storage_backup_[timestamp].tar.gz` - Backup storage

---

## 🎉 SELESAI!

Setelah migrasi berhasil:

1. ✅ Database sudah update dengan struktur terbaru
2. ✅ Sistem SHU Point siap digunakan
3. ✅ Attendance tracking lebih detail (late minutes & category)
4. ✅ Foto check-in dihapus (storage lebih ringan)
5. ✅ Semua data aman dan konsisten
6. ✅ Aplikasi siap dengan fitur baru

**Selamat! Migrasi database berhasil! 🚀**

---

## 💡 TIPS PRO

1. **Backup Ganda:** Simpan backup di 2 tempat berbeda
2. **Test Rollback:** Test script rollback di development
3. **Monitor Real-time:** Buka 2 terminal (1 untuk migrasi, 1 untuk log)
4. **Dokumentasi:** Catat semua yang terjadi selama migrasi
5. **Komunikasi:** Update tim secara berkala

---

**Dibuat oleh:** Kiro AI Assistant  
**Tanggal:** 5 April 2026  
**Versi:** 2.0 - Final Consolidated Version  
**Status:** ✅ READY - Foto akan dihapus permanen
