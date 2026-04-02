<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AutoCheckOutCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-checkout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis check-out absensi ketika waktu sesi berakhir';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\AttendanceService $attendanceService)
    {
        $this->info("Memulai proses auto-checkout...");
        
        $count = $attendanceService->processAutoCheckOuts();

        if ($count > 0) {
            $this->info("Berhasil memproses {$count} auto-checkout.");
            \Log::info("Auto-checkout: Memproses {$count} attendance(s)");
        } else {
            $this->info("Tidak ada absensi yang perlu di-auto-checkout.");
            \Log::info("Auto-checkout: Tidak ada attendance yang perlu diproses");
        }
    }
}
