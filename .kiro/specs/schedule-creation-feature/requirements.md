# Requirements Document - Schedule Creation Feature

## Introduction

Fitur pembuatan jadwal (Schedule Creation) adalah sistem yang memungkinkan admin/ketua koperasi untuk membuat, mengelola, dan mempublikasikan jadwal shift anggota secara efisien. Sistem ini mendukung pembuatan jadwal manual maupun otomatis berdasarkan ketersediaan anggota, dengan mempertimbangkan keadilan distribusi shift dan preferensi waktu.

## Glossary

- **Schedule**: Jadwal mingguan yang berisi assignment shift untuk semua anggota
- **Schedule Assignment**: Penugasan shift spesifik untuk satu anggota pada tanggal dan sesi tertentu
- **Session**: Periode waktu shift (Sesi 1: 08:00-12:00, Sesi 2: 13:00-17:00, Sesi 3: 17:00-21:00)
- **Availability**: Ketersediaan waktu anggota untuk mengambil shift
- **Template**: Pola jadwal yang dapat digunakan kembali untuk mempercepat pembuatan jadwal
- **Auto-Assignment**: Algoritma otomatis untuk mendistribusikan shift berdasarkan ketersediaan dan keadilan
- **Draft Schedule**: Jadwal yang masih dalam tahap penyusunan dan belum dipublikasikan
- **Published Schedule**: Jadwal yang sudah dipublikasikan dan dapat dilihat oleh semua anggota
- **Conflict**: Kondisi dimana satu anggota memiliki lebih dari satu assignment pada waktu yang sama
- **Coverage**: Persentase slot shift yang terisi dari total slot yang tersedia

## Requirements

### Requirement 1: Membuat Jadwal Baru

**User Story:** Sebagai ketua koperasi, saya ingin membuat jadwal mingguan baru, sehingga saya dapat mengatur shift anggota untuk periode tertentu.

#### Acceptance Criteria

1. WHEN ketua mengakses halaman buat jadwal, THE System SHALL menampilkan form dengan field week_start_date, week_end_date, dan notes
2. THE System SHALL memvalidasi bahwa week_start_date adalah hari Senin
3. THE System SHALL memvalidasi bahwa week_end_date adalah hari Kamis (4 hari kerja)
4. THE System SHALL mencegah pembuatan jadwal duplikat untuk periode yang sama
5. WHEN jadwal berhasil dibuat, THE System SHALL menyimpan dengan status 'draft'

### Requirement 2: Assignment Manual

**User Story:** Sebagai ketua koperasi, saya ingin menambahkan assignment shift secara manual, sehingga saya dapat mengatur shift dengan fleksibel sesuai kebutuhan khusus.

#### Acceptance Criteria

1. WHEN ketua memilih tanggal dan sesi, THE System SHALL menampilkan daftar anggota yang tersedia
2. THE System SHALL menampilkan indikator ketersediaan anggota (available, not available, already assigned)
3. WHEN ketua memilih anggota, THE System SHALL memeriksa konflik dengan assignment lain
4. IF konflik ditemukan, THEN THE System SHALL menampilkan warning dan meminta konfirmasi
5. THE System SHALL menyimpan assignment dengan status 'scheduled'

### Requirement 3: Auto-Assignment Berdasarkan Availability

**User Story:** Sebagai ketua koperasi, saya ingin sistem mengisi jadwal secara otomatis berdasarkan ketersediaan anggota, sehingga saya dapat menghemat waktu dalam penyusunan jadwal.

#### Acceptance Criteria

1. WHEN ketua mengklik tombol "Auto Assign", THE System SHALL mengambil data availability semua anggota untuk periode tersebut
2. THE System SHALL mendistribusikan shift dengan algoritma yang mempertimbangkan keadilan (jumlah shift per anggota seimbang)
3. THE System SHALL memprioritaskan anggota dengan availability tinggi
4. THE System SHALL menghindari assignment yang konflik (satu anggota tidak boleh double shift)
5. WHEN auto-assignment selesai, THE System SHALL menampilkan preview hasil dengan statistik coverage

### Requirement 4: Template Jadwal

**User Story:** Sebagai ketua koperasi, saya ingin menyimpan pola jadwal sebagai template, sehingga saya dapat menggunakan kembali pola yang sama untuk periode berikutnya.

#### Acceptance Criteria

1. WHEN ketua menyimpan jadwal sebagai template, THE System SHALL menyimpan pola assignment (hari, sesi, user_id)
2. THE System SHALL memberikan nama template yang unik
3. WHEN ketua memilih template, THE System SHALL mengisi jadwal baru dengan pola dari template
4. THE System SHALL menyesuaikan tanggal assignment sesuai periode jadwal baru
5. THE System SHALL memvalidasi bahwa anggota dalam template masih aktif

### Requirement 5: Validasi dan Conflict Detection

**User Story:** Sebagai ketua koperasi, saya ingin sistem mendeteksi konflik jadwal, sehingga tidak ada anggota yang mendapat double shift atau shift yang tidak sesuai availability.

#### Acceptance Criteria

1. WHEN assignment ditambahkan, THE System SHALL memeriksa apakah anggota sudah memiliki assignment pada tanggal dan sesi yang sama
2. THE System SHALL memeriksa apakah assignment sesuai dengan availability anggota
3. IF konflik terdeteksi, THEN THE System SHALL menampilkan error message dengan detail konflik
4. THE System SHALL menampilkan visual indicator (warna merah) untuk slot yang konflik
5. THE System SHALL mencegah penyimpanan jadwal jika masih ada konflik yang belum diselesaikan

