# Rencana Restrukturisasi Proyek SIKOPMA

> **Dokumen ini merupakan proposal rencana restrukturisasi menyeluruh yang memerlukan persetujuan sebelum eksekusi.**
> 
> **Tanggal Analisis**: 21 Februari 2026
> **Versi Dokumen**: 1.0

---

## Daftar Isi

1. [Ringkasan Eksekutif](#1-ringkasan-eksekutif)
2. [Analisis Struktur Saat Ini](#2-analisis-struktur-saat-ini)
3. [Identifikasi Masalah](#3-identifikasi-masalah)
4. [Standarisasi Penamaan](#4-standarisasi-penamaan)
5. [Struktur Direktori Baru](#5-struktur-direktori-baru)
6. [Pemetaan Migrasi](#6-pemetaan-migrasi)
7. [Rencana Implementasi](#7-rencana-implementasi)
8. [Risiko dan Mitigasi](#8-risiko-dan-mitigasi)

---

## 1. Ringkasan Eksekutif

Proyek SIKOPMA merupakan aplikasi manajemen koperasi berbasis Laravel 12 dengan Livewire 3. Analisis menyeluruh mengidentifikasi beberapa area yang memerlukan restrukturisasi untuk mencapai standar industri profesional:

### Temuan Utama
- **Struktur direktori tidak konsisten** di beberapa area
- **Konvensi penamaan file** yang bervariasi (PascalCase, kebab-case, snake_case bercampur)
- **File-file sisa/refactoring** yang masih tersisa di direktori kode
- **Organisasi komponen Livewire** yang perlu distandardisasi
- **Dokumentasi dan file non-kode** bercampur dengan kode sumber

### Tujuan Restrukturisasi
1. Menerapkan standar industri Laravel yang konsisten
2. Meningkatkan maintainability dan readability kode
3. Menghilangkan "vibe coding" dan jejak kode tidak terstruktur
4. Memfasilitasi onboarding developer baru
5. Meningkatkan testability dan separation of concerns

---

## 2. Analisis Struktur Saat Ini

### 2.1 Root Directory

```
DEPLOY SIKOPMA/
├── -p/                          # ❌ Direktori tidak standar
├── .kilocode/                   # ⚠️ IDE-specific, seharusnya di .gitignore
├── .trae/                       # ⚠️ IDE-specific, seharusnya di .gitignore
├── _bmad/                       # ⚠️ Tool eksternal, perlu dokumentasi
├── CHEATSHEET.txt               # ❌ Seharusnya di docs/
├── PANDUAN.md                   # ❌ Seharusnya di docs/
├── PERMISSIONREPORT.md          # ❌ Seharusnya di docs/
├── README.md                    # ✅ OK
├── artisan                      # ✅ OK
├── composer.json                # ✅ OK
├── package.json                 # ✅ OK
├── tailwind.config.js           # ✅ OK
└── ... (standar Laravel files)
```

### 2.2 App Directory Structure

```
app/
├── Console/Commands/            # ✅ Struktur baik
├── Events/                      # ✅ OK
├── Exceptions/                  # ✅ OK
├── Exports/                     # ✅ OK
├── Helpers/                     # ✅ OK
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php
│   │   ├── FileDownloadController.php
│   │   ├── LogoutController.php
│   │   ├── PublicPageController.php
│   │   ├── Admin/               # ✅ Terorganisir
│   │   └── PublicApi/           # ✅ Terorganisir
│   ├── Middleware/              # ✅ OK
│   └── Requests/                # ✅ OK
├── Jobs/                        # ✅ OK
├── Listeners/                   # ✅ OK
├── Livewire/                    # ⚠️ Perlu standardisasi (lihat detail)
├── Mail/                        # ✅ OK
├── Models/                      # ✅ OK
├── Observers/                   # ✅ OK
├── Policies/                    # ✅ OK
├── Providers/                   # ✅ OK
├── Repositories/                # ⚠️ Hanya 4 file, pola tidak konsisten
├── Services/                    # ⚠️ Beberapa file sangat besar
│   └── Storage/                 # ✅ Sub-struktur baik dengan DTOs/Exceptions
└── Traits/                      # ✅ OK
```

### 2.3 Livewire Components Analysis

**Masalah Penamaan yang Teridentifikasi:**

| File Saat Ini | Masalah | Rekomendasi |
|--------------|--------|-------------|
| `Index.php` (multiple) | Terlalu generik | `List{Entity}.php` |
| `MyRequests.php` | Tidak konsisten dengan pola lain | `UserRequestList.php` |
| `MyPenalties.php` | Tidak konsisten | `UserPenaltyList.php` |
| `MySchedule.php` | Tidak konsisten | `UserSchedule.php` |
| `MyNotifications.php` | Tidak konsisten | `UserNotificationList.php` |
| `CreateRequest.php` | Ambigu (Leave? Swap?) | `CreateLeaveRequest.php` |
| `CreateProduct.php` | ✅ OK | - |
| `EditProduct.php` | ✅ OK | - |
| `Pos.php` | Terlalu singkat | `PointOfSale.php` |
| `PosEntry.php` | ✅ OK | - |
| `TestAvailability.php` | ❌ File test di direktori produksi | Hapus/Pindahkan |
| `AvailabilityInput.php` | ❌ File stub (250 bytes) | Hapus atau implementasikan |
| `PurchaseList.php` | ❌ File stub (240 bytes) | Hapus atau implementasikan |
| `UserManagement.php` | ❌ File stub (236 bytes) | Hapus atau implementasikan |

**File Non-Kode di Direktori Livewire:**
- `app/Livewire/Stock/REFACTORING-SUMMARY.md` → Pindahkan ke `docs/refactoring/`

### 2.4 Services Analysis

**File dengan Ukuran Besar:**

| File | Ukuran | Rekomendasi |
|------|--------|-------------|
| `ScheduleEditService.php` | 41,521 chars | Pecah menjadi beberapa service classes |
| `ProductVariantService.php` | 23,467 chars | Pertimbangkan pemisahan |
| `ActivityLogService.php` | 13,583 chars | Monitor pertumbuhan |
| `AttendanceService.php` | 15,218 chars | Monitor pertumbuhan |
| `StoreStatusService.php` | 22,794 chars | Pertimbangkan pemisahan |

### 2.5 Resources Directory

```
resources/
├── css/                         # ✅ OK
├── js/
│   ├── components/ui/           # ✅ React UI components
│   ├── react/                   # ✅ React pages & components
│   └── *.js                     # ✅ Config files
└── views/
    ├── welcome.blade.php        # ⚠️ 82,894 chars (inline CSS fallback)
    ├── maintenance.blade.php    # ✅ OK
    ├── admin/                   # ✅ OK
    ├── components/              # ✅ OK
    ├── emails/                  # ✅ OK
    ├── layouts/
    │   ├── APP-LAYOUT-REFACTORING-SUMMARY.md  # ❌ Dokumentasi di direktori view
    │   └── app-layout-test.blade.php          # ❌ File test di produksi
    ├── livewire/
    │   ├── attendance/REFACTORING-SUMMARY.md  # ❌ Dokumentasi di direktori view
    │   └── ...
    └── public/
        ├── test.blade.php       # ❌ File test di produksi
        └── ...
```

### 2.6 Config Directory

**File Konfigurasi Kustom:**
- `siwirus.php` - ⚠️ Nama tidak deskriptif
- `filestorage.php` - ✅ OK
- `menu.php` - ✅ OK
- `roles.php` - ✅ OK
- `schedule.php` - ✅ OK

### 2.7 Database Directory

```
database/
├── Data/                        # ⚠️ Nama tidak standar
│   ├── *.csv                    # Data seeder
│   └── PROSEDUR_IMPORT.md       # ❌ Dokumentasi seharusnya di docs/
├── factories/                   # ✅ OK
├── migrations/                  # ✅ OK
└── seeders/                     # ✅ OK
```

### 2.8 Routes Directory

```
routes/
├── web.php                      # ✅ OK (16,081 chars - terstruktur baik)
└── console.php                  # ✅ OK
```

**Catatan:** Tidak ada `api.php` atau `channels.php` - rute API ada di `web.php` dengan prefix `/api/publik`.

### 2.9 Tests Directory

```
tests/
├── Feature/                     # ✅ Struktur baik
│   ├── Audit/                   # ✅ Terorganisir
│   ├── Commands/                # ✅ Terorganisir
│   ├── Components/              # ✅ Terorganisir
│   ├── Livewire/                # ✅ Terorganisir
│   └── ShuPoint/                # ✅ Terorganisir
├── Unit/                        # ✅ OK
└── TestCase.php                 # ✅ OK
```

---

## 3. Identifikasi Masalah

### 3.1 Kritikal (Harus Diperbaiki)

| ID | Masalah | Lokasi | Dampak |
|----|---------|--------|--------|
| C1 | File stub/kosong di direktori produksi | `app/Livewire/*/` | Kode mati, kebingungan |
| C2 | File dokumentasi bercampur dengan kode | Multiple locations | Maintainability |
| C3 | Direktori tidak standar di root | `-p/`, `_bmad/` | Struktur project |
| C4 | File test di direktori produksi | `resources/views/*/` | Security, cleanliness |

### 3.2 Tinggi (Sebaiknya Diperbaiki)

| ID | Masalah | Lokasi | Dampak |
|----|---------|--------|--------|
| H1 | Inkonsistensi penamaan Livewire | `app/Livewire/*/` | Readability |
| H2 | Service class terlalu besar | `app/Services/` | Maintainability |
| H3 | Nama file konfigurasi tidak deskriptif | `config/siwirus.php` | Discoverability |
| H4 | Repository pattern tidak konsisten | `app/Repositories/` | Architecture |

### 3.3 Sedang (Disarankan Diperbaiki)

| ID | Masalah | Lokasi | Dampak |
|----|---------|--------|--------|
| M1 | Inline CSS di view | `welcome.blade.php` | Performance |
| M2 | Data CSV di database directory | `database/Data/` | Organization |
| M3 | IDE-specific folders tidak di gitignore | `.kilocode/`, `.trae/` | Cleanliness |

---

## 4. Standarisasi Penamaan

### 4.1 Konvensi Penamaan File

| Tipe File | Konvensi | Contoh |
|-----------|----------|--------|
| PHP Class | PascalCase | `ProductService.php` |
| Livewire Component | PascalCase dengan konteks | `ListProducts.php` |
| Blade View | kebab-case | `list-products.blade.php` |
| Migration | snake_case dengan timestamp | `2026_01_01_create_products_table.php` |
| Config | kebab-case | `app.php`, `file-storage.php` |
| Test | PascalCase dengan suffix Test | `ProductServiceTest.php` |

### 4.2 Konvensi Penamaan Livewire Components

**Pola yang Disarankan:**

| Aksi | Format | Contoh |
|------|--------|--------|
| List/Index | `{Entity}List` | `ProductList.php` |
| Create | `Create{Entity}` | `CreateProduct.php` |
| Edit | `Edit{Entity}` | `EditProduct.php` |
| View Detail | `{Entity}Detail` | `ProductDetail.php` |
| User-specific | `User{Entity}{Action}` | `UserScheduleList.php` |
| Manager/Admin | `{Entity}Manager` | `LeaveManager.php` |
| Dashboard | `{Context}Dashboard` | `SwapDashboard.php` |

### 4.3 Konvensi Penamaan Service

**Pola yang Disarankan:**

| Tipe | Format | Contoh |
|------|--------|--------|
| CRUD | `{Entity}Service` | `ProductService.php` |
| Domain | `{Domain}{Action}Service` | `ScheduleEditService.php` |
| Utility | `{Function}Service` | `CacheService.php` |
| External | `{Provider}{Function}Service` | `PaymentGatewayService.php` |

---

## 5. Struktur Direktori Baru

### 5.1 Root Directory (Usulan)

```
DEPLOY SIKOPMA/
├── .github/                     # CI/CD workflows
├── app/                         # Application code
├── bootstrap/                   # Framework bootstrap
├── config/                      # Configuration files
├── database/                    # Database files
│   ├── factories/
│   ├── migrations/
│   ├── seeders/
│   └── seed-data/              # Renamed from Data/
├── docs/                        # 📁 BARU: Semua dokumentasi
│   ├── api/
│   ├── architecture/
│   ├── refactoring/            # Refactoring summaries
│   └── guides/
├── lang/                        # Localization
├── public/                      # Public assets
├── resources/                   # Views, assets, frontend
├── routes/                      # Route definitions
├── storage/                     # Storage
├── tests/                       # Test files
├── .editorconfig
├── .env.example
├── .gitignore
├── artisan
├── composer.json
├── composer.lock
├── LICENSE
├── package.json
├── package-lock.json
├── phpunit.xml
├── README.md
├── tailwind.config.js
└── vite.config.js
```

### 5.2 App Directory (Usulan)

```
app/
├── Console/
│   └── Commands/
│       ├── Schedule/
│       │   ├── AutoGenerateSchedule.php
│       │   └── SendAvailabilityReminder.php
│       ├── Attendance/
│       │   └── ProcessAbsences.php
│       ├── Penalty/
│       │   └── ResetPenaltyPoints.php
│       ├── Permission/
│       │   └── ClearPermissionCache.php
│       └── ScheduleCheckCommand.php
│
├── Domain/                      # 📁 BARU: Domain logic
│   ├── Attendance/
│   │   ├── Actions/
│   │   ├── DTOs/
│   │   └── Events/
│   ├── Inventory/
│   │   ├── Actions/
│   │   ├── DTOs/
│   │   └── Events/
│   ├── Schedule/
│   │   ├── Actions/
│   │   ├── DTOs/
│   │   └── Events/
│   └── Sales/
│       ├── Actions/
│       ├── DTOs/
│       └── Events/
│
├── Events/
├── Exceptions/
│   ├── Domain/                 # 📁 BARU: Domain exceptions
│   │   ├── ScheduleConflictException.php
│   │   └── BusinessException.php
│   └── Handler.php
│
├── Exports/
├── Helpers/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   ├── Api/
│   │   │   └── Public/        # Renamed from PublicApi
│   │   ├── Auth/
│   │   ├── Controller.php
│   │   ├── FileDownloadController.php
│   │   └── PublicController.php  # Renamed from PublicPageController
│   ├── Middleware/
│   └── Requests/
│
├── Jobs/
├── Listeners/
├── Livewire/
│   ├── Admin/                  # 📁 Reorganized
│   │   ├── ActivityLogViewer.php
│   │   ├── AttendanceManagement.php
│   │   ├── BannerManagement.php
│   │   └── Settings/
│   ├── Attendance/
│   │   ├── CheckInOut.php
│   │   ├── AttendanceHistory.php  # Renamed from History.php
│   │   └── AttendanceList.php     # Renamed from Index.php
│   ├── Auth/
│   │   └── LoginForm.php
│   ├── Cashier/
│   │   ├── PointOfSale.php       # Renamed from Pos.php
│   │   └── PosEntry.php
│   ├── Dashboard/
│   │   └── DashboardIndex.php
│   ├── Leave/
│   │   ├── LeaveRequestList.php  # Renamed from Index.php
│   │   ├── CreateLeaveRequest.php # Renamed from CreateRequest.php
│   │   ├── UserLeaveRequests.php  # Renamed from MyRequests.php
│   │   ├── LeaveApprovals.php     # Renamed from PendingApprovals.php
│   │   └── LeaveManager.php
│   ├── Notification/
│   │   ├── NotificationList.php
│   │   └── UserNotifications.php
│   ├── Penalty/
│   │   ├── PenaltyList.php
│   │   ├── UserPenalties.php
│   │   └── PenaltyManager.php
│   ├── Product/
│   │   ├── ProductList.php
│   │   ├── CreateProduct.php
│   │   └── EditProduct.php
│   ├── Profile/
│   │   └── EditProfile.php
│   ├── Public/                  # Public-facing components
│   │   ├── AboutPage.php
│   │   ├── CatalogPage.php
│   │   ├── ProductDetailPage.php
│   │   └── StoreStatusWidget.php
│   ├── Purchase/
│   │   └── PurchaseList.php
│   ├── Report/
│   │   ├── AttendanceReport.php
│   │   ├── SalesReport.php
│   │   └── PenaltyReport.php
│   ├── Role/
│   │   └── RoleList.php
│   ├── Schedule/
│   │   ├── UserSchedule.php       # Renamed from MySchedule.php
│   │   ├── ScheduleList.php       # Renamed from Index.php
│   │   ├── CreateSchedule.php
│   │   ├── EditSchedule.php
│   │   ├── ScheduleCalendar.php
│   │   ├── ScheduleGenerator.php
│   │   ├── ScheduleStatistics.php
│   │   ├── ScheduleTemplates.php
│   │   ├── AvailabilityManager.php
│   │   └── ScheduleChangeManager.php
│   ├── Settings/
│   │   ├── PaymentSettings.php
│   │   └── SystemSettings.php
│   ├── ShuPoint/
│   │   ├── ShuMonitoring.php
│   │   └── StudentDetail.php
│   ├── Stock/
│   │   ├── StockManager.php
│   │   └── ProcurementModal.php
│   ├── Swap/
│   │   ├── SwapRequestList.php    # Renamed from Index.php
│   │   ├── CreateSwapRequest.php  # Renamed from CreateRequest.php
│   │   ├── UserSwapRequests.php   # Renamed from MyRequests.php
│   │   ├── SwapApprovals.php      # Renamed from PendingApprovals.php
│   │   ├── SwapManager.php
│   │   └── SwapDashboard.php
│   └── User/
│       └── UserList.php
│
├── Mail/
├── Models/
│   ├── Concerns/               # 📁 BARU: Model traits
│   └── Scopes/                 # 📁 BARU: Query scopes
│
├── Observers/
├── Policies/
├── Providers/
├── Repositories/
│   ├── Contracts/              # 📁 BARU: Repository interfaces
│   └── Eloquent/
│
├── Services/
│   ├── Contracts/              # 📁 BARU: Service interfaces
│   ├── Domain/                 # 📁 BARU: Domain services
│   │   ├── Attendance/
│   │   ├── Inventory/
│   │   ├── Schedule/
│   │   └── Sales/
│   ├── Shared/                 # 📁 BARU: Shared services
│   │   ├── CacheService.php
│   │   ├── NotificationService.php
│   │   └── ActivityLogService.php
│   └── Storage/                # ✅ Already good
│
└── Traits/
```

### 5.3 Resources Directory (Usulan)

```
resources/
├── css/
│   └── app.css
│
├── js/
│   ├── app.js
│   ├── bootstrap.js
│   ├── components/
│   │   └── ui/                 # React UI components
│   ├── config/                 # 📁 Renamed from root js files
│   │   ├── alpine-init.js
│   │   ├── filepond-config.js
│   │   ├── flatpickr-config.js
│   │   ├── sortable-config.js
│   │   └── tom-select-config.js
│   ├── lib/
│   │   ├── api.js
│   │   ├── format.js
│   │   ├── theme.js
│   │   └── utils.js
│   ├── react/
│   │   ├── components/
│   │   ├── context/
│   │   ├── data/
│   │   ├── hooks/
│   │   ├── lib/
│   │   └── pages/
│   └── utils/
│       └── charts.js
│
└── views/
    ├── admin/
    ├── components/
    │   ├── data/
    │   ├── layout/
    │   ├── schedule/
    │   ├── sidebar/
    │   └── ui/
    ├── emails/
    ├── layouts/
    ├── livewire/               # Mirror app/Livewire structure
    └── public/
        └── partials/
```

### 5.4 Config Directory (Usulan)

```
config/
├── app.php
├── auth.php
├── cache.php
├── database.php
├── file-storage.php            # Renamed from filestorage.php
├── filesystems.php
├── livewire.php
├── logging.php
├── mail.php
├── menu.php
├── permission.php
├── queue.php
├── roles.php
├── schedule.php
├── services.php
├── session.php
└── app-settings.php            # Renamed from siwirus.php
```

---

## 6. Pemetaan Migrasi

### 6.1 File yang Dihapus

| File | Alasan |
|------|--------|
| `-p/` (direktori) | Direktori tidak standar |
| `app/Livewire/Schedule/TestAvailability.php` | File test di produksi |
| `app/Livewire/Schedule/AvailabilityInput.php` | File stub kosong |
| `app/Livewire/Purchase/PurchaseList.php` | File stub kosong |
| `app/Livewire/User/UserManagement.php` | File stub kosong |
| `resources/views/layouts/app-layout-test.blade.php` | File test di produksi |
| `resources/views/public/test.blade.php` | File test di produksi |

### 6.2 File yang Dipindahkan

| Dari | Ke |
|------|-----|
| `CHEATSHEET.txt` | `docs/cheatsheet.txt` |
| `PANDUAN.md` | `docs/guides/panduan.md` |
| `PERMISSIONREPORT.md` | `docs/architecture/permission-report.md` |
| `app/Livewire/Stock/REFACTORING-SUMMARY.md` | `docs/refactoring/stock-management.md` |
| `resources/views/livewire/attendance/REFACTORING-SUMMARY.md` | `docs/refactoring/attendance.md` |
| `resources/views/layouts/APP-LAYOUT-REFACTORING-SUMMARY.md` | `docs/refactoring/app-layout.md` |
| `database/Data/` | `database/seed-data/` |
| `database/Data/PROSEDUR_IMPORT.md` | `docs/guides/import-procedure.md` |

### 6.3 File yang Diubah Nama (Renamed)

#### Livewire Components

| Lama | Baru |
|------|------|
| `app/Livewire/Attendance/Index.php` | `app/Livewire/Attendance/AttendanceList.php` |
| `app/Livewire/Attendance/History.php` | `app/Livewire/Attendance/AttendanceHistory.php` |
| `app/Livewire/Cashier/Pos.php` | `app/Livewire/Cashier/PointOfSale.php` |
| `app/Livewire/Dashboard/Index.php` | `app/Livewire/Dashboard/DashboardIndex.php` |
| `app/Livewire/Leave/Index.php` | `app/Livewire/Leave/LeaveRequestList.php` |
| `app/Livewire/Leave/CreateRequest.php` | `app/Livewire/Leave/CreateLeaveRequest.php` |
| `app/Livewire/Leave/MyRequests.php` | `app/Livewire/Leave/UserLeaveRequests.php` |
| `app/Livewire/Leave/PendingApprovals.php` | `app/Livewire/Leave/LeaveApprovals.php` |
| `app/Livewire/Notification/Index.php` | `app/Livewire/Notification/NotificationList.php` |
| `app/Livewire/Notification/MyNotifications.php` | `app/Livewire/Notification/UserNotifications.php` |
| `app/Livewire/Penalty/Index.php` | `app/Livewire/Penalty/PenaltyList.php` |
| `app/Livewire/Penalty/MyPenalties.php` | `app/Livewire/Penalty/UserPenalties.php` |
| `app/Livewire/Penalty/ManagePenalties.php` | `app/Livewire/Penalty/PenaltyManager.php` |
| `app/Livewire/Product/Index.php` | `app/Livewire/Product/ProductList.php` |
| `app/Livewire/Profile/Edit.php` | `app/Livewire/Profile/EditProfile.php` |
| `app/Livewire/Public/About.php` | `app/Livewire/Public/AboutPage.php` |
| `app/Livewire/Public/Catalog.php` | `app/Livewire/Public/CatalogPage.php` |
| `app/Livewire/Public/ProductDetail.php` | `app/Livewire/Public/ProductDetailPage.php` |
| `app/Livewire/Public/StoreStatus.php` | `app/Livewire/Public/StoreStatusWidget.php` |
| `app/Livewire/Purchase/Index.php` | `app/Livewire/Purchase/PurchaseList.php` |
| `app/Livewire/Report/AttendanceReport.php` | `app/Livewire/Report/AttendanceReport.php` (no change) |
| `app/Livewire/Role/Index.php` | `app/Livewire/Role/RoleList.php` |
| `app/Livewire/Schedule/Index.php` | `app/Livewire/Schedule/ScheduleList.php` |
| `app/Livewire/Schedule/MySchedule.php` | `app/Livewire/Schedule/UserSchedule.php` |
| `app/Livewire/Swap/Index.php` | `app/Livewire/Swap/SwapRequestList.php` |
| `app/Livewire/Swap/CreateRequest.php` | `app/Livewire/Swap/CreateSwapRequest.php` |
| `app/Livewire/Swap/MyRequests.php` | `app/Livewire/Swap/UserSwapRequests.php` |
| `app/Livewire/Swap/PendingApprovals.php` | `app/Livewire/Swap/SwapApprovals.php` |
| `app/Livewire/User/Index.php` | `app/Livewire/User/UserList.php` |

#### Controllers

| Lama | Baru |
|------|------|
| `app/Http/Controllers/PublicPageController.php` | `app/Http/Controllers/PublicController.php` |
| `app/Http/Controllers/PublicApi/` | `app/Http/Controllers/Api/Public/` |

#### Config Files

| Lama | Baru |
|------|------|
| `config/siwirus.php` | `config/app-settings.php` |
| `config/filestorage.php` | `config/file-storage.php` |

#### Blade Views (mengikuti perubahan Livewire)

| Lama | Baru |
|------|------|
| `resources/views/livewire/attendance/index.blade.php` | `resources/views/livewire/attendance/attendance-list.blade.php` |
| `resources/views/livewire/attendance/history.blade.php` | `resources/views/livewire/attendance/attendance-history.blade.php` |
| `resources/views/livewire/cashier/pos.blade.php` | `resources/views/livewire/cashier/point-of-sale.blade.php` |
| (dan seterusnya mengikuti pola Livewire) | |

### 6.4 Pembaruan Namespace dan Referensi

Setelah perubahan nama file, pembaruan berikut diperlukan:

1. **Namespace PHP** - Update semua class namespace
2. **Import statements** - Update semua use statements
3. **Route definitions** - Update referensi di `routes/web.php`
4. **Livewire component references** - Update di blade views
5. **Test files** - Update referensi di test classes
6. **Config references** - Update `config()` calls

---

## 7. Rencana Implementasi

### 7.1 Fase 1: Persiapan (Estimasi: 1 hari)

- [ ] Backup seluruh codebase
- [ ] Buat branch baru `refactor/restructure-v1`
- [ ] Pastikan semua test passing
- [ ] Document current state

### 7.2 Fase 2: Pembersihan (Estimasi: 1 hari)

- [ ] Hapus direktori `-p/`
- [ ] Hapus file-file stub kosong
- [ ] Hapus file test di direktori produksi
- [ ] Update `.gitignore` untuk IDE-specific folders

### 7.3 Fase 3: Reorganisasi Dokumentasi (Estimasi: 0.5 hari)

- [ ] Buat struktur direktori `docs/`
- [ ] Pindahkan semua file dokumentasi
- [ ] Update referensi di README.md

### 7.4 Fase 4: Rename Livewire Components (Estimasi: 2 hari)

- [ ] Rename semua Livewire component files
- [ ] Update namespace dan class names
- [ ] Rename blade view files
- [ ] Update route definitions
- [ ] Update component references di views
- [ ] Update test files

### 7.5 Fase 5: Reorganisasi Services (Estimasi: 2 hari)

- [ ] Buat struktur `Services/Domain/` dan `Services/Shared/`
- [ ] Pindahkan service classes ke struktur baru
- [ ] Pertimbangkan pemecahan service besar (opsional, fase terpisah)

### 7.6 Fase 6: Reorganisasi Config (Estimasi: 0.5 hari)

- [ ] Rename config files
- [ ] Update semua `config()` calls di seluruh codebase

### 7.7 Fase 7: Testing & Validasi (Estimasi: 1 hari)

- [ ] Run semua unit tests
- [ ] Run semua feature tests
- [ ] Manual testing fitur utama
- [ ] Fix breaking changes

### 7.8 Fase 8: Dokumentasi & Finalisasi (Estimasi: 0.5 hari)

- [ ] Update README.md
- [ ] Update PANDUAN.md
- [ ] Create CHANGELOG entry
- [ ] Code review

**Total Estimasi Waktu: 8-10 hari kerja**

---

## 8. Risiko dan Mitigasi

### 8.1 Risiko Teknis

| Risiko | Probabilitas | Dampak | Mitigasi |
|--------|--------------|--------|----------|
| Breaking changes di routes | Tinggi | Tinggi | Comprehensive testing, staged rollout |
| Livewire component tidak terdeteksi | Sedang | Tinggi | Clear cache, update autoloader |
| Referensi view hilang | Sedang | Sedang | Grep semua string references |
| Config keys tidak ditemukan | Sedang | Sedang | Search & replace semua config() calls |

### 8.2 Risiko Operasional

| Risiko | Probabilitas | Dampak | Mitigasi |
|--------|--------------|--------|----------|
| Developer confusion | Tinggi | Sedang | Dokumentasi lengkap, team briefing |
| Merge conflicts dengan ongoing work | Sedang | Sedang | Koordinasi tim, feature freeze |
| Production deployment issues | Rendah | Tinggi | Staging environment testing |

### 8.3 Rollback Plan

Jika terjadi masalah kritis setelah deployment:

1. **Immediate rollback**: Revert ke commit sebelum merge
2. **Hotfix branch**: Buat branch dari state sebelum restrukturisasi
3. **Selective revert**: Revert file-file spesifik yang bermasalah

---

## 9. Laporan Analisis Statis: Kode Mati dan File Tidak Terreferensikan

### 9.1 Metodologi Analisis

Analisis dilakukan dengan pemindaian menyeluruh terhadap:
- **Commands**: Pemeriksaan signature command dan referensi di routes/console.php, kernel, dan pemanggilan artisan
- **Events**: Pemeriksaan dispatch event dan listener
- **Exports**: Pemeriksaan penggunaan di Livewire components dan controllers
- **Helpers**: Pemeriksaan penggunaan function dan class
- **Jobs**: Pemeriksaan dispatch dan queue usage
- **Traits**: Pemeriksaan penggunaan di class lain
- **Exceptions**: Pemeriksaan throw dan catch statements
- **Migrations**: Pemeriksaan file kosong dan duplikasi
- **Tests**: Pemeriksaan test file yang tidak relevan

### 9.2 Commands - Analisis Detail

| File | Signature | Status | Justifikasi |
|------|-----------|--------|-------------|
| `AutoGenerateSchedule.php` | `schedule:auto-generate` | ⚠️ TIDAK TERREFERENSI | Tidak ada pemanggilan di routes/console.php, kernel, atau kode lain. Command ini mungkin dijalankan manual tapi tidak terjadwal. |
| `CheckMissedSchedules.php` | `schedule:check-missed` | ⚠️ TIDAK TERREFERENSI | Tidak ada pemanggilan di sistem. Fungsi ini mungkin sudah digantikan oleh `ProcessAbsencesJob`. |
| `ClearPermissionCache.php` | `permission:clear-cache` | ✅ TERREFERENSI | Digunakan di `tests/Feature/PermissionTest.php` line 499 |
| `ProcessAbsencesJob.php` | `attendance:process-absences` | ✅ TERREFERENSI | Digunakan di `tests/Feature/Commands/ProcessAbsencesJobTest.php` dan direferensikan di `AttendanceService.php` |
| `ResetPenaltyPoints.php` | `penalty:reset-points` | ⚠️ TIDAK TERREFERENSI | Tidak ada pemanggilan di sistem. Perlu dijadwalkan di kernel atau dihapus. |
| `SendAvailabilityReminder.php` | `schedule:send-reminder` | ⚠️ TIDAK TERREFERENSI | Tidak ada pemanggilan di sistem. Perlu dijadwalkan di kernel atau dihapus. |

**Rekomendasi Commands:**
- Hapus atau jadwalkan 4 command yang tidak terreferensiasi
- Pertimbangkan konsolidasi `CheckMissedSchedules` dengan `ProcessAbsencesJob`

### 9.3 Events - Analisis Detail

| File | Status | Referensi |
|------|--------|-----------|
| `StoreStatusChanged.php` | ✅ TERREFERENSI | Dispatched di `StoreStatusService.php` lines 170, 196 |

**Kesimpulan Events:** Semua event class terreferensiasi dengan baik.

### 9.4 Exports - Analisis Detail

| File | Status | Referensi |
|------|--------|-----------|
| `AttendanceExport.php` | ✅ TERREFERENSI | `Admin/AttendanceManagement.php` line 295 |
| `SaleItemsExport.php` | ✅ TERREFERENSI | `Report/SalesReport.php` line 356 |
| `SalesExport.php` | ✅ TERREFERENSI | `Report/SalesReport.php` line 343 |
| `ShuRedemptionsExport.php` | ✅ TERREFERENSI | `ShuPoint/Monitoring.php` line 287 |
| `ShuStudentsExport.php` | ✅ TERREFERENSI | `ShuPoint/Monitoring.php` line 216 |
| `ShuStudentTransactionsExport.php` | ✅ TERREFERENSI | `ShuPoint/StudentDetail.php` line 160 |
| `StockHistoryExport.php` | ✅ TERREFERENSI | `Stock/Index.php` line 98 |
| `StockProductsExport.php` | ✅ TERREFERENSI | `Stock/Index.php` line 106 |

**Kesimpulan Exports:** Semua export class terreferensiasi dan digunakan.

### 9.5 Helpers - Analisis Detail

| File | Status | Referensi |
|------|--------|-----------|
| `DateTimeHelper.php` | ✅ TERREFERENSI | Digunakan di 35+ lokasi termasuk AppServiceProvider, Middleware, Services |
| `helpers.php` | ✅ TERREFERENSI | Loaded via composer.json autoload, functions used globally |

**Kesimpulan Helpers:** Semua helper terreferensiasi dengan baik.

### 9.6 Jobs - Analisis Detail

| File | Status | Referensi |
|------|--------|-----------|
| `LogLoginActivity.php` | ✅ TERREFERENSI | Dispatched di `Livewire/Auth/LoginForm.php` lines 64, 79 |
| `SendInitialCredentialsJob.php` | ✅ TERREFERENSI | Dispatched di `Services/CredentialService.php` lines 110, 178 |

**Kesimpulan Jobs:** Semua job class terreferensiasi dan digunakan.

### 9.7 Traits - Analisis Detail

| File | Status | Referensi |
|------|--------|-----------|
| `AuthorizesLivewireRequests.php` | ✅ TERREFERENSI | Used in `Livewire/Cashier/Pos.php` and `Livewire/Admin/AttendanceManagement.php` |

**Kesimpulan Traits:** Trait terreferensiasi dan digunakan.

### 9.8 Exceptions - Analisis Detail

| File | Status | Referensi |
|------|--------|-----------|
| `BusinessException.php` | ✅ TERREFERENSI | Thrown di `AttendanceService.php`, `NotificationService.php`, `ProductVariantService.php` |
| `Handler.php` | ✅ TERREFERENSI | Laravel default exception handler |
| `ScheduleConflictException.php` | ✅ TERREFERENSI | Thrown di `Livewire/Schedule/InteractiveCalendar.php` line 260 |

**Kesimpulan Exceptions:** Semua exception class terreferensiasi.

### 9.9 Migrations - File Bermasalah

#### Migrations Kosong (Vibe Coding Remnants)

| File | Masalah | Rekomendasi |
|------|---------|-------------|
| `2025_11_08_015823_add_missing_status_column_to_users_table.php` | Migration KOSONG - tidak ada operasi di up() atau down() | **HAPUS** - File tidak melakukan apapun |
| `2025_11_08_015938_fix_missing_status_column_in_users_table.php` | Migration KOSONG - tidak ada operasi di up() atau down() | **HAPUS** - File tidak melakukan apapun |

**Analisis:**
Kedua file migration ini adalah sisa "vibe coding" yang kemungkinan dibuat saat debugging issue kolom status di users table, tapi tidak pernah diimplementasikan. Kedua file memiliki body method kosong:

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // KOSONG
    });
}
```

#### Migrations Cleanup (Sudah Dijalankan - Bisa Dihapus)

| File | Deskripsi | Status |
|------|-----------|--------|
| `2026_02_13_000000_remove_custom_datetime_settings.php` | Menghapus setting datetime lama | ✅ Valid, sudah dijalankan |
| `2026_02_12_240000_cleanup_quick_adjust_history.php` | Membersihkan data stock adjustment lama | ✅ Valid, sudah dijalankan |
| `2026_01_24_200000_drop_maintenance_logs_table.php` | Drop table yang tidak digunakan | ✅ Valid, sudah dijalankan |

**Catatan:** Migration cleanup di atas adalah migration yang valid dan sudah dijalankan. Tidak perlu dihapus karena sudah menjadi bagian dari sejarah database.

### 9.10 Tests - File Tidak Relevan

| File | Masalah | Rekomendasi |
|------|---------|-------------|
| `tests/Unit/ExampleTest.php` | Default Laravel example test | **HAPUS** - Tidak ada value |
| `tests/Feature/ExampleTest.php` | Default Laravel example test | **HAPUS** - Tidak ada value |

### 9.11 Ringkasan File yang Wajib Dihapus

#### Kritikal - Hapus Segera

| Kategori | File | Alasan |
|----------|------|--------|
| Migration | `database/migrations/2025_11_08_015823_add_missing_status_column_to_users_table.php` | Migration kosong, tidak melakukan apapun |
| Migration | `database/migrations/2025_11_08_015938_fix_missing_status_column_in_users_table.php` | Migration kosong, tidak melakukan apapun |
| Test | `tests/Unit/ExampleTest.php` | Default Laravel stub, tidak ada value |
| Test | `tests/Feature/ExampleTest.php` | Default Laravel stub, tidak ada value |
| Livewire | `app/Livewire/Schedule/TestAvailability.php` | File test di direktori produksi (289 bytes) |
| Livewire | `app/Livewire/Schedule/AvailabilityInput.php` | File stub kosong (250 bytes) |
| Livewire | `app/Livewire/Purchase/PurchaseList.php` | File stub kosong (240 bytes) |
| Livewire | `app/Livewire/User/UserManagement.php` | File stub kosong (236 bytes) |
| View | `resources/views/layouts/app-layout-test.blade.php` | File test di produksi |
| View | `resources/views/public/test.blade.php` | File test di produksi |
| Root | `-p/` (direktori) | Direktori tidak standar |

#### Sedang - Evaluasi untuk Dihapus atau Jadwalkan

| Kategori | File | Alasan |
|----------|------|--------|
| Command | `app/Console/Commands/AutoGenerateSchedule.php` | Tidak terjadwal, tidak terreferensiasi |
| Command | `app/Console/Commands/CheckMissedSchedules.php` | Tidak terjadwal, mungkin duplikasi dengan ProcessAbsencesJob |
| Command | `app/Console/Commands/ResetPenaltyPoints.php` | Tidak terjadwal, tidak terreferensiasi |
| Command | `app/Console/Commands/SendAvailabilityReminder.php` | Tidak terjadwal, tidak terreferensiasi |

### 9.12 Rekomendasi Tindak Lanjut

#### Untuk Commands yang Tidak Terreferensiasi:

**Opsi A: Jadwalkan di Kernel**
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    // Auto-generate schedule every Friday at 23:00
    $schedule->command('schedule:auto-generate')
        ->fridays()
        ->at('23:00');
    
    // Process absences daily at 01:00
    $schedule->command('attendance:process-absences')
        ->dailyAt('01:00');
    
    // Reset penalty points every 6 months
    $schedule->command('penalty:reset-points')
        ->everySixMonths();
    
    // Send availability reminder every Sunday at 20:00
    $schedule->command('schedule:send-reminder')
        ->sundays()
        ->at('20:00');
}
```

**Opsi B: Hapus Jika Tidak Diperlukan**
```bash
rm app/Console/Commands/AutoGenerateSchedule.php
rm app/Console/Commands/CheckMissedSchedules.php
rm app/Console/Commands/ResetPenaltyPoints.php
rm app/Console/Commands/SendAvailabilityReminder.php
```

#### Untuk Migration Kosong:

```bash
# Hapus migration kosong
rm database/migrations/2025_11_08_015823_add_missing_status_column_to_users_table.php
rm database/migrations/2025_11_08_015938_fix_missing_status_column_in_users_table.php

# Catatan: Tidak perlu rollback karena migration tidak melakukan apapun
```

---

## Lampiran

### A. Daftar Semua File yang Terdampak

*Lihat spreadsheet terpisah untuk daftar lengkap 200+ file yang terdampak*

### B. Script Migrasi Otomatis

```bash
# Contoh script untuk rename Livewire components
# PERINGATAN: Jalankan dengan hati-hati, review terlebih dahulu

# 1. Rename files
git mv app/Livewire/Attendance/Index.php app/Livewire/Attendance/AttendanceList.php
git mv app/Livewire/Attendance/History.php app/Livewire/Attendance/AttendanceHistory.php
# ... (lanjutkan untuk semua file)

# 2. Update namespaces (menggunakan sed atau tool lain)
find app/Livewire -name "*.php" -exec sed -i 's/namespace App\\Livewire\\Attendance;/namespace App\\Livewire\\Attendance;/' {} \;

# 3. Clear cache
php artisan clear-compiled
php artisan cache:clear
php artisan config:clear
php artisan view:clear
composer dump-autoload
```

### C. Checklist Pre-Deployment

- [ ] Semua tests passing
- [ ] Manual testing completed
- [ ] Documentation updated
- [ ] Team notified
- [ ] Backup verified
- [ ] Rollback plan documented
- [ ] Staging environment tested

---

## Persetujuan

Dokumen ini memerlukan persetujuan sebelum eksekusi. Silakan review dan berikan feedback atau persetujuan untuk melanjutkan.

**Status**: ⏳ Menunggu Persetujuan

---

*Dokumen ini dibuat oleh Kilo Code pada 21 Februari 2026*