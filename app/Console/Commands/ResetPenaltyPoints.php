<?php

namespace App\Console\Commands;

use App\Models\Penalty;
use App\Models\PenaltyHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ResetPenaltyPoints extends Command
{
    protected $signature = 'penalty:reset-points';

    protected $description = 'Reset penalty points based on reset period';

    public function handle()
    {
        $resetMonths = (int) setting('penalty.reset_period_months', 6);
        $resetDate = Carbon::now()->subMonths($resetMonths);

        // Get users with active penalties
        $usersWithPenalties = User::whereHas('penalties', function ($query) use ($resetDate) {
            $query->where('status', 'active')
                ->where('date', '<=', $resetDate);
        })->get();

        foreach ($usersWithPenalties as $user) {
            $activePenalties = $user->penalties()
                ->where('status', 'active')
                ->where('date', '<=', $resetDate)
                ->get();

            $totalPoints = $activePenalties->sum('points');
            $totalViolations = $activePenalties->count();

            if ($totalPoints > 0) {
                // Create penalty history record
                PenaltyHistory::create([
                    'user_id' => $user->id,
                    'period_start' => $resetDate->copy()->subMonths($resetMonths)->startOfMonth(),
                    'period_end' => $resetDate->endOfMonth(),
                    'total_points' => $totalPoints,
                    'total_violations' => $totalViolations,
                    'status' => 'archived',
                    'notes' => "Auto-archived after {$resetMonths} months",
                ]);

                // Archive penalties
                $activePenalties->each(function ($penalty) {
                    $penalty->update(['status' => 'expired']);
                });

                $this->info("Archived {$totalPoints} points for user {$user->name}");
            }
        }

        $this->info("Penalty reset completed for {$usersWithPenalties->count()} users");
    }
}
