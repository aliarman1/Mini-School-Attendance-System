<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Section;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get class IDs
        $class9 = ClassModel::where('name', '9')->first();
        $class10 = ClassModel::where('name', '10')->first();
        $class11 = ClassModel::where('name', '11')->first();
        $class12 = ClassModel::where('name', '12')->first();

        // Get section IDs
        $sectionA = Section::where('name', 'A')->first();
        $sectionB = Section::where('name', 'B')->first();
        $sectionC = Section::where('name', 'C')->first();
        $sectionScience = Section::where('name', 'Science')->first();
        $sectionCommerce = Section::where('name', 'Commerce')->first();
        $sectionGeneral = Section::where('name', 'General')->first();

        // Create test students with diverse classes and sections
        $students = [
            // Class 10, Section Science
            ['name' => 'John Doe', 'student_id' => 'STU001', 'class_id' => $class10->id, 'section_id' => $sectionScience->id],
            ['name' => 'Jane Smith', 'student_id' => 'STU002', 'class_id' => $class10->id, 'section_id' => $sectionScience->id],
            ['name' => 'Mike Johnson', 'student_id' => 'STU003', 'class_id' => $class10->id, 'section_id' => $sectionScience->id],
            
            // Class 10, Section Commerce
            ['name' => 'Sarah Williams', 'student_id' => 'STU004', 'class_id' => $class10->id, 'section_id' => $sectionCommerce->id],
            ['name' => 'David Brown', 'student_id' => 'STU005', 'class_id' => $class10->id, 'section_id' => $sectionCommerce->id],
            
            // Class 10, Section A
            ['name' => 'Maria Taylor', 'student_id' => 'STU010', 'class_id' => $class10->id, 'section_id' => $sectionA->id],
            ['name' => 'Jennifer Garcia', 'student_id' => 'STU011', 'class_id' => $class10->id, 'section_id' => $sectionA->id],
            
            // Class 9, Section A
            ['name' => 'Emily Davis', 'student_id' => 'STU006', 'class_id' => $class9->id, 'section_id' => $sectionA->id],
            ['name' => 'Robert Miller', 'student_id' => 'STU007', 'class_id' => $class9->id, 'section_id' => $sectionA->id],
            
            // Class 9, Section B
            ['name' => 'Lisa Wilson', 'student_id' => 'STU008', 'class_id' => $class9->id, 'section_id' => $sectionB->id],
            ['name' => 'James Moore', 'student_id' => 'STU009', 'class_id' => $class9->id, 'section_id' => $sectionB->id],
            ['name' => 'Michael Anderson', 'student_id' => 'STU012', 'class_id' => $class9->id, 'section_id' => $sectionB->id],
            
            // Class 11, Section Science
            ['name' => 'Patricia Martinez', 'student_id' => 'STU013', 'class_id' => $class11->id, 'section_id' => $sectionScience->id],
            ['name' => 'Daniel Rodriguez', 'student_id' => 'STU014', 'class_id' => $class11->id, 'section_id' => $sectionScience->id],
            
            // Class 12, Section General
            ['name' => 'Linda Hernandez', 'student_id' => 'STU015', 'class_id' => $class12->id, 'section_id' => $sectionGeneral->id],
        ];

        foreach ($students as $student) {
            Student::create($student);
        }

        $this->command->info('✅ Created ' . count($students) . ' students');
        $this->command->info('   - Class 9, Section A: 2 students');
        $this->command->info('   - Class 9, Section B: 3 students');
        $this->command->info('   - Class 10, Section A: 2 students');
        $this->command->info('   - Class 10, Section Science: 3 students');
        $this->command->info('   - Class 10, Section Commerce: 2 students');
        $this->command->info('   - Class 11, Section Science: 2 students');
        $this->command->info('   - Class 12, Section General: 1 student');
    }
}
