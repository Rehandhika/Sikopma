<?php

namespace App\Services;

use App\Models\{Schedule, ScheduleTemplate, ScheduleAssignment, User};
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TemplateService
{
    /**
     * Create template from schedule
     */
    public function createTemplate(Schedule $schedule, string $name, string $description = ''): ScheduleTemplate
    {
        // Extract pattern from schedule
        $pattern = $this->extractPattern($schedule);

        if (empty($pattern)) {
            throw new \Exception('Jadwal tidak memiliki assignment untuk dijadikan template.');
        }

        $template = ScheduleTemplate::create([
            'name' => $name,
            'description' => $description,
            'created_by' => auth()->id(),
            'pattern' => $pattern,
            'is_public' => false,
            'usage_count' => 0,
        ]);

        Log::info('Template created', [
            'template_id' => $template->id,
            'name' => $name,
            'assignments_count' => count($pattern),
        ]);

        return $template;
    }

    /**
     * Extract pattern from schedule
     */
    private function extractPattern(Schedule $schedule): array
    {
        $assignments = $schedule->assignments()->with('user:id,name')->get();
        $pattern = [];

        foreach ($assignments as $assignment) {
            $pattern[] = [
                'day' => $assignment->day,
                'session' => $assignment->session,
                'user_id' => $assignment->user_id,
                'user_name' => $assignment->user->name ?? 'Unknown',
                'time_start' => $assignment->time_start,
                'time_end' => $assignment->time_end,
            ];
        }

        return $pattern;
    }

    /**
     * Apply template to schedule
     */
    public function applyTemplate(ScheduleTemplate $template, Schedule $targetSchedule): array
    {
        if (!$targetSchedule->canEdit()) {
            throw new \Exception('Jadwal target tidak dapat diubah.');
        }

        $pattern = $template->pattern;
        $startDate = Carbon::parse($targetSchedule->week_start_date);
        
        $assignments = [];
        $skipped = [];
        $conflicts = [];

        DB::beginTransaction();
        try {
            foreach ($pattern as $item) {
                // Find the date for this day
                $dayIndex = $this->getDayIndex($item['day']);
                $date = $startDate->copy()->addDays($dayIndex);

                // Validate user still exists and is active
                $user = User::find($item['user_id']);
                if (!$user) {
                    $skipped[] = [
                        'reason' => 'user_not_found',
                        'day' => $item['day'],
                        'session' => $item['session'],
                        'user_id' => $item['user_id'],
                    ];
                    continue;
                }

                if ($user->status !== 'active') {
                    $skipped[] = [
                        'reason' => 'user_inactive',
                        'day' => $item['day'],
                        'session' => $item['session'],
                        'user_id' => $item['user_id'],
                        'user_name' => $user->name,
                        'user_status' => $user->status,
                    ];
                    continue;
                }

                // Check for conflicts
                $existingAssignment = ScheduleAssignment::where('schedule_id', $targetSchedule->id)
                    ->where('date', $date)
                    ->where('session', $item['session'])
                    ->first();

                if ($existingAssignment) {
                    $conflicts[] = [
                        'reason' => 'slot_already_filled',
                        'day' => $item['day'],
                        'session' => $item['session'],
                        'date' => $date->toDateString(),
                        'existing_user' => $existingAssignment->user->name ?? 'Unknown',
                        'template_user' => $user->name,
                    ];
                    continue;
                }

                // Check if user already has assignment at this time
                $userConflict = ScheduleAssignment::where('user_id', $user->id)
                    ->where('date', $date)
                    ->where('session', $item['session'])
                    ->exists();

                if ($userConflict) {
                    $conflicts[] = [
                        'reason' => 'user_double_assignment',
                        'day' => $item['day'],
                        'session' => $item['session'],
                        'date' => $date->toDateString(),
                        'user_name' => $user->name,
                    ];
                    continue;
                }

                // Create assignment
                $assignment = ScheduleAssignment::create([
                    'schedule_id' => $targetSchedule->id,
                    'user_id' => $user->id,
                    'date' => $date,
                    'day' => $item['day'],
                    'session' => $item['session'],
                    'time_start' => $item['time_start'],
                    'time_end' => $item['time_end'],
                    'status' => 'scheduled',
                ]);

                $assignments[] = $assignment;
            }

            // Update schedule statistics
            $targetSchedule->calculateCoverage();

            // Increment template usage count
            $template->incrementUsage();

            DB::commit();

            Log::info('Template applied', [
                'template_id' => $template->id,
                'schedule_id' => $targetSchedule->id,
                'assignments_created' => count($assignments),
                'skipped' => count($skipped),
                'conflicts' => count($conflicts),
            ]);

            return [
                'success' => true,
                'assignments' => $assignments,
                'skipped' => $skipped,
                'conflicts' => $conflicts,
                'statistics' => [
                    'total_pattern_items' => count($pattern),
                    'assignments_created' => count($assignments),
                    'skipped_count' => count($skipped),
                    'conflicts_count' => count($conflicts),
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get day index (0-3 for Monday-Thursday)
     */
    private function getDayIndex(string $day): int
    {
        $days = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
        ];

        return $days[strtolower($day)] ?? 0;
    }

    /**
     * List templates
     */
    public function listTemplates(User $user, array $filters = []): Collection
    {
        $query = ScheduleTemplate::query();

        // Show user's own templates + public templates
        $query->where(function($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhere('is_public', true);
        });

        // Apply filters
        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['is_public'])) {
            $query->where('is_public', $filters['is_public']);
        }

        if (isset($filters['created_by_me'])) {
            $query->where('created_by', $user->id);
        }

        // Order by usage count (popular first) or created date
        $orderBy = $filters['order_by'] ?? 'usage_count';
        $orderDirection = $filters['order_direction'] ?? 'desc';
        $query->orderBy($orderBy, $orderDirection);

        return $query->with('creator:id,name')->get();
    }

    /**
     * Delete template
     */
    public function deleteTemplate(ScheduleTemplate $template): bool
    {
        // Check permission
        if (!$template->canDelete(auth()->user())) {
            throw new \Exception('Anda tidak memiliki izin untuk menghapus template ini.');
        }

        $template->delete();

        Log::info('Template deleted', [
            'template_id' => $template->id,
            'name' => $template->name,
        ]);

        return true;
    }

    /**
     * Update template
     */
    public function updateTemplate(ScheduleTemplate $template, array $data): ScheduleTemplate
    {
        // Check permission
        if (!$template->canEdit(auth()->user())) {
            throw new \Exception('Anda tidak memiliki izin untuk mengubah template ini.');
        }

        $template->update([
            'name' => $data['name'] ?? $template->name,
            'description' => $data['description'] ?? $template->description,
            'is_public' => $data['is_public'] ?? $template->is_public,
        ]);

        Log::info('Template updated', [
            'template_id' => $template->id,
            'name' => $template->name,
        ]);

        return $template;
    }

    /**
     * Get template preview
     */
    public function getTemplatePreview(ScheduleTemplate $template): array
    {
        $pattern = $template->pattern;
        $summary = $template->getPatternSummary();

        // Group by day and session
        $grid = [];
        foreach ($pattern as $item) {
            $day = $item['day'];
            $session = $item['session'];
            
            if (!isset($grid[$day])) {
                $grid[$day] = [];
            }
            
            $grid[$day][$session] = [
                'user_id' => $item['user_id'],
                'user_name' => $item['user_name'],
                'time_start' => $item['time_start'],
                'time_end' => $item['time_end'],
            ];
        }

        return [
            'template' => $template,
            'summary' => $summary,
            'grid' => $grid,
        ];
    }

    /**
     * Duplicate template
     */
    public function duplicateTemplate(ScheduleTemplate $template, string $newName): ScheduleTemplate
    {
        $newTemplate = ScheduleTemplate::create([
            'name' => $newName,
            'description' => $template->description . ' (Copy)',
            'created_by' => auth()->id(),
            'pattern' => $template->pattern,
            'is_public' => false,
            'usage_count' => 0,
        ]);

        Log::info('Template duplicated', [
            'original_template_id' => $template->id,
            'new_template_id' => $newTemplate->id,
            'new_name' => $newName,
        ]);

        return $newTemplate;
    }
}
