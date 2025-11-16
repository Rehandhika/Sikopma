<?php

require_once 'vendor/autoload.php';

use App\Livewire\Schedule\AvailabilityManager;

try {
    $component = new AvailabilityManager();
    echo "Component created successfully\n";
    echo "Properties:\n";
    echo "- selectedWeekOffset: " . $component->selectedWeekOffset . "\n";
    echo "- weekStart: " . ($component->weekStart ? $component->weekStart->format('Y-m-d') : 'null') . "\n";
    echo "- weekEnd: " . ($component->weekEnd ? $component->weekEnd->format('Y-m-d') : 'null') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
