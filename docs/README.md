# Seeder Data

## Cara Pakai

```bash
# Fresh migration + seed semua data
php artisan migrate:fresh --seed
```

## Seeder yang Dijalankan

### 1. Konfigurasi & Setting Sistem (Krusial)
- **PenaltyTypeSeeder** - Jenis-jenis penalty/sanksi
- **SystemSettingSeeder** - Pengaturan sistem umum
- **StoreSettingSeeder** - Pengaturan toko
- **ScheduleConfigurationSeeder** - Konfigurasi jadwal

### 2. Users, Roles & Permissions
- **RolePermissionSeeder** - Roles dan permissions
- **UserSeeder** - Data user dari `users_complete.csv`
- **Jumlah**: 14 users, 15 roles
- **Default Password**: `password`
- **Login**: Gunakan NIM atau Email

### 3. Products (Katalog)
- **KatalogSeeder** - Import dari `Katalog.csv`
- **Jumlah**: 262 produk

## Struktur Organisasi

- **Ketua**: Diva Afdholia R.
- **Wakil Ketua**: Fikri Adi Nugraha
- **Super Admin (IT)**: Rehandhika Arya Pratama

## Verifikasi

```bash
php artisan tinker
>>> App\Models\User::count()
>>> App\Models\Product::count()
>>> Spatie\Permission\Models\Role::count()
```

## Seeder yang Dihapus

Seeder data dummy/sample berikut sudah dihapus:
- ❌ ProductSeeder (dummy products)
- ❌ SaleSeeder (dummy sales)
- ❌ PurchaseSeeder (dummy purchases)
- ❌ AttendanceSeeder (dummy attendance)
- ❌ ScheduleSeeder (dummy schedules)
- ❌ VariantOptionSeeder (dummy variants)

