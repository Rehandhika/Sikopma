<?php

namespace App\Services;

use App\Models\Schedule;
use Carbon\Carbon;

class ScheduleExportService
{
    /**
     * Export schedule to PDF
     * Note: Requires DomPDF package (composer require barryvdh/laravel-dompdf)
     */
    public function exportToPdf(Schedule $schedule): string
    {
        // This is a placeholder implementation
        // Actual implementation will require DomPDF package
        
        $data = $this->prepareExportData($schedule);
        
        // TODO: Implement PDF generation using DomPDF
        // Example:
        // $pdf = PDF::loadView('exports.schedule-pdf', $data);
        // return $pdf->download('schedule-' . $schedule->id . '.pdf');
        
        return 'PDF export not yet implemented. Install barryvdh/laravel-dompdf package.';
    }

    /**
     * Export schedule to Excel
     * Note: Requires Laravel Excel package (composer require maatwebsite/excel)
     */
    public function exportToExcel(Schedule $schedule): string
    {
        // This is a placeholder implementation
        // Actual implementation will require Laravel Excel package
        
        $data = $this->prepareExportData($schedule);
        
        // TODO: Implement Excel export using Laravel Excel
        // Example:
        // return Excel::download(new ScheduleExport($schedule), 'schedule-' . $schedule->id . '.xlsx');
        
        return 'Excel export not yet implemented. Install maatwebsite/excel package.';
    }

    /**
     * Prepare data for export
     */
    private function prepareExportData(Schedule $schedule): array
    {
        $assignments = $schedule->assignments()
            ->with('user:id,name,nim')
            ->orderBy('date')
            ->orderBy('session')
            ->get();

        // Group by date and session
        $grid = [];
        $startDate = Carbon::parse($schedule->week_start_date);
        
        for ($day = 0; $day < 4; $day++) {
            $date = $startDate->copy()->addDays($day);
            $dateStr = $date->format('Y-m-d');
            $dayName = $date->locale('id')->dayName;
            
            $grid[$dateStr] = [
                'date' => $date,
                'day_name' => $dayName,
                'sessions' => [],
            ];
            
            for ($session = 1; $session <= 3; $session++) {
                $assignment = $assignments->first(function($a) use ($dateStr, $session) {
                    return $a->date->format('Y-m-d') === $dateStr && $a->session == $session;
                });
                
                $grid[$dateStr]['sessions'][$session] = [
                    'session' => $session,
                    'time' => $this->getSessionTime($session),
                    'assignment' => $assignment,
                    'user' => $assignment ? $assignment->user : null,
                ];
            }
        }

        // Statistics
        $statistics = $schedule->getStatistics();

        return [
            'schedule' => $schedule,
            'grid' => $grid,
            'statistics' => $statistics,
            'generated_at' => now(),
            'generated_by' => auth()->user(),
        ];
    }

    /**
     * Get session time label
     */
    private function getSessionTime(int $session): string
    {
        $times = [
            1 => '07:30 - 10:00',
            2 => '10:20 - 12:50',
            3 => '13:30 - 16:00',
        ];

        return $times[$session] ?? '';
    }

