<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'student_id' => 'STU' . fake()->unique()->numberBetween(1000, 9999),
            'class' => fake()->randomElement(['9A', '9B', '10A', '10B']),
            'section' => fake()->randomElement(['Science', 'Arts', 'General']),
            'photo' => null,
        ];
    }
}
