<?php

return [
    /*
    |--------------------------------------------------------------------------
    | File Storage Language Lines (Indonesian)
    |--------------------------------------------------------------------------
    |
    | Pesan error dan notifikasi untuk sistem penyimpanan file.
    | Semua pesan dalam Bahasa Indonesia untuk user-friendly experience.
    |
    */

    'validation' => [
        'file_too_large' => 'Ukuran file terlalu besar. Maksimal :max MB.',
        'invalid_type' => 'Tipe file tidak didukung. Gunakan :types.',
        'invalid_image' => 'File bukan gambar yang valid.',
        'mime_mismatch' => 'Konten file tidak sesuai dengan tipe yang dideklarasikan.',
        'file_required' => 'File harus diunggah.',
        'file_empty' => 'File tidak boleh kosong.',
        'invalid_path' => 'Format path tidak valid: :path',
        'invalid_file_type' => 'Tipe file ":type" tidak valid. Tipe yang didukung: :valid_types.',
        'invalid_base64' => 'Format base64 tidak valid.',
        'corrupted_file' => 'File rusak atau tidak dapat dibaca.',
        'checksum_mismatch' => 'Verifikasi integritas file gagal.',
    ],

    'processing' => [
        'resize_failed' => 'Gagal mengubah ukuran gambar.',
        'convert_failed' => 'Gagal mengkonversi format gambar.',
        'variant_failed' => 'Gagal membuat varian gambar.',
        'thumbnail_failed' => 'Gagal membuat thumbnail gambar.',
        'general_failed' => 'Gagal memproses file.',
        'webp_conversion_failed' => 'Gagal mengkonversi gambar ke format WebP.',
        'image_read_failed' => 'Gagal membaca file gambar.',
        'image_save_failed' => 'Gagal menyimpan hasil pemrosesan gambar.',
    ],

    'storage' => [
        'disk_unavailable' => 'Storage tidak tersedia. Silakan coba lagi.',
        'write_failed' => 'Gagal menyimpan file.',
        'delete_failed' => 'Gagal menghapus file.',
        'quota_exceeded' => 'Kuota penyimpanan telah terlampaui.',
        'file_not_found' => 'File tidak ditemukan: :path',
        'variant_not_found' => 'Varian :variant tidak ditemukan untuk file: :path',
        'general_error' => 'Terjadi kesalahan pada penyimpanan file: :message',
        'read_failed' => 'Gagal membaca file.',
        'copy_failed' => 'Gagal menyalin file.',
        'move_failed' => 'Gagal memindahkan file.',
        'directory_create_failed' => 'Gagal membuat direktori.',
    ],

    'cleanup' => [
        'started' => 'Pembersihan file dimulai.',
        'completed' => 'Pembersihan file selesai. :count file dihapus.',
        'dry_run' => 'Mode simulasi: :count file akan dihapus.',
        'error' => 'Gagal membersihkan file: :message',
        'orphan_found' => 'Ditemukan :count file orphan.',
        'temp_cleaned' => ':count file temporary berhasil dihapus.',
        'no_orphans' => 'Tidak ada file orphan yang ditemukan.',
    ],

    'migration' => [
        'started' => 'Migrasi file dimulai.',
        'completed' => 'Migrasi file selesai. :count file berhasil dimigrasi.',
        'failed' => 'Gagal migrasi file: :path',
        'skipped' => 'File dilewati: :path',
        'in_progress' => 'Migrasi sedang berjalan. :current dari :total file.',
        'variants_generated' => ':count varian berhasil dibuat.',
        'database_updated' => 'Referensi database berhasil diperbarui.',
        'rollback_started' => 'Rollback migrasi dimulai.',
        'rollback_completed' => 'Rollback migrasi selesai.',
    ],

    'monitoring' => [
        'warning' => 'Peringatan: Penggunaan storage mencapai :percentage%.',
        'critical' => 'Kritis: Penggunaan storage mencapai :percentage%.',
        'normal' => 'Penggunaan storage normal: :percentage%.',
        'stats_retrieved' => 'Statistik storage berhasil diambil.',
        'threshold_exceeded' => 'Batas penggunaan storage terlampaui.',
    ],

    'security' => [
        'access_denied' => 'Akses ke file ditolak.',
        'unauthorized' => 'Anda tidak memiliki izin untuk mengakses file ini.',
        'path_traversal_detected' => 'Terdeteksi upaya akses path tidak valid.',
        'malicious_file_detected' => 'File terdeteksi berbahaya dan ditolak.',
        'signed_url_expired' => 'Link akses file telah kedaluwarsa.',
        'signed_url_invalid' => 'Link akses file tidak valid.',
        'authentication_required' => 'Autentikasi diperlukan untuk mengakses file ini.',
        'role_insufficient' => 'Role Anda tidak memiliki izin untuk ukuran file ini.',
    ],

    'cache' => [
        'invalidated' => 'Cache file berhasil dihapus.',
        'cleared' => 'Semua cache file berhasil dihapus.',
        'refresh_failed' => 'Gagal memperbarui cache.',
    ],

    'upload' => [
        'started' => 'Proses upload dimulai.',
        'completed' => 'File berhasil diunggah.',
        'failed' => 'Gagal mengunggah file.',
        'cancelled' => 'Upload dibatalkan.',
        'retry_queued' => 'Upload dijadwalkan untuk dicoba ulang.',
    ],
];
