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
            ->with('penaltyType')
            ->orderBy('date', 'desc')
            ->paginate(15);

        $summary = [
            'total_points' => Penalty::where('user_id', auth()->id())
                ->where('status', 'active')
                ->sum('points'),
            'count' => Penalty::where('user_id', auth()->id())->count(),
            'by_status' => [
                'active' => Penalty::where('user_id', auth()->id())->where('status', 'active')->count(),
                'appealed' => Penalty::where('user_id', auth()->id())->where('status', 'appealed')->count(),
                'dismissed' => Penalty::where('user_id', auth()->id())->where('status', 'dismissed')->count(),
                'expired' => Penalty::where('user_id', auth()->id())->where('status', 'expired')->count(),
            ],
        ];

        return view('livewire.penalty.index', [
            'penalties' => $penalties,
            'summary' => $summary,
        ])->layout('layouts.app')->title('Penalti Saya');
    }
}
