<?php

namespace App\Console\Commands;

use App\Models\Availability;
use App\Models\Schedule;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAvailabilityReminder extends Command
{
    protected $signature = 'schedule:send-reminder';

    protected $description = 'Send reminder to users who haven\'t submitted availability';

    public function handle()
    {
        $nextMonday = Carbon::now()->next(Carbon::MONDAY);

        $schedule = Schedule::where('week_start_date', $nextMonday->toDateString())
            ->where('status', 'draft')
            ->first();

        if (! $schedule) {
            $this->info('No schedule found for next week');

            return;
        }

        $activeUsers = User::active()->get();
        $submittedUserIds = Availability::where('schedule_id', $schedule->id)
            ->where('status', 'submitted')
            ->pluck('user_id')
            ->toArray();

        $usersNotSubmitted = $activeUsers->whereNotIn('id', $submittedUserIds);

        foreach ($usersNotSubmitted as $user) {
            NotificationService::send(
                $user,
                'availability_reminder',
                'Reminder: Input Jadwal Kosong',
                "Jangan lupa input jadwal kosong untuk minggu {$schedule->week_start_date->format('d M')} - {$schedule->week_end_date->format('d M')}. Deadline: Minggu 23:59",
                null,
                route('schedule.availability')
            );
        }

        $this->info("Reminder sent to {$usersNotSubmitted->count()} users");
    }
}
