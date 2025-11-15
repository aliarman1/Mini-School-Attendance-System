<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Student;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_student(): void
    {
        $response = $this->postJson('/api/students', [
            'name' => 'John Doe',
            'student_id' => 'STU001',
            'class' => '10A',
            'section' => 'Science',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Student created successfully',
            ]);

        $this->assertDatabaseHas('students', [
            'name' => 'John Doe',
            'student_id' => 'STU001',
        ]);
    }

    public function test_can_list_students(): void
    {
        Student::factory()->count(5)->create();

        $response = $this->getJson('/api/students');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'pagination',
            ]);
    }

    public function test_can_show_student(): void
    {
        $student = Student::create([
            'name' => 'Jane Doe',
            'student_id' => 'STU002',
            'class' => '10B',
            'section' => 'Arts',
        ]);

        $response = $this->getJson("/api/students/{$student->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_can_update_student(): void
    {
        $student = Student::create([
            'name' => 'Old Name',
            'student_id' => 'STU003',
            'class' => '9A',
            'section' => 'General',
        ]);

        $response = $this->putJson("/api/students/{$student->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Student updated successfully',
            ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'New Name',
        ]);
    }

    public function test_can_delete_student(): void
    {
        $student = Student::create([
            'name' => 'Delete Me',
            'student_id' => 'STU004',
            'class' => '10A',
            'section' => 'Science',
        ]);

        $response = $this->deleteJson("/api/students/{$student->id}");

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
        $response = $this->postJson('/api/students', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'student_id', 'class', 'section']);
    }
}
