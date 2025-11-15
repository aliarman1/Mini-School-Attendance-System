<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test students with diverse classes and sections
        $students = [
            // Class 10A - Science
            ['name' => 'John Doe', 'student_id' => 'STU001', 'class' => '10A', 'section' => 'Science'],
            ['name' => 'Jane Smith', 'student_id' => 'STU002', 'class' => '10A', 'section' => 'Science'],
            ['name' => 'Mike Johnson', 'student_id' => 'STU003', 'class' => '10A', 'section' => 'Science'],
            ['name' => 'Maria Taylor', 'student_id' => 'STU010', 'class' => '10A', 'section' => 'Science'],
            
            // Class 10B - Arts
            ['name' => 'Sarah Williams', 'student_id' => 'STU004', 'class' => '10B', 'section' => 'Arts'],
            ['name' => 'David Brown', 'student_id' => 'STU005', 'class' => '10B', 'section' => 'Arts'],
            ['name' => 'Jennifer Garcia', 'student_id' => 'STU011', 'class' => '10B', 'section' => 'Arts'],
            
            // Class 9A - General
            ['name' => 'Emily Davis', 'student_id' => 'STU006', 'class' => '9A', 'section' => 'General'],
            ['name' => 'Robert Miller', 'student_id' => 'STU007', 'class' => '9A', 'section' => 'General'],
            ['name' => 'Michael Anderson', 'student_id' => 'STU012', 'class' => '9A', 'section' => 'General'],
            
            // Class 9B - General
            ['name' => 'Lisa Wilson', 'student_id' => 'STU008', 'class' => '9B', 'section' => 'General'],
            ['name' => 'James Moore', 'student_id' => 'STU009', 'class' => '9B', 'section' => 'General'],
            ['name' => 'Patricia Martinez', 'student_id' => 'STU013', 'class' => '9B', 'section' => 'General'],
            ['name' => 'Daniel Rodriguez', 'student_id' => 'STU014', 'class' => '9B', 'section' => 'General'],
            ['name' => 'Linda Hernandez', 'student_id' => 'STU015', 'class' => '9B', 'section' => 'General'],
        ];

        foreach ($students as $student) {
            Student::create($student);
        }

        $this->command->info('✅ Created ' . count($students) . ' students');
        $this->command->info('   - Class 10A (Science): 4 students');
        $this->command->info('   - Class 10B (Arts): 3 students');
        $this->command->info('   - Class 9A (General): 3 students');
        $this->command->info('   - Class 9B (General): 5 students');
    }
}
