<?php

namespace App\Livewire\Penalty;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\Penalty;
use Illuminate\Support\Facades\DB;

#[Title('Penalti Saya')]
class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    #[Computed]
    public function summary()
    {
        $userId = auth()->id();
        
        $result = DB::select("
            SELECT 
                COUNT(*) as count,
                COALESCE(SUM(CASE WHEN status = 'active' THEN points ELSE 0 END), 0) as total_points,
                SUM(status = 'active') as active,
                SUM(status = 'appealed') as appealed,
                SUM(status = 'dismissed') as dismissed,
                SUM(status = 'expired') as expired
            FROM penalties 
            WHERE user_id = ?
        ", [$userId]);

        $data = $result[0] ?? (object)['count' => 0, 'total_points' => 0, 'active' => 0, 'appealed' => 0, 'dismissed' => 0, 'expired' => 0];

        return [
            'total_points' => (int)$data->total_points,
            'count' => (int)$data->count,
            'active' => (int)$data->active,
            'appealed' => (int)$data->appealed,
            'dismissed' => (int)$data->dismissed,
            'expired' => (int)$data->expired,
        ];
    }

    public function render()
    {
        $penalties = Penalty::query()
            ->where('user_id', auth()->id())
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->with('penaltyType:id,name,code')
            ->select('id', 'penalty_type_id', 'date', 'points', 'description', 'status')
            ->orderByDesc('date')
            ->paginate(15);

        return view('livewire.penalty.index', ['penalties' => $penalties])
            ->layout('layouts.app');
    }
}
