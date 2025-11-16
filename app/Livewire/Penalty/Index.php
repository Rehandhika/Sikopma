<?php

namespace App\Livewire\Penalty;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Penalty;

class Index extends Component
{
    use WithPagination;

    public $statusFilter = '';

    public function render()
    {
        $penalties = Penalty::query()
            ->where('user_id', auth()->id())
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->with('penaltyType:id,name,code,points')
            ->orderBy('date', 'desc')
            ->paginate(15);

        // Optimize summary with single query
        $summaryData = Penalty::where('user_id', auth()->id())
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(CASE WHEN status = "active" THEN points ELSE 0 END) as total_points')
            ->selectRaw('SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active')
            ->selectRaw('SUM(CASE WHEN status = "appealed" THEN 1 ELSE 0 END) as appealed')
            ->selectRaw('SUM(CASE WHEN status = "dismissed" THEN 1 ELSE 0 END) as dismissed')
            ->selectRaw('SUM(CASE WHEN status = "expired" THEN 1 ELSE 0 END) as expired')
            ->first();

        $summary = [
            'total_points' => $summaryData->total_points ?? 0,
            'count' => $summaryData->count ?? 0,
            'by_status' => [
                'active' => $summaryData->active ?? 0,
                'appealed' => $summaryData->appealed ?? 0,
                'dismissed' => $summaryData->dismissed ?? 0,
                'expired' => $summaryData->expired ?? 0,
            ],
        ];

        return view('livewire.penalty.index', [
            'penalties' => $penalties,
            'summary' => $summary,
        ])->layout('layouts.app')->title('Penalti Saya');
    }
}
