# 🚀 Migrasi Database Production SIKOPMA

## 📖 PANDUAN LENGKAP

**Baca file ini untuk semua informasi migrasi:**

👉 **[PANDUAN_MIGRASI_DATABASE.md](PANDUAN_MIGRASI_DATABASE.md)** ⭐

---

## ⚡ QUICK START

### 1. Backup (WAJIB!)
```bash
mysqldump -u username -p wirus > backup_$(date +%Y%m%d).sql
tar -czf storage_backup.tar.gz storage/app/public/attendance/
```

### 2. Migrasi
```bash
chmod +x migrate-production.sh
bash migrate-production.sh
```

### 3. Validasi
```bash
php artisan db:seed --class=ValidationSeeder --force
```

---

## ⚠️ PERINGATAN PENTING

### 🔴 FOTO CHECK-IN AKAN DIHAPUS PERMANEN!

- ❌ 483 file foto akan dihapus dari storage
- ❌ Tidak bisa di-restore setelah migrasi
- ✅ Path foto di-backup untuk audit trail

**Baca detail lengkap di [PANDUAN_MIGRASI_DATABASE.md](PANDUAN_MIGRASI_DATABASE.md)**

---

## 📁 File Penting

- **PANDUAN_MIGRASI_DATABASE.md** - Panduan lengkap (BACA INI!)
- **migrate-production.sh** - Script migrasi otomatis
- **rollback-migration.sh** - Script rollback
- **database/seeders/** - Seeder migrasi data

---

## 📊 Ringkasan Perubahan

- **3 tabel baru:** students, shu_point_transactions, attendances_photo_backup
- **6 tabel diubah:** attendances, sales, activity_logs, purchase_items, schedule_assignments, schedule_change_requests
- **483 foto dihapus:** File foto check-in dihapus permanen
- **Semua data aman:** 714 attendance records, 14 users, 262 products tetap utuh

---

## ✅ Checklist Singkat

- [ ] Baca **PANDUAN_MIGRASI_DATABASE.md**
- [ ] Pahami foto akan dihapus permanen
- [ ] Test di development
- [ ] Backup database & storage
- [ ] Jalankan migrasi
- [ ] Validasi hasil

---

**Estimasi Waktu:** 30-60 menit  
**Downtime:** 30-60 menit  
**Status:** ✅ Ready to Deploy

---

**Dibuat oleh:** Kiro AI Assistant  
**Tanggal:** 5 April 2026
