<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        // Get or create classes and sections
        $class = ClassModel::inRandomOrder()->first() ?? ClassModel::create(['name' => '10', 'capacity' => 30]);
        $section = Section::inRandomOrder()->first() ?? Section::create(['name' => 'A']);

        return [
            'name' => fake()->name(),
            'student_id' => 'STU' . fake()->unique()->numberBetween(1000, 9999),
            'class_id' => $class->id,
            'section_id' => $section->id,
            'photo' => null,
        ];
    }
}