    /**
     * Generate HTML for print preview
     */
    public function generatePrintHtml(Schedule $schedule): string
    {
        $data = $this->prepareExportData($schedule);
        
        $html = '<html><head>';
        $html .= '<style>';
        $html .= 'body { font-family: Arial, sans-serif; margin: 20px; }';
        $html .= 'h1 { text-align: center; }';
        $html .= 'table { width: 100%; border-collapse: collapse; margin-top: 20px; }';
        $html .= 'th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }';
        $html .= 'th { background-color: #f3f4f6; font-weight: bold; }';
        $html .= '.header { margin-bottom: 20px; }';
        $html .= '.statistics { margin-top: 20px; padding: 15px; background-color: #f9fafb; border-radius: 8px; }';
        $html .= '@media print { body { margin: 0; } }';
        $html .= '</style>';
        $html .= '</head><body>';
        
        // Header
        $html .= '<div class="header">';
        $html .= '<h1>Jadwal Shift Koperasi</h1>';
        $html .= '<p><strong>Periode:</strong> ' . $schedule->week_start_date->format('d M Y') . ' - ' . $schedule->week_end_date->format('d M Y') . '</p>';
        $html .= '<p><strong>Status:</strong> ' . ucfirst($schedule->status) . '</p>';
        if ($schedule->published_at) {
            $html .= '<p><strong>Dipublikasikan:</strong> ' . $schedule->published_at->format('d M Y H:i') . '</p>';
        }
        $html .= '</div>';
        
        // Schedule table
        $html .= '<table>';
        $html .= '<thead><tr>';
        $html .= '<th>Hari/Tanggal</th>';
        $html .= '<th>Sesi 1<br>07:30-10:00</th>';
        $html .= '<th>Sesi 2<br>10:20-12:50</th>';
        $html .= '<th>Sesi 3<br>13:30-16:00</th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';
        
        foreach ($data['grid'] as $dateStr => $dayData) {
            $html .= '<tr>';
            $html .= '<td><strong>' . $dayData['day_name'] . '</strong><br>' . $dayData['date']->format('d M Y') . '</td>';
            
            foreach ($dayData['sessions'] as $sessionData) {
                $html .= '<td>';
                if ($sessionData['user']) {
                    $html .= $sessionData['user']->name;
                    if ($sessionData['user']->nim) {
                        $html .= '<br><small>' . $sessionData['user']->nim . '</small>';
                    }
                } else {
                    $html .= '<em style="color: #999;">Belum diisi</em>';
                }
                $html .= '</td>';
            }
            
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        
        // Statistics
        $html .= '<div class="statistics">';
        $html .= '<h3>Statistik</h3>';
        $html .= '<p><strong>Total Assignments:</strong> ' . $data['statistics']['total_assignments'] . ' / ' . $schedule->total_slots . '</p>';
        $html .= '<p><strong>Coverage Rate:</strong> ' . round($data['statistics']['coverage_rate'], 1) . '%</p>';
        $html .= '<p><strong>Unique Users:</strong> ' . $data['statistics']['unique_users'] . '</p>';
        $html .= '</div>';
        
        // Footer
        $html .= '<p style="margin-top: 30px; text-align: center; color: #666; font-size: 12px;">';
        $html .= 'Dicetak pada: ' . now()->format('d M Y H:i') . ' oleh ' . auth()->user()->name;
        $html .= '</p>';
        
        $html .= '</body></html>';
        
        return $html;
    }

    /**
     * Generate CSV data
     */
    public function generateCsv(Schedule $schedule): string
    {
        $data = $this->prepareExportData($schedule);
        
        $csv = "Jadwal Shift Koperasi\n";
        $csv .= "Periode: " . $schedule->week_start_date->format('d M Y') . " - " . $schedule->week_end_date->format('d M Y') . "\n\n";
        
        $csv .= "Hari/Tanggal,Sesi 1 (07:30-10:00),Sesi 2 (10:20-12:50),Sesi 3 (13:30-16:00)\n";
        
        foreach ($data['grid'] as $dateStr => $dayData) {
            $csv .= $dayData['day_name'] . ' ' . $dayData['date']->format('d M Y');
            
            foreach ($dayData['sessions'] as $sessionData) {
                $csv .= ',';
                if ($sessionData['user']) {
                    $csv .= $sessionData['user']->name . ' (' . $sessionData['user']->nim . ')';
                } else {
                    $csv .= 'Belum diisi';
                }
            }
            
            $csv .= "\n";
        }
        
        $csv .= "\nStatistik\n";
        $csv .= "Total Assignments," . $data['statistics']['total_assignments'] . "\n";
        $csv .= "Coverage Rate," . round($data['statistics']['coverage_rate'], 1) . "%\n";
        $csv .= "Unique Users," . $data['statistics']['unique_users'] . "\n";
        
        return $csv;
    }
}
