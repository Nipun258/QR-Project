<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Student;

class AttendanceScanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Student::create([
            'register_number' => 'REG1001',
            'name' => 'Alex Johnson',
            'department' => 'Computer Science',
            'is_active' => true,
        ]);

        Student::create([
            'register_number' => 'REG9001',
            'name' => 'Michael Scott',
            'department' => 'Business',
            'is_active' => false,
        ]);
    }

    public function test_active_student_scan_records_attendance_and_returns_eligible(): void
    {
        $response = $this->postJson('/api/scan', [
            'register_number' => 'REG1001',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'eligible' => true,
                     'status' => 'ACTIVE',
                     'title' => 'User Active',
                 ]);

        $this->assertDatabaseHas('attendances', [
            'register_number' => 'REG1001',
            'status' => 'PRESENT',
        ]);
    }

    public function test_inactive_student_scan_returns_ineligible(): void
    {
        $response = $this->postJson('/api/scan', [
            'register_number' => 'REG9001',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => false,
                     'eligible' => false,
                     'status' => 'INACTIVE',
                     'title' => 'User Not Active',
                 ]);

        $this->assertDatabaseHas('attendances', [
            'register_number' => 'REG9001',
            'status' => 'REJECTED',
        ]);
    }

    public function test_unregistered_student_scan_returns_404(): void
    {
        $response = $this->postJson('/api/scan', [
            'register_number' => 'NOTFOUND99',
        ]);

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'eligible' => false,
                     'status' => 'NOT_FOUND',
                     'title' => 'User Not Active',
                 ]);
    }
}
