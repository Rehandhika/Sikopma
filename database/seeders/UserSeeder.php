<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Wirus Angkatan 66 - Data Anggota
     *
     * Struktur Organisasi:
     * - Ketua: Pimpinan tertinggi
     * - Wakil Ketua: Pendamping ketua
     * - Sekretaris I & II: Administrasi
     * - Bendahara Umum: Keuangan utama
     * - Bendahara Kegiatan: Keuangan kegiatan
     * - Bendahara Toko: Keuangan toko
     * - Koordinator Divisi: Kepala divisi masing-masing
     * - Anggota: Member biasa
     */
    public function run(): void
    {
        $csvPath = database_path('Data/kredensial.csv');
        
        if (!file_exists($csvPath)) {
            $this->command->error("❌ File CSV kredensial tidak ditemukan di: $csvPath");
            return;
        }

        $csvData = array_map('str_getcsv', file($csvPath));
        $header = array_shift($csvData);
        
        $count = 0;
        foreach ($csvData as $row) {
            $data = array_combine($header, $row);
            
            // Cari role dari data statis lama atau default ke 'Anggota'
            $oldMembers = collect($this->getMembers());
            $oldMember = $oldMembers->where('nim', $data['nim'])->first();
            $role = $oldMember['role'] ?? 'anggota';

            // Create or Update User
            $nim = trim((string) $data['nim']);
            $user = User::withTrashed()->where('nim', $nim)->first();
            
            if ($user) {
                $user->restore(); // Restore if soft-deleted
                $user->update([
                    'name' => trim($data['nama']),
                    'email' => trim($data['email']),
                    'password' => Hash::make($data['password']),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
            } else {
                $user = User::create([
                    'nim' => $nim,
                    'name' => trim($data['nama']),
                    'email' => trim($data['email']),
                    'password' => Hash::make($data['password']),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
            }

            // Sync Role
            $user->syncRoles([$role]);

            // Dispatch Email Job
            \App\Jobs\SendInitialCredentialsJob::dispatch($user, $data['password']);
            
            $count++;
        }

        $this->command->info("✅ Berhasil memproses $count anggota dari CSV.");
        $this->command->info("📧 Email kredensial telah masuk ke antrean (queue).");
        $this->command->info("🚀 Jalankan 'php artisan queue:work --queue=emails,default' untuk mengirim.");
    }

    /**
     * Get member data array.
     *
     * @return array<int, array{name: string, nim: string, jabatan: string, role: string, email: string}>
     */
    private function getMembers(): array
    {
        return [
            // =====================================================
            // PIMPINAN INTI
            // =====================================================
            [
                'name' => 'Diva Afdholia R.',
                'nim' => '222413550',
                'jabatan' => 'Ketua',
                'role' => 'ketua',
                'email' => '222413550@stis.ac.id',
            ],
            [
                'name' => 'Fikri Adi Nugraha',
                'nim' => '212413577',
                'jabatan' => 'Wakil Ketua',
                'role' => 'wakil-ketua',
                'email' => '212413577@stis.ac.id',
            ],

            // =====================================================
            // SEKRETARIAT
            // =====================================================
            [
                'name' => 'Defila Cahyati',
                'nim' => '222413540',
                'jabatan' => 'Sekretaris I',
                'role' => 'sekretaris',
                'email' => '222413540@stis.ac.id',
            ],
            [
                'name' => 'Raziq Alzam Fadlullah',
                'nim' => '112413751',
                'jabatan' => 'Sekretaris II',
                'role' => 'sekretaris',
                'email' => '112413751@stis.ac.id',
            ],

            // =====================================================
            // BENDAHARA
            // =====================================================
            [
                'name' => 'Siti Rahmadhani Zaskya Mantika',
                'nim' => '222413785',
                'jabatan' => 'Bendahara Umum',
                'role' => 'bendahara',
                'email' => '222413785@stis.ac.id',
            ],
            [
                'name' => 'Mei Indriyanti Syamsi',
                'nim' => '212413652',
                'jabatan' => 'Bendahara Kegiatan',
                'role' => 'bendahara',
                'email' => '212413652@stis.ac.id',
            ],
            [
                'name' => 'Putra Irvan Kala\'padang',
                'nim' => '212413736',
                'jabatan' => 'Bendahara Toko',
                'role' => 'bendahara',
                'email' => '212413736@stis.ac.id',
            ],

            // =====================================================
            // KOORDINATOR DIVISI
            // =====================================================
            [
                'name' => 'Diah Puji Pramesti',
                'nim' => '212413547',
                'jabatan' => 'Koordinator Toko',
                'role' => 'koordinator-toko',
                'email' => '212413547@stis.ac.id',
            ],
            [
                'name' => 'Ego Stiven Rafliza',
                'nim' => '212413552',
                'jabatan' => 'Koordinator PSDA',
                'role' => 'koordinator-psda',
                'email' => '212413552@stis.ac.id',
            ],
            [
                'name' => 'Desvita Prabawaningrum',
                'nim' => '212413544',
                'jabatan' => 'Koordinator Humsar',
                'role' => 'koordinator-humsar',
                'email' => '212413544@stis.ac.id',
            ],
            [
                'name' => 'Fatimah Az Zahra',
                'nim' => '222413569',
                'jabatan' => 'Koordinator Produksi dan Pengadaan',
                'role' => 'koordinator-produksi',
                'email' => '222413569@stis.ac.id',
            ],
            [
                'name' => 'Rehandhika Arya Pratama',
                'nim' => '222413752',
                'jabatan' => 'Koordinator IT',
                'role' => 'Super Admin',
                'email' => '222413752@stis.ac.id',
            ],
            [
                'name' => 'Risyda Azifatil Maghfira',
                'nim' => '222413763',
                'jabatan' => 'Koordinator Desain',
                'role' => 'koordinator-desain',
                'email' => '222413763@stis.ac.id',
            ],

            // =====================================================
            // ANGGOTA
            // =====================================================
            [
                'name' => 'Rahmat Budiyanto',
                'nim' => '222413742',
                'jabatan' => 'Anggota Produksi dan Pengadaan',
                'role' => 'anggota',
                'email' => '222413742@stis.ac.id',
            ],
        ];
    }
}
