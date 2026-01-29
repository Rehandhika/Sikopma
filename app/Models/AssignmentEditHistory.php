<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentEditHistory extends Model
{
    use HasFactory;

    protected $table = 'assignment_edit_history';

    protected $fillable = [
        'assignment_id',
        'schedule_id',
        'edited_by',
        'action',
        'old_values',
        'new_values',
        'reason',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ScheduleAssignment::class, 'assignment_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    // Scopes
    public function scopeForSchedule($query, int $scheduleId)
    {
        return $query->where('schedule_id', $scheduleId);
    }

    public function scopeByEditor($query, int $userId)
    {
        return $query->where('edited_by', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper Methods
    public function getChangeSummary(): string
    {
        $action = match ($this->action) {
            'created' => 'menambahkan assignment',
            'updated' => 'mengubah assignment',
            'deleted' => 'menghapus assignment',
            'swapped' => 'menukar assignment',
            default => 'melakukan aksi pada assignment',
        };

        $editorName = $this->editor ? $this->editor->name : 'Unknown';
        $date = $this->created_at->locale('id')->isoFormat('D MMMM Y HH:mm');

        return "{$editorName} {$action} pada {$date}";
    }

    public function getAffectedFields(): array
    {
        if (! $this->old_values || ! $this->new_values) {
            return [];
        }

        $affected = [];
        $oldValues = $this->old_values;
        $newValues = $this->new_values;

        foreach ($newValues as $field => $newValue) {
            $oldValue = $oldValues[$field] ?? null;

            if ($oldValue !== $newValue) {
                $affected[] = [
                    'field' => $field,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'label' => $this->getFieldLabel($field),
                ];
            }
        }

        return $affected;
    }

    private function getFieldLabel(string $field): string
    {
        return match ($field) {
            'user_id' => 'Anggota',
            'date' => 'Tanggal',
            'session' => 'Sesi',
            'day' => 'Hari',
            'status' => 'Status',
            'notes' => 'Catatan',
            default => ucfirst(str_replace('_', ' ', $field)),
        };
    }
}
