<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Schedule\ScheduleChangeManager;
use App\Models\AssignmentEditHistory;
use App\Models\Availability;
use App\Models\AvailabilityDetail;
use App\Models\Schedule;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleChangeRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ScheduleChangeManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;
    private Schedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        // Lock time to a specific Monday to prevent database ENUM constraint failures (only allows Mon-Thu)
        Carbon::setTestNow(Carbon::parse('2026-04-06 08:00:00')); // Monday

        Role::firstOrCreate(['name' => 'Super Admin']);
        Role::firstOrCreate(['name' => 'Kasir']);
        
        $this->user = User::factory()->create()->assignRole('Kasir');
        $this->admin = User::factory()->create()->assignRole('Super Admin');

        $this->schedule = Schedule::create([
            'week_start_date' => now()->startOfWeek()->toDateString(),
            'week_end_date' => now()->startOfWeek()->addDays(6)->toDateString(),
            'status' => 'published',
            'total_slots' => 12,
        ]);
    }

    private function setAvailability(string $dateString, int $session)
    {
        $date = Carbon::parse($dateString);
        $weekStart = $date->copy()->startOfWeek()->toDateString();
        
        $availability = Availability::create([
            'user_id' => $this->user->id,
            'week_start_date' => $weekStart,
            'week_end_date' => $date->copy()->endOfWeek()->toDateString(),
            'status' => 'submitted',
            'notes' => 'Test'
        ]);

        AvailabilityDetail::create([
            'availability_id' => $availability->id,
            'day' => strtolower($date->englishDayOfWeek),
            'session' => $session,
            'is_available' => true
        ]);
    }

    /** @test */
    public function it_fails_reschedule_if_less_than_3_hours()
    {
        $date = now();
        // Set time to exactly 2 hours from now
        $assignment = ScheduleAssignment::create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'date' => clone $date,
            'session' => 1,
            'day' => strtolower($date->englishDayOfWeek),
            'time_start' => now()->addHours(2)->format('H:i'),
            'time_end' => now()->addHours(4)->format('H:i'),
        ]);

        Livewire::actingAs($this->user)
            ->test(ScheduleChangeManager::class)
            ->call('openForm')
            ->set('selectedAssignment', $assignment->id)
            ->set('changeType', 'reschedule')
            ->set('requestedDate', now()->addDays(2)->format('Y-m-d'))
            ->set('requestedSession', 1)
            ->set('reason', 'Alasan pengajuan yang panjang')
            ->call('submitForm')
            ->assertHasErrors(['selectedAssignment' => 'Pengajuan pindah jadwal minimal 3 jam sebelum sesi dimulai']);
    }

    /** @test */
    public function it_fails_cancel_if_less_than_24_hours()
    {
        $date = now();
        $assignment = ScheduleAssignment::create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'date' => clone $date,
            'session' => 1,
            'day' => strtolower($date->englishDayOfWeek),
            'time_start' => now()->addHours(20)->format('H:i'), // 20 hours < 24 hours
            'time_end' => now()->addHours(22)->format('H:i'),
        ]);

        Livewire::actingAs($this->user)
            ->test(ScheduleChangeManager::class)
            ->call('openForm')
            ->set('selectedAssignment', $assignment->id)
            ->set('changeType', 'cancel')
            ->set('reason', 'Alasan pengajuan yang panjang sekali')
            ->call('submitForm')
            ->assertHasErrors(['selectedAssignment' => 'Pengajuan batal jadwal minimal 24 jam sebelum jadwal']);
    }

    /** @test */
    public function it_can_submit_valid_reschedule_request()
    {
        $date = now()->addDays(2);
        $assignment = ScheduleAssignment::create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'date' => clone $date,
            'session' => 1,
            'day' => strtolower($date->englishDayOfWeek),
            'time_start' => '10:00',
            'time_end' => '12:00',
        ]);

        $targetDate = now()->addDays(3);

        Livewire::actingAs($this->user)
            ->test(ScheduleChangeManager::class)
            ->call('openForm')
            ->set('selectedAssignment', $assignment->id)
            ->set('changeType', 'reschedule')
            ->set('requestedDate', $targetDate->format('Y-m-d'))
            ->set('requestedSession', 2)
            ->set('reason', 'Alasan pengajuan yang panjang sekali')
            ->call('submitForm')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('schedule_change_requests', [
            'user_id' => $this->user->id,
            'change_type' => 'reschedule',
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_can_submit_valid_cancel_request()
    {
        $date = now()->addDays(2);
        $assignment = ScheduleAssignment::create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'date' => clone $date,
            'session' => 1,
            'day' => strtolower($date->englishDayOfWeek),
            'time_start' => '10:00',
            'time_end' => '12:00',
        ]);

        Livewire::actingAs($this->user)
            ->test(ScheduleChangeManager::class)
            ->call('openForm')
            ->set('selectedAssignment', $assignment->id)
            ->set('changeType', 'cancel')
            ->set('reason', 'Saya tidak bisa hadir karena ada acara keluarga yang sangat penting')
            ->call('submitForm')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('schedule_change_requests', [
            'user_id' => $this->user->id,
            'change_type' => 'cancel',
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function admin_can_approve_reschedule_and_updates_assignment_with_history()
    {
        $date = now()->addDays(2);
        $assignment = ScheduleAssignment::create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'date' => clone $date,
            'session' => 1,
            'day' => strtolower($date->englishDayOfWeek),
            'time_start' => '07:30',
            'time_end' => '10:00',
        ]);

        $request = ScheduleChangeRequest::create([
            'user_id' => $this->user->id,
            'original_assignment_id' => $assignment->id,
            'change_type' => 'reschedule',
            'requested_date' => now()->addDays(3)->format('Y-m-d'),
            'requested_session' => 2,
            'reason' => 'Tes approve reschedule',
            'status' => 'pending'
        ]);

        Livewire::actingAs($this->admin)
            ->test(ScheduleChangeManager::class)
            ->call('openReview', $request->id, 'approved')
            ->set('reviewNotes', 'Oke silakan')
            ->call('submitReview')
            ->assertHasNoErrors();

        // Assignment should be updated
        $assignment->refresh();
        $this->assertEquals(2, $assignment->session);
        $this->assertEquals(now()->addDays(3)->format('Y-m-d'), $assignment->date->format('Y-m-d'));
        $this->assertEquals('10:20:00', $assignment->time_start);

        // Request status updated
        $this->assertDatabaseHas('schedule_change_requests', [
            'id' => $request->id,
            'status' => 'approved',
            'admin_response' => 'Oke silakan'
        ]);

        // History recorded
        $this->assertDatabaseHas('assignment_edit_history', [
            'assignment_id' => $assignment->id,
            'action' => 'updated'
        ]);
    }

    /** @test */
    public function admin_can_approve_cancel_and_deletes_assignment_with_history()
    {
        $date = now()->addDays(2);
        $assignment = ScheduleAssignment::create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'date' => clone $date,
            'session' => 1,
            'day' => strtolower($date->englishDayOfWeek),
            'time_start' => '07:30',
            'time_end' => '10:00',
        ]);

        $request = ScheduleChangeRequest::create([
            'user_id' => $this->user->id,
            'original_assignment_id' => $assignment->id,
            'change_type' => 'cancel',
            'reason' => 'Tes approve cancel',
            'status' => 'pending'
        ]);

        Livewire::actingAs($this->admin)
            ->test(ScheduleChangeManager::class)
            ->call('openReview', $request->id, 'approved')
            ->call('submitReview')
            ->assertHasNoErrors();

        // Assignment should be deleted
        $this->assertSoftDeleted('schedule_assignments', [
            'id' => $assignment->id,
        ]);

        // Request status updated
        $this->assertDatabaseHas('schedule_change_requests', [
            'id' => $request->id,
            'status' => 'approved'
        ]);

        // History recorded for deletion
        $this->assertDatabaseHas('assignment_edit_history', [
            'assignment_id' => $assignment->id,
            'action' => 'deleted'
        ]);
    }
}
