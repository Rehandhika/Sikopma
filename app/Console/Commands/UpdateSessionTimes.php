<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\SystemSetting;
use App\Models\ScheduleAssignment;

class UpdateSessionTimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:update-session-times 
                            {--dry-run : Run without making changes}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update session times to new schedule (07:30-10:00, 10:20-12:50, 13:30-16:00)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('===========================================');
        $this->info('Update Session Times Command');
        $this->info('===========================================');
        $this->newLine();

        // Show new times
        $this->info('New Session Times:');
        $this->line('  Sesi 1: 07:30 - 10:00');
        $this->line('  Sesi 2: 10:20 - 12:50');
        $this->line('  Sesi 3: 13:30 - 16:00');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Count affected records
        $assignmentCount = ScheduleAssignment::count();
        $this->info("Total Schedule Assignments to update: {$assignmentCount}");
        $this->newLine();

        // Confirm
        if (!$force && !$isDryRun) {
            if (!$this->confirm('Do you want to proceed with updating session times?')) {
                $this->warn('Operation cancelled.');
                return 0;
            }
        }

        DB::beginTransaction();

        try {
            // Update System Settings
            $this->info('Updating System Settings...');
            
            $settings = [
                'schedule.session_1_start' => '07:30',
                'schedule.session_1_end' => '10:00',
                'schedule.session_2_start' => '10:20',
                'schedule.session_2_end' => '12:50',
                'schedule.session_3_start' => '13:30',
                'schedule.session_3_end' => '16:00',
            ];

            foreach ($settings as $key => $value) {
                if (!$isDryRun) {
                    SystemSetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $value, 'type' => 'time']
                    );
                }
                $this->line("  ✓ {$key} = {$value}");
            }

            $this->newLine();

            // Update Schedule Assignments
            $this->info('Updating Schedule Assignments...');

            $sessionUpdates = [
                1 => ['start' => '07:30:00', 'end' => '10:00:00'],
                2 => ['start' => '10:20:00', 'end' => '12:50:00'],
                3 => ['start' => '13:30:00', 'end' => '16:00:00'],
            ];

            foreach ($sessionUpdates as $session => $times) {
                $count = ScheduleAssignment::where('session', $session)->count();
                
                if (!$isDryRun) {
                    ScheduleAssignment::where('session', $session)
                        ->update([
                            'time_start' => $times['start'],
                            'time_end' => $times['end'],
                        ]);
                }

                $this->line("  ✓ Session {$session}: {$times['start']} - {$times['end']} ({$count} records)");
            }

            $this->newLine();

            if (!$isDryRun) {
                DB::commit();
                $this->info('✓ All updates completed successfully!');
            } else {
                DB::rollBack();
                $this->info('✓ Dry run completed - no changes made');
            }

            $this->newLine();
            $this->info('Next steps:');
            $this->line('  1. Clear cache: php artisan cache:clear');
            $this->line('  2. Clear config: php artisan config:clear');
            $this->line('  3. Test schedule generation and attendance check-in');

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error('Error updating session times:');
            $this->error($e->getMessage());
            
            return 1;
        }
    }
}
