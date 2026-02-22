# Prosedur Import Data Roles, Permissions & Users

## 📁 File CSV yang Dibutuhkan

Pastikan file-file berikut ada di folder `database/Data/`:

| File | Deskripsi |
|------|-----------|
| `permissions.csv` | Daftar semua permissions |
| `roles.csv` | Daftar semua roles |
| `role_permissions.csv` | Mapping role ke permission |
| `users_complete.csv` | Data users lengkap |

---

## 🚀 Cara Penggunaan

### 1. Import Semua Data Sekaligus (Recommended)

```bash
# Simulasi dulu (dry-run) untuk melihat apa yang akan terjadi
php artisan import:all --dry-run

# Import dengan data fresh (hapus data lama)
php artisan import:all --fresh

# Import tanpa menghapus data lama (update/create)
php artisan import:all

# Import dengan password custom
php artisan import:all --fresh --password=kopma2024

# Import dan kirim email kredensial
php artisan import:all --fresh --send-credentials
```

### 2. Import Terpisah (Step by Step)

```bash
# Step 1: Import roles & permissions dulu
php artisan import:roles-permissions --fresh

# Step 2: Import users
php artisan import:users --fresh --password=kopma2024
```

---

## 📋 Opsi Command

### `import:all`
| Opsi | Deskripsi |
|------|-----------|
| `--fresh` | Hapus semua data sebelum import |
| `--dry-run` | Simulasi tanpa menyimpan ke database |
| `--password=xxx` | Set password default untuk users |
| `--send-credentials` | Kirim email kredensial ke user baru |

### `import:roles-permissions`
| Opsi | Deskripsi |
|------|-----------|
| `--fresh` | Hapus roles & permissions yang ada |
| `--dry-run` | Simulasi tanpa menyimpan |

### `import:users`
| Opsi | Deskripsi |
|------|-----------|
| `--fresh` | Hapus users yang ada (kecuali Super Admin) |
| `--dry-run` | Simulasi tanpa menyimpan |
| `--password=xxx` | Password default (default: "password") |
| `--send-credentials` | Kirim email kredensial |

---

## 🔄 Export Data dari Database

Untuk backup atau migrasi ke server lain:

```bash
# Export ke folder default (database/Data/export)
php artisan export:roles-permissions

# Export ke folder custom
php artisan export:roles-permissions --path=/path/to/folder
```

---

## 📝 Prosedur Lengkap untuk Server Production

### Persiapan di Server Lokal

1. Pastikan semua file CSV sudah benar
2. Test dengan dry-run:
   ```bash
   php artisan import:all --dry-run
   ```

### Upload ke Server Production

1. Upload file CSV ke server:
   ```bash
   scp database/Data/*.csv user@server:/path/to/project/database/Data/
   ```

2. SSH ke server:
   ```bash
   ssh user@server
   cd /path/to/project
   ```

3. Jalankan import:
   ```bash
   # Backup database dulu!
   php artisan backup:run --only-db
   
   # Import data
   php artisan import:all --fresh --password=passwordAman123
   
   # Clear cache
   php artisan cache:clear
   php artisan config:clear
   ```

---

## ⚠️ Catatan Penting

1. **Backup Database** - Selalu backup sebelum menjalankan dengan `--fresh`

2. **Urutan Import** - Roles & permissions HARUS diimport sebelum users

3. **Password Default** - Pastikan user mengganti password setelah login pertama

4. **Email Kredensial** - Pastikan konfigurasi email sudah benar sebelum menggunakan `--send-credentials`

5. **Super Admin** - User dengan role Super Admin tidak akan dihapus saat `--fresh`

---

## 🔍 Troubleshooting

### Error: Role tidak ditemukan
```
❌ Role berikut belum ada di database: Koordinator Toko
```
**Solusi:** Jalankan `php artisan import:roles-permissions` terlebih dahulu

### Error: File CSV tidak ditemukan
```
❌ File tidak ditemukan: users_complete.csv
```
**Solusi:** Pastikan file ada di folder `database/Data/`

### Error: Permission denied
```
❌ Error: Permission denied
```
**Solusi:** Periksa permission folder dan file

---

## 📊 Struktur Data

### Roles (16 total)
- Super Admin (Koordinator IT)
- Ketua, Wakil Ketua
- Sekretaris
- Bendahara Umum, Bendahara Kegiatan, Bendahara Toko
- Koordinator Toko, Koordinator PSDA, Koordinator Produksi, Koordinator Desain
- Anggota, Anggota Toko, Anggota PSDA, Anggota Produksi, Anggota Desain

### Users (14 total)
- 2 Pimpinan (Ketua, Wakil Ketua)
- 2 Sekretaris
- 3 Bendahara
- 6 Koordinator (termasuk IT sebagai Super Admin)
- 1 Anggota Produksi

---

## ✅ Checklist Setelah Import

- [ ] Login sebagai Super Admin berhasil
- [ ] Login sebagai Ketua berhasil
- [ ] Verifikasi permission setiap role
- [ ] Test fitur check-in/check-out
- [ ] Test akses menu sesuai role
- [ ] Pastikan semua user bisa login
