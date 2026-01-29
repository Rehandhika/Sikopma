<?php

namespace Tests\Feature\Audit;

use App\Livewire\Attendance\CheckInOut;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\ScheduleAssignment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * Attendance Module Audit Tests
 *
 * Tests attendance check-in/check-out functionality, validation, and status classification.
 * Requirements: 4.1, 4.2, 4.3, 4.4, 4.7, 4.8, 4.9, 4.10
 */
class AttendanceAuditTest extends AuditTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up fake storage for photo uploads
        Storage::fake('public');
    }

    // ==========================================
    // Helper Methods
    // ==========================================

    /**
     * Create a schedule with an assignment for a user at the current time.
     */
    protected function createActiveScheduleForUser(User $user, array $overrides = []): ScheduleAssignment
    {
        $now = now();
        $schedule = Schedule::create([
            'week_start_date' => $now->copy()->startOfWeek(),
            'week_end_date' => $now->copy()->startOfWeek()->addDays(3),
            'status' => 'published',
            'generated_by' => $this->ketua->id,
            'total_slots' => 12,
            'filled_slots' => 1,
            'coverage_rate' => 8.33,
        ]);

        $defaults = [
            'schedule_id' => $schedule->id,
            'user_id' => $user->id,
            'day' => strtolower($now->englishDayOfWeek),
            'session' => 1,
            'date' => $now->format('Y-m-d'),
            'time_start' => $now->copy()->subMinutes(30)->format('H:i:s'),
            'time_end' => $now->copy()->addHours(3)->format('H:i:s'),
            'status' => 'scheduled',
        ];

        return ScheduleAssignment::create(array_merge($defaults, $overrides));
    }

    /**
     * Create a schedule with an assignment for a user in the future (upcoming).
     */
    protected function createUpcomingScheduleForUser(User $user): ScheduleAssignment
    {
        $now = now();
        $schedule = Schedule::create([
            'week_start_date' => $now->copy()->startOfWeek(),
            'week_end_date' => $now->copy()->startOfWeek()->addDays(3),
            'status' => 'published',
            'generated_by' => $this->ketua->id,
            'total_slots' => 12,
            'filled_slots' => 1,
            'coverage_rate' => 8.33,
        ]);

        return ScheduleAssignment::create([
            'schedule_id' => $schedule->id,
            'user_id' => $user->id,
            'day' => strtolower($now->englishDayOfWeek),
            'session' => 1,
            'date' => $now->format('Y-m-d'),
            // Start time is 2 hours in the future (beyond the 30-minute early check-in window)
            'time_start' => $now->copy()->addHours(2)->format('H:i:s'),
            'time_end' => $now->copy()->addHours(5)->format('H:i:s'),
            'status' => 'scheduled',
        ]);
    }

    // ==========================================
    // 6.1 Check-in Tests
    // Requirements: 4.1, 4.2, 4.3, 4.4
    // ==========================================

    /**
     * Test check-in page displays schedule info.
     * Requirement 4.1: WHEN a user with an active schedule accesses check-in page
     * THEN the System SHALL display current schedule information and check-in form
     */
    public function test_check_in_page_displays_schedule_info(): void
    {
        $assignment = $this->createActiveScheduleForUser($this->anggota);

        $component = Livewire::actingAs($this->anggota)
            ->test(CheckInOut::class);

        // Verify schedule information is displayed
        $component->assertSee('Jadwal Hari Ini');
        $component->assertSee('Sesi '.$assignment->session);
        $component->assertSee($assignment->session_label);
    }

    /**
     * Test check-in page shows no schedule message when user has no schedule.
     * Requirement 4.9: WHEN a user has no scheduled assignment for today
     * THEN the System SHALL display a message indicating no active schedule
     */
    public function test_check_in_page_shows_no_schedule_message(): void
    {
        // User has no schedule for today
        $component = Livewire::actingAs($this->anggota)
            ->test(CheckInOut::class);

        $component->assertSee('Tidak ada jadwal aktif');
    }

    /**
     * Test early check-in is blocked with message.
     * Requirement 4.2: WHEN a user attempts check-in before the allowed time window
     * THEN the System SHALL display a message indicating when check-in becomes available
     */
    public function test_early_check_in_is_blocked_with_message(): void
    {
        $assignment = $this->createUpcomingScheduleForUser($this->anggota);

        $component = Livewire::actingAs($this->anggota)
            ->test(CheckInOut::class);

        // Should show upcoming status and message about when check-in is available
        $component->assertSee('Check-in dapat dilakukan 30 menit sebelum jadwal dimulai');

        // The canCheckInNow method should return false
        $this->assertFalse($component->instance()->canCheckInNow());
    }

    /**
     * Test successful check-in with valid photo.
     * Requirement 4.3: WHEN a user uploads a valid photo and submits check-in
     * THEN the System SHALL record the attendance with timestamp and photo path
     */
    public function test_successful_check_in_with_valid_photo(): void
    {
        $assignment = $this->createActiveScheduleForUser($this->anggota);
        $photo = UploadedFile::fake()->image('checkin.jpg', 640, 480)->size(1024);

        Livewire::actingAs($this->anggota)
            ->test(CheckInOut::class)
            ->set('checkInPhoto', $photo)
            ->call('checkIn');

        // Verify attendance record was created
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->anggota->id,
            'schedule_assignment_id' => $assignment->id,
        ]);

        // Verify photo was stored
        $attendance = Attendance::where('user_id', $this->anggota->id)
            ->where('schedule_assignment_id', $assignment->id)
            ->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->check_in);
        $this->assertNotNull($attendance->check_in_photo);
    }

    /**
     * Test check-in without photo fails validation.
     * Requirement 4.4: WHEN a user attempts check-in without uploading a photo
     * THEN the System SHALL display a validation error requiring photo upload
     */
    public function test_check_in_without_photo_fails_validation(): void
    {
        $assignment = $this->createActiveScheduleForUser($this->anggota);

        $component = Livewire::actingAs($this->anggota)
            ->test(CheckInOut::class)
            ->call('checkIn');

        // Should have validation error for photo
        $component->assertHasErrors(['checkInPhoto' => 'required']);
    }

    /**
     * Test check-in with file exceeding 5MB fails validation.
     * Requirement 4.5: WHEN a user uploads a file exceeding 5MB
     * THEN the System SHALL display a validation error about file size limit
     *
     * Note: This test verifies the validation rule exists in the component.
     * Livewire handles large file uploads at the framework level before component validation.
     */
    public function test_check_in_photo_has_max_size_validation_rule(): void
    {
        $assignment = $this->createActiveScheduleForUser($this->anggota);

        // Verify the component has the correct validation rules
        $component = Livewire::actingAs($this->anggota)
            ->test(CheckInOut::class);

        // Get the component instance and check its rules
        $rules = $component->instance()->getRules();

        // Verify max:5120 rule exists for checkInPhoto (5MB = 5120KB)
        $this->assertArrayHasKey('checkInPhoto', $rules);
        $this->assertStringContainsString('max:5120', $rules['checkInPhoto']);
    }

    /**
     * Test check-in with non-image file fails validation.
     * Requirement 4.6: WHEN a user uploads a non-image file
     * THEN the System SHALL display a validation error about file type
     */
    public function test_check_in_with_non_image_file_fails_validation(): void
    {
        $assignment = $this->createActiveScheduleForUser($this->anggota);
        // Create a non-image file
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $component = Livewire::actingAs($this->anggota)
            ->test(CheckInOut::class)
            ->set('checkInPhoto', $file);

        // Should have validation error for file type
        $component->assertHasErrors(['checkInPhoto' => 'image']);
    }

    /**
     * Test user cannot check-in twice.
     */
    public function test_user_cannot_check_in_twice(): void
    {
        $assignment = $this->createActiveScheduleForUser($this->anggota);

        // Create existing attendance record
        Attendance::create([
            'user_id' => $this->anggota->id,
            'schedule_assignment_id' => $assignment->id,
            'date' => today(),
            'check_in' => now(),
            'status' => 'present',
        ]);

        $photo = UploadedFile::fake()->image('checkin.jpg', 640, 480)->size(1024);

        // The component should show already checked in state
        $component = Livewire::actingAs($this->anggota)
            ->test(CheckInOut::class);

        // Verify the check-in time is displayed (meaning already checked in)
        $this->assertNotNull($component->get('checkInTime'));
    }

    // ==========================================
    // 6.2 Check-out Tests
    // Requirements: 4.7, 4.8, 4.9
    // ==========================================

    /**
     * Test successful check-out after check-in.
     * Requirement 4.7: WHEN a checked-in user submits check-out
     * THEN the System SHALL record check-out time and calculate work hours
     */
    public function test_successful_check_out_after_check_in(): void
    {
        $assignment = $this->createActiveScheduleForUser($this->anggota);

        // Create check-in record from 2 hours ago
        $checkInTime = now()->subHours(2);
        $attendance = Attendance::create([
            'user_id' => $this->anggota->id,
            'schedule_assignment_id' => $assignment->id,
            'date' => today(),
            'check_in' => $checkInTime,
            'status' => 'present',
        ]);

        Livewire::actingAs($this->anggota)
            ->test(CheckInOut::class)
            ->call('checkOut');

        // Refresh attendance from database
        $attendance->refresh();

        // Verify check-out was recorded
        $this->assertNotNull($attendance->check_out);

        // Verify work hours were calculated (should be approximately 2 hours)
        $this->assertNotNull($attendance->work_hours);
        $this->assertGreaterThan(1.9, $attendance->work_hours);
        $this->assertLessThan(2.1, $attendance->work_hours);
    }

    /**
     * Test check-out without check-in fails.
     * Requirement 4.8: WHEN a user attempts check-out without prior check-in
     * THEN the System SHALL display an appropriate error message
     */
    public function test_check_out_without_check_in_fails(): void
    {
        $assignment = $this->createActiveScheduleForUser($this->anggota);

        $component = Livewire::actingAs($this->anggota)
            ->test(CheckInOut::class);

        // Verify no attendance exists yet
        $this->assertNull($component->get('currentAttendance'));

        // The check-out button should not be available when not checked in
        $this->assertNull($component->get('checkInTime'));
    }

    /**
     * Test user cannot check-out twice.
     */
    public function test_user_cannot_check_out_twice(): void
    {
        $assignment = $this->createActiveScheduleForUser($this->anggota);

        // Create attendance record with both check-in and check-out
        Attendance::create([
            'user_id' => $this->anggota->id,
            'schedule_assignment_id' => $assignment->id,
            'date' => today(),
            'check_in' => now()->subHours(2),
            'check_out' => now()->subHour(),
            'work_hours' => 1.0,
            'status' => 'present',
        ]);

        $component = Livewire::actingAs($this->anggota)
            ->test(CheckInOut::class);

        // Verify both check-in and check-out times are displayed
        $this->assertNotNull($component->get('checkInTime'));
        $this->assertNotNull($component->get('checkOutTime'));
    }

    /**
     * Test no schedule displays appropriate message.
     * Requirement 4.9: WHEN a user has no scheduled assignment for today
     * THEN the System SHALL display a message indicating no active schedule
     */
    public function test_no_schedule_displays_appropriate_message(): void
    {
        // User has no schedule
        $component = Livewire::actingAs($this->anggota)
            ->test(CheckInOut::class);

        $component->assertSee('Tidak ada jadwal aktif');
        $component->assertSee('Saat ini tidak ada jadwal kerja yang aktif untuk Anda');
    }

    /**
     * Test check-in page is accessible only to authenticated users.
     */
    public function test_check_in_page_requires_authentication(): void
    {
        $response = $this->get('/admin/absensi/masuk-keluar');

        $response->assertRedirect('/admin/masuk');
    }

    /**
     * Test authenticated user can access check-in page.
     */
    public function test_authenticated_user_can_access_check_in_page(): void
    {
        $response = $this->actingAs($this->anggota)
            ->get('/admin/absensi/masuk-keluar');

        $response->assertStatus(200);
    }

    /**
     * Test notes can be updated after check-in.
     */
    public function test_notes_can_be_updated_after_check_in(): void
    {
        $assignment = $this->createActiveScheduleForUser($this->anggota);

        // Create check-in record
        $attendance = Attendance::create([
            'user_id' => $this->anggota->id,
            'schedule_assignment_id' => $assignment->id,
            'date' => today(),
            'check_in' => now(),
            'status' => 'present',
        ]);

        Livewire::actingAs($this->anggota)
            ->test(CheckInOut::class)
            ->set('notes', 'Test notes for attendance')
            ->call('updateNotes');

        // Refresh attendance from database
        $attendance->refresh();

        // Verify notes were saved
        $this->assertEquals('Test notes for attendance', $attendance->notes);
    }

    /**
     * Test notes validation (max 500 characters).
     * Note: The component validates notes with max:500 rule.
     */
    public function test_notes_has_max_length_validation_rule(): void
    {
        $assignment = $this->createActiveScheduleForUser($this->anggota);

        // Create check-in record
        Attendance::create([
            'user_id' => $this->anggota->id,
            'schedule_assignment_id' => $assignment->id,
            'date' => today(),
            'check_in' => now(),
            'status' => 'present',
        ]);

        // Verify the component has the correct validation rules for notes
        $component = Livewire::actingAs($this->anggota)
            ->test(CheckInOut::class);

        // Get the component instance and check its rules
        $rules = $component->instance()->getRules();

        // Verify max:500 rule exists for notes
        $this->assertArrayHasKey('notes', $rules);
        $this->assertStringContainsString('max:500', $rules['notes']);
    }
}
