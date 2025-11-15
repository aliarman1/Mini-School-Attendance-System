<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\User;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user for authentication
        $this->user = User::factory()->create();
        
        // Create classes and sections for testing
        ClassModel::create(['name' => '9', 'capacity' => 30]);
        ClassModel::create(['name' => '10', 'capacity' => 30]);
        ClassModel::create(['name' => '11', 'capacity' => 30]);
        ClassModel::create(['name' => '12', 'capacity' => 30]);
        
        Section::create(['name' => 'A']);
        Section::create(['name' => 'B']);
        Section::create(['name' => 'C']);
    }

    public function test_can_create_student(): void
    {
        $class = ClassModel::where('name', '10')->first();
        $section = Section::where('name', 'A')->first();

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/students', [
            'name' => 'John Doe',
            'student_id' => 'STU001',
            'class_id' => $class->id,
            'section_id' => $section->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Student created successfully',
            ]);

        $this->assertDatabaseHas('students', [
            'name' => 'John Doe',
            'student_id' => 'STU001',
            'class_id' => $class->id,
            'section_id' => $section->id,
        ]);
    }

    public function test_can_list_students(): void
    {
        Student::factory()->count(5)->create();

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/students');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'pagination',
            ]);
    }

    public function test_can_show_student(): void
    {
        $class = ClassModel::where('name', '10')->first();
        $section = Section::where('name', 'B')->first();
        
        $student = Student::create([
            'name' => 'Jane Doe',
            'student_id' => 'STU002',
            'class_id' => $class->id,
            'section_id' => $section->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson("/api/students/{$student->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_can_update_student(): void
    {
        $class = ClassModel::where('name', '9')->first();
        $section = Section::where('name', 'A')->first();
        
        $student = Student::create([
            'name' => 'Old Name',
            'student_id' => 'STU003',
            'class_id' => $class->id,
            'section_id' => $section->id,
        ]);

        $newClass = ClassModel::where('name', '10')->first();

        $response = $this->actingAs($this->user, 'sanctum')->putJson("/api/students/{$student->id}", [
            'name' => 'New Name',
            'student_id' => 'STU003',
            'class_id' => $newClass->id,
            'section_id' => $section->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Student updated successfully',
            ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'New Name',
            'class_id' => $newClass->id,
        ]);
    }

    public function test_can_delete_student(): void
    {
        $class = ClassModel::where('name', '10')->first();
        $section = Section::where('name', 'A')->first();
        
        $student = Student::create([
            'name' => 'Delete Me',
            'student_id' => 'STU004',
            'class_id' => $class->id,
            'section_id' => $section->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/students/{$student->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Student deleted successfully',
            ]);

        $this->assertDatabaseMissing('students', [
            'id' => $student->id,
        ]);
    }

    public function test_student_validation_fails_without_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/students', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'student_id', 'class_id', 'section_id']);
    }

    public function test_can_filter_students_by_class(): void
    {
        $class10 = ClassModel::where('name', '10')->first();
        $class11 = ClassModel::where('name', '11')->first();
        $sectionA = Section::where('name', 'A')->first();

        // Create students in different classes
        Student::factory()->count(3)->create(['class_id' => $class10->id, 'section_id' => $sectionA->id]);
        Student::factory()->count(2)->create(['class_id' => $class11->id, 'section_id' => $sectionA->id]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson("/api/students?class_id={$class10->id}");

        $response->assertStatus(200);
        $this->assertEquals(3, count($response->json('data')));
    }

    public function test_can_filter_students_by_section(): void
    {
        $class10 = ClassModel::where('name', '10')->first();
        $sectionA = Section::where('name', 'A')->first();
        $sectionB = Section::where('name', 'B')->first();

        // Create students in different sections
        Student::factory()->count(3)->create(['class_id' => $class10->id, 'section_id' => $sectionA->id]);
        Student::factory()->count(2)->create(['class_id' => $class10->id, 'section_id' => $sectionB->id]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson("/api/students?section_id={$sectionA->id}");

        $response->assertStatus(200);
        $this->assertEquals(3, count($response->json('data')));
    }

    public function test_can_filter_students_by_class_and_section(): void
    {
        $class10 = ClassModel::where('name', '10')->first();
        $sectionA = Section::where('name', 'A')->first();
        $sectionB = Section::where('name', 'B')->first();

        // Create students in different combinations
        Student::factory()->count(3)->create(['class_id' => $class10->id, 'section_id' => $sectionA->id]);
        Student::factory()->count(2)->create(['class_id' => $class10->id, 'section_id' => $sectionB->id]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson("/api/students?class_id={$class10->id}&section_id={$sectionA->id}");

        $response->assertStatus(200);
        $this->assertEquals(3, count($response->json('data')));
    }
}