### Requirement 6: Preview dan Edit Jadwal

**User Story:** Sebagai ketua koperasi, saya ingin melihat preview jadwal sebelum dipublikasikan, sehingga saya dapat melakukan koreksi jika diperlukan.

#### Acceptance Criteria

1. WHEN ketua mengklik "Preview", THE System SHALL menampilkan jadwal dalam format kalender mingguan
2. THE System SHALL menampilkan statistik: total assignments, coverage percentage, assignments per user
3. THE System SHALL memungkinkan edit assignment langsung dari preview (drag & drop atau click to edit)
4. THE System SHALL menampilkan anggota yang belum mendapat assignment
5. THE System SHALL menampilkan anggota yang mendapat assignment terlalu banyak atau terlalu sedikit

### Requirement 7: Publish Jadwal

**User Story:** Sebagai ketua koperasi, saya ingin mempublikasikan jadwal yang sudah final, sehingga semua anggota dapat melihat jadwal mereka.

#### Acceptance Criteria

1. WHEN ketua mengklik "Publish", THE System SHALL memvalidasi bahwa semua slot wajib sudah terisi
2. THE System SHALL memvalidasi bahwa tidak ada konflik yang belum diselesaikan
3. WHEN validasi berhasil, THE System SHALL mengubah status jadwal menjadi 'published'
4. THE System SHALL mencatat timestamp published_at dan user yang mempublikasikan
5. THE System SHALL mengirim notifikasi ke semua anggota yang mendapat assignment

### Requirement 8: Statistik dan Analytics

**User Story:** Sebagai ketua koperasi, saya ingin melihat statistik distribusi shift, sehingga saya dapat memastikan keadilan dalam pembagian shift.

#### Acceptance Criteria

1. THE System SHALL menampilkan jumlah total assignments per anggota
2. THE System SHALL menampilkan distribusi shift per sesi (pagi, siang, sore)
3. THE System SHALL menampilkan coverage rate (persentase slot terisi)
4. THE System SHALL menampilkan anggota dengan assignment terbanyak dan tersedikit
5. THE System SHALL menampilkan grafik distribusi shift untuk visualisasi yang lebih baik

### Requirement 9: Copy dari Jadwal Sebelumnya

**User Story:** Sebagai ketua koperasi, saya ingin menyalin jadwal dari minggu sebelumnya, sehingga saya dapat mempercepat pembuatan jadwal dengan pola yang mirip.

#### Acceptance Criteria

1. WHEN ketua memilih "Copy from Previous Week", THE System SHALL menampilkan daftar jadwal minggu sebelumnya
2. WHEN ketua memilih jadwal sumber, THE System SHALL menyalin semua assignments
3. THE System SHALL menyesuaikan tanggal assignment ke periode jadwal baru
4. THE System SHALL memvalidasi bahwa anggota masih aktif dan tersedia
5. THE System SHALL menandai assignment yang perlu review (anggota tidak tersedia atau sudah tidak aktif)

### Requirement 10: Bulk Operations

**User Story:** Sebagai ketua koperasi, saya ingin melakukan operasi massal pada assignments, sehingga saya dapat menghemat waktu untuk perubahan yang berulang.

#### Acceptance Criteria

1. THE System SHALL menyediakan fitur "Assign to All Sessions" untuk mengisi semua sesi dalam satu hari dengan anggota yang sama
2. THE System SHALL menyediakan fitur "Assign to All Days" untuk mengisi satu sesi di semua hari dengan anggota yang sama
3. THE System SHALL menyediakan fitur "Clear All" untuk menghapus semua assignments dalam jadwal
4. THE System SHALL menyediakan fitur "Clear Day" untuk menghapus semua assignments dalam satu hari
5. THE System SHALL meminta konfirmasi sebelum melakukan operasi massal yang destructive

### Requirement 11: Undo/Redo Functionality

**User Story:** Sebagai ketua koperasi, saya ingin dapat membatalkan perubahan yang baru saja saya lakukan, sehingga saya dapat dengan mudah memperbaiki kesalahan.

#### Acceptance Criteria

1. THE System SHALL menyimpan history perubahan assignment (maksimal 20 langkah terakhir)
2. WHEN ketua mengklik "Undo", THE System SHALL mengembalikan state ke kondisi sebelumnya
3. WHEN ketua mengklik "Redo", THE System SHALL mengembalikan perubahan yang di-undo
4. THE System SHALL menampilkan indikator jumlah undo/redo yang tersedia
5. THE System SHALL menghapus history undo ketika jadwal dipublikasikan

### Requirement 12: Export dan Print

**User Story:** Sebagai ketua koperasi, saya ingin mengekspor jadwal ke format yang dapat dicetak, sehingga saya dapat membagikan jadwal secara offline.

#### Acceptance Criteria

1. THE System SHALL menyediakan fitur export ke PDF dengan format yang rapi dan mudah dibaca
2. THE System SHALL menyediakan fitur export ke Excel untuk analisis lebih lanjut
3. THE System SHALL menyediakan fitur print preview sebelum mencetak
4. THE System SHALL menyertakan informasi periode, tanggal publikasi, dan statistik dalam export
5. THE System SHALL memformat export sesuai dengan ukuran kertas standar (A4)
