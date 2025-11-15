<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $class;
    protected $section;
    protected $students;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create classes
        $class9 = ClassModel::create(['name' => '9', 'capacity' => 40]);
        $class10 = ClassModel::create(['name' => '10', 'capacity' => 40]);

        // Create sections
        $sectionA = Section::create(['name' => 'A']);
        $sectionB = Section::create(['name' => 'B']);

        // Store for use in tests
        $this->class = $class9;
        $this->section = $sectionA;

        // Create test students
        $this->students = collect([
            Student::factory()->create(['name' => 'John Doe', 'class_id' => $class9->id, 'section_id' => $sectionA->id]),
            Student::factory()->create(['name' => 'Jane Smith', 'class_id' => $class9->id, 'section_id' => $sectionA->id]),
            Student::factory()->create(['name' => 'Bob Johnson', 'class_id' => $class10->id, 'section_id' => $sectionB->id]),
        ]);
    }

    /**
     * Test bulk attendance recording
     */
    public function test_can_record_bulk_attendance()
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/attendance/bulk', [
            'date' => Carbon::today()->format('Y-m-d'),
            'attendances' => [
                [
                    'student_id' => $this->students[0]->id,
                    'status' => 'present',
                    'note' => 'On time',
                ],
                [
                    'student_id' => $this->students[1]->id,
                    'status' => 'absent',
                    'note' => 'Sick',
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Attendance recorded successfully',
            ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->students[0]->id,
            'status' => 'present',
        ]);

        $attendance = Attendance::where('student_id', $this->students[0]->id)->first();
        $this->assertEquals(Carbon::today()->toDateString(), $attendance->date->toDateString());

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->students[1]->id,
            'status' => 'absent',
        ]);
    }

    /**
     * Test bulk attendance validation - missing date
     */
    public function test_bulk_attendance_requires_date()
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/attendance/bulk', [
            'attendances' => [
                [
                    'student_id' => $this->students[0]->id,
                    'status' => 'present',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    /**
     * Test bulk attendance validation - missing attendances array
     */
    public function test_bulk_attendance_requires_attendances_array()
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/attendance/bulk', [
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['attendances']);
    }

    /**
     * Test bulk attendance validation - invalid status
     */
    public function test_bulk_attendance_validates_status()
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/attendance/bulk', [
            'date' => Carbon::today()->format('Y-m-d'),
            'attendances' => [
                [
                    'student_id' => $this->students[0]->id,
                    'status' => 'invalid_status',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['attendances.0.status']);
    }

    /**
     * Test bulk attendance validation - non-existent student
     */
    public function test_bulk_attendance_validates_student_exists()
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/attendance/bulk', [
            'date' => Carbon::today()->format('Y-m-d'),
            'attendances' => [
                [
                    'student_id' => 99999,
                    'status' => 'present',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['attendances.0.student_id']);
    }

    /**
     * Test updating existing attendance (upsert functionality)
     */
    public function test_can_update_existing_attendance()
    {
        $date = Carbon::today();

        // First record
        $this->actingAs($this->user, 'sanctum')->postJson('/api/attendance/bulk', [
            'date' => $date->format('Y-m-d'),
            'attendances' => [
                [
                    'student_id' => $this->students[0]->id,
                    'status' => 'present',
                ],
            ],
        ]);

        // Update the same record
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/attendance/bulk', [
            'date' => $date->format('Y-m-d'),
            'attendances' => [
                [
                    'student_id' => $this->students[0]->id,
                    'status' => 'absent',
                    'note' => 'Changed to absent',
                ],
            ],
        ]);

        $response->assertStatus(201);

        // Should only have one record, not two
        $this->assertEquals(1, Attendance::where('student_id', $this->students[0]->id)
            ->whereDate('date', $date->format('Y-m-d'))
            ->count());

        // Should be updated to absent
        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->students[0]->id,
            'status' => 'absent',
            'note' => 'Changed to absent',
        ]);
    }

    /**
     * Test fetching attendance list
     */
    public function test_can_list_attendance_records()
    {
        // Create attendance records
        Attendance::factory()->create([
            'student_id' => $this->students[0]->id,
            'date' => Carbon::today(),
            'status' => 'present',
        ]);

        Attendance::factory()->create([
            'student_id' => $this->students[1]->id,
            'date' => Carbon::today(),
            'status' => 'absent',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/attendance');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'student_id', 'date', 'status'],
                ],
                'pagination',
            ]);
    }

    /**
     * Test filtering attendance by date
     */
    public function test_can_filter_attendance_by_date()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        Attendance::factory()->create([
            'student_id' => $this->students[0]->id,
            'date' => $today,
            'status' => 'present',
        ]);

        Attendance::factory()->create([
            'student_id' => $this->students[1]->id,
            'date' => $yesterday,
            'status' => 'absent',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/attendance?start_date=' . $today->format('Y-m-d'));

        $response->assertStatus(200);
        
        // Should only return today's record
        $this->assertCount(1, $response->json('data'));
    }

    /**
     * Test filtering attendance by status
     */
    public function test_can_filter_attendance_by_status()
    {
        Attendance::factory()->create([
            'student_id' => $this->students[0]->id,
            'date' => Carbon::today(),
            'status' => 'present',
        ]);

        Attendance::factory()->create([
            'student_id' => $this->students[1]->id,
            'date' => Carbon::today(),
            'status' => 'absent',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/attendance?status=present');

        $response->assertStatus(200);
        
        // Should only return present records
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    /**
     * Test filtering attendance by student
     */
    public function test_can_filter_attendance_by_student()
    {
        Attendance::factory()->create([
            'student_id' => $this->students[0]->id,
            'date' => Carbon::today(),
            'status' => 'present',
        ]);

        Attendance::factory()->create([
            'student_id' => $this->students[1]->id,
            'date' => Carbon::today(),
            'status' => 'absent',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/attendance?student_id=' . $this->students[0]->id);

        $response->assertStatus(200);
        
        // Should only return records for student 0
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    /**
     * Test filtering attendance by class
     */
    public function test_can_filter_attendance_by_class()
    {
        Attendance::factory()->create([
            'student_id' => $this->students[0]->id, // class 9
            'date' => Carbon::today(),
            'status' => 'present',
        ]);

        Attendance::factory()->create([
            'student_id' => $this->students[2]->id, // class 10
            'date' => Carbon::today(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/attendance?class_id=' . $this->class->id);

        $response->assertStatus(200);
        
        // Should only return records for class 9
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    /**
     * Test showing single attendance record
     */
    public function test_can_show_attendance_record()
    {
        $attendance = Attendance::factory()->create([
            'student_id' => $this->students[0]->id,
            'date' => Carbon::today(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/attendance/' . $attendance->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $attendance->id,
                    'student_id' => $this->students[0]->id,
                    'status' => 'present',
                ],
            ]);
    }

    /**
     * Test updating attendance record
     */
    public function test_can_update_attendance_record()
    {
        $attendance = Attendance::factory()->create([
            'student_id' => $this->students[0]->id,
            'date' => Carbon::today(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/attendance/' . $attendance->id, [
                'status' => 'late',
                'note' => 'Arrived 10 minutes late',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Attendance updated successfully',
            ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'late',
            'note' => 'Arrived 10 minutes late',
        ]);
    }

    /**
     * Test deleting attendance record
     */
    public function test_can_delete_attendance_record()
    {
        $attendance = Attendance::factory()->create([
            'student_id' => $this->students[0]->id,
            'date' => Carbon::today(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/attendance/' . $attendance->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Attendance deleted successfully',
            ]);

        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
        ]);
    }

    /**
     * Test getting today's attendance
     */
    public function test_can_get_today_attendance()
    {
        $today = Carbon::today();

        Attendance::factory()->create([
            'student_id' => $this->students[0]->id,
            'date' => $today,
            'status' => 'present',
        ]);

        Attendance::factory()->create([
            'student_id' => $this->students[1]->id,
            'date' => $today,
            'status' => 'absent',
        ]);

        // Create one for yesterday (should not be included)
        Attendance::factory()->create([
            'student_id' => $this->students[2]->id,
            'date' => Carbon::yesterday(),
            'status' => 'present',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/attendance/today');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'date' => $today->format('Y-m-d'),
                    'total' => 2,
                    'present' => 1,
                    'absent' => 1,
                    'late' => 0,
                ],
            ]);
    }

    /**
     * Test getting attendance statistics
     */
    public function test_can_get_attendance_statistics()
    {
        // Create today's attendance
        Attendance::factory()->create([
            'student_id' => $this->students[0]->id,
            'date' => Carbon::today(),
            'status' => 'present',
        ]);

        Attendance::factory()->create([
            'student_id' => $this->students[1]->id,
            'date' => Carbon::today(),
            'status' => 'absent',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/attendance/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_students',
                    'today' => [
                        'total_recorded',
                        'present',
                        'absent',
                        'late',
                        'percentage',
                    ],
                    'monthly' => [
                        'total_records',
                        'present',
                        'percentage',
                    ],
                ],
            ]);
    }

    /**
     * Test attendance requires authentication
     */
    public function test_attendance_requires_authentication()
    {
        $response = $this->getJson('/api/attendance');
        $response->assertStatus(401);

        $response = $this->postJson('/api/attendance/bulk', []);
        $response->assertStatus(401);
    }
}
