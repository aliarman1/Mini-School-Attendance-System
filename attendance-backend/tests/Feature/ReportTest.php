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

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $class9;
    protected $class10;
    protected $sectionA;
    protected $sectionB;
    protected $students;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create classes
        $this->class9 = ClassModel::create(['name' => '9', 'capacity' => 40]);
        $this->class10 = ClassModel::create(['name' => '10', 'capacity' => 40]);

        // Create sections
        $this->sectionA = Section::create(['name' => 'A']);
        $this->sectionB = Section::create(['name' => 'B']);

        // Create test students
        $this->students = [
            'class9_student1' => Student::factory()->create([
                'name' => 'John Doe',
                'student_id' => 'STU001',
                'class_id' => $this->class9->id,
                'section_id' => $this->sectionA->id,
            ]),
            'class9_student2' => Student::factory()->create([
                'name' => 'Jane Smith',
                'student_id' => 'STU002',
                'class_id' => $this->class9->id,
                'section_id' => $this->sectionA->id,
            ]),
            'class10_student1' => Student::factory()->create([
                'name' => 'Bob Johnson',
                'student_id' => 'STU003',
                'class_id' => $this->class10->id,
                'section_id' => $this->sectionB->id,
            ]),
        ];
    }

    /**
     * Test generating monthly attendance report
     */
    public function test_can_generate_monthly_report()
    {
        $month = Carbon::now()->format('Y-m');
        $currentMonth = Carbon::now()->startOfMonth();

        // Create attendance records for class 9 students
        for ($i = 1; $i <= 10; $i++) {
            Attendance::factory()->create([
                'student_id' => $this->students['class9_student1']->id,
                'date' => $currentMonth->copy()->addDays($i),
                'status' => $i <= 8 ? 'present' : 'absent', // 8 present, 2 absent
            ]);

            Attendance::factory()->create([
                'student_id' => $this->students['class9_student2']->id,
                'date' => $currentMonth->copy()->addDays($i),
                'status' => $i <= 9 ? 'present' : 'absent', // 9 present, 1 absent
            ]);
        }

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/attendance/report/monthly?month={$month}&class_id={$this->class9->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'student_id',
                        'student_name',
                        'student_number',
                        'total_days',
                        'present',
                        'absent',
                        'late',
                        'attendance_percentage',
                    ],
                ],
                'meta' => [
                    'month',
                    'class_id',
                ],
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data); // Should have 2 students from class 9
    }

    /**
     * Test monthly report calculates attendance percentage correctly
     */
    public function test_monthly_report_calculates_percentage_correctly()
    {
        $month = Carbon::now()->format('Y-m');
        $currentMonth = Carbon::now()->startOfMonth();

        // Create 20 attendance records: 18 present, 2 absent = 90% attendance
        for ($i = 1; $i <= 20; $i++) {
            Attendance::factory()->create([
                'student_id' => $this->students['class9_student1']->id,
                'date' => $currentMonth->copy()->addDays($i),
                'status' => $i <= 18 ? 'present' : 'absent',
            ]);
        }

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/attendance/report/monthly?month={$month}&class_id={$this->class9->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $student1Report = collect($data)->firstWhere('student_id', $this->students['class9_student1']->id);

        $this->assertEquals(20, $student1Report['total_days']);
        $this->assertEquals(18, $student1Report['present']);
        $this->assertEquals(2, $student1Report['absent']);
        $this->assertEquals(90.0, $student1Report['attendance_percentage']);
    }

    /**
     * Test monthly report includes late status
     */
    public function test_monthly_report_includes_late_status()
    {
        $month = Carbon::now()->format('Y-m');
        $currentMonth = Carbon::now()->startOfMonth();

        // Create mixed attendance records
        Attendance::factory()->create([
            'student_id' => $this->students['class9_student1']->id,
            'date' => $currentMonth->copy()->addDays(1),
            'status' => 'present',
        ]);

        Attendance::factory()->create([
            'student_id' => $this->students['class9_student1']->id,
            'date' => $currentMonth->copy()->addDays(2),
            'status' => 'late',
        ]);

        Attendance::factory()->create([
            'student_id' => $this->students['class9_student1']->id,
            'date' => $currentMonth->copy()->addDays(3),
            'status' => 'absent',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/attendance/report/monthly?month={$month}&class_id={$this->class9->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $student1Report = collect($data)->firstWhere('student_id', $this->students['class9_student1']->id);

        $this->assertEquals(3, $student1Report['total_days']);
        $this->assertEquals(1, $student1Report['present']);
        $this->assertEquals(1, $student1Report['absent']);
        $this->assertEquals(1, $student1Report['late']);
    }

    /**
     * Test monthly report filters by class correctly
     */
    public function test_monthly_report_filters_by_class()
    {
        $month = Carbon::now()->format('Y-m');
        $currentMonth = Carbon::now()->startOfMonth();

        // Create attendance for both classes
        Attendance::factory()->create([
            'student_id' => $this->students['class9_student1']->id,
            'date' => $currentMonth->copy()->addDays(1),
            'status' => 'present',
        ]);

        Attendance::factory()->create([
            'student_id' => $this->students['class10_student1']->id,
            'date' => $currentMonth->copy()->addDays(1),
            'status' => 'present',
        ]);

        // Request report for class 9 only
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/attendance/report/monthly?month={$month}&class_id={$this->class9->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // Should only return class 9 students
        foreach ($data as $student) {
            $this->assertContains($student['student_id'], [
                $this->students['class9_student1']->id,
                $this->students['class9_student2']->id,
            ]);
        }
    }

    /**
     * Test monthly report with no attendance records
     */
    public function test_monthly_report_with_no_attendance_shows_zero_percentage()
    {
        $month = Carbon::now()->format('Y-m');

        // Don't create any attendance records
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/attendance/report/monthly?month={$month}&class_id={$this->class9->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // Should still return students, but with 0 attendance
        $this->assertCount(2, $data); // 2 students in class 9
        
        foreach ($data as $studentReport) {
            $this->assertEquals(0, $studentReport['total_days']);
            $this->assertEquals(0, $studentReport['present']);
            $this->assertEquals(0, $studentReport['absent']);
            $this->assertEquals(0, $studentReport['late']);
            $this->assertEquals(0, $studentReport['attendance_percentage']);
        }
    }

    /**
     * Test monthly report validates required month parameter
     */
    public function test_monthly_report_requires_month()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/attendance/report/monthly?class_id={$this->class9->id}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['month']);
    }

    /**
     * Test monthly report validates required class_id parameter
     */
    public function test_monthly_report_requires_class_id()
    {
        $month = Carbon::now()->format('Y-m');
        
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/attendance/report/monthly?month={$month}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['class_id']);
    }

    /**
     * Test monthly report validates month format
     */
    public function test_monthly_report_validates_month_format()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/attendance/report/monthly?month=invalid-format&class_id={$this->class9->id}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['month']);
    }

    /**
     * Test monthly report validates class exists
     */
    public function test_monthly_report_validates_class_exists()
    {
        $month = Carbon::now()->format('Y-m');
        
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/attendance/report/monthly?month={$month}&class_id=99999");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['class_id']);
    }

    /**
     * Test monthly report for different months
     */
    public function test_monthly_report_filters_by_month()
    {
        $currentMonth = Carbon::now()->format('Y-m');
        $lastMonth = Carbon::now()->subMonth()->format('Y-m');

        // Create attendance for current month
        Attendance::factory()->create([
            'student_id' => $this->students['class9_student1']->id,
            'date' => Carbon::now()->startOfMonth()->addDays(5),
            'status' => 'present',
        ]);

        // Create attendance for last month
        Attendance::factory()->create([
            'student_id' => $this->students['class9_student1']->id,
            'date' => Carbon::now()->subMonth()->startOfMonth()->addDays(5),
            'status' => 'present',
        ]);

        // Request current month report
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/attendance/report/monthly?month={$currentMonth}&class_id={$this->class9->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $student1Report = collect($data)->firstWhere('student_id', $this->students['class9_student1']->id);

        // Should only count current month's attendance
        $this->assertEquals(1, $student1Report['total_days']);

        // Request last month report
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/attendance/report/monthly?month={$lastMonth}&class_id={$this->class9->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $student1Report = collect($data)->firstWhere('student_id', $this->students['class9_student1']->id);

        // Should only count last month's attendance
        $this->assertEquals(1, $student1Report['total_days']);
    }

    /**
     * Test monthly report includes all students even without attendance
     */
    public function test_monthly_report_includes_all_class_students()
    {
        $month = Carbon::now()->format('Y-m');

        // Create attendance only for student 1
        Attendance::factory()->create([
            'student_id' => $this->students['class9_student1']->id,
            'date' => Carbon::now()->startOfMonth()->addDays(1),
            'status' => 'present',
        ]);

        // Don't create attendance for student 2

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/attendance/report/monthly?month={$month}&class_id={$this->class9->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // Should include both students
        $this->assertCount(2, $data);
        
        $studentIds = collect($data)->pluck('student_id')->toArray();
        $this->assertContains($this->students['class9_student1']->id, $studentIds);
        $this->assertContains($this->students['class9_student2']->id, $studentIds);
    }

    /**
     * Test monthly report data accuracy
     */
    public function test_monthly_report_data_accuracy()
    {
        $month = Carbon::now()->format('Y-m');
        $currentMonth = Carbon::now()->startOfMonth();

        // Create specific attendance pattern: 7 present, 2 late, 1 absent = 10 days
        $statuses = ['present', 'present', 'present', 'present', 'present', 'present', 'present', 'late', 'late', 'absent'];
        
        foreach ($statuses as $index => $status) {
            Attendance::factory()->create([
                'student_id' => $this->students['class9_student1']->id,
                'date' => $currentMonth->copy()->addDays($index + 1),
                'status' => $status,
            ]);
        }

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/attendance/report/monthly?month={$month}&class_id={$this->class9->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $student1Report = collect($data)->firstWhere('student_id', $this->students['class9_student1']->id);

        // Verify exact counts
        $this->assertEquals(10, $student1Report['total_days']);
        $this->assertEquals(7, $student1Report['present']);
        $this->assertEquals(2, $student1Report['late']);
        $this->assertEquals(1, $student1Report['absent']);
        $this->assertEquals(70.0, $student1Report['attendance_percentage']); // 7/10 * 100
    }

    /**
     * Test monthly report student information is correct
     */
    public function test_monthly_report_includes_student_information()
    {
        $month = Carbon::now()->format('Y-m');

        Attendance::factory()->create([
            'student_id' => $this->students['class9_student1']->id,
            'date' => Carbon::now()->startOfMonth()->addDays(1),
            'status' => 'present',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/attendance/report/monthly?month={$month}&class_id={$this->class9->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $student1Report = collect($data)->firstWhere('student_id', $this->students['class9_student1']->id);

        // Verify student information
        $this->assertEquals($this->students['class9_student1']->id, $student1Report['student_id']);
        $this->assertEquals($this->students['class9_student1']->name, $student1Report['student_name']);
        $this->assertEquals($this->students['class9_student1']->student_id, $student1Report['student_number']);
    }

    /**
     * Test report requires authentication
     */
    public function test_report_requires_authentication()
    {
        $month = Carbon::now()->format('Y-m');
        
        $response = $this->getJson("/api/attendance/report/monthly?month={$month}&class_id={$this->class9->id}");
        
        $response->assertStatus(401);
    }
}
