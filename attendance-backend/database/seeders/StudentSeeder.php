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

        // Create test students with diverse classes and sections
        $students = [
            // Class 9, Section A
            ['name' => 'Emily Davis', 'student_id' => 'STU001', 'class_id' => $class9->id, 'section_id' => $sectionA->id],
            ['name' => 'Robert Miller', 'student_id' => 'STU002', 'class_id' => $class9->id, 'section_id' => $sectionA->id],
            ['name' => 'Maria Taylor', 'student_id' => 'STU003', 'class_id' => $class9->id, 'section_id' => $sectionA->id],
            
            // Class 9, Section B
            ['name' => 'Lisa Wilson', 'student_id' => 'STU004', 'class_id' => $class9->id, 'section_id' => $sectionB->id],
            ['name' => 'James Moore', 'student_id' => 'STU005', 'class_id' => $class9->id, 'section_id' => $sectionB->id],
            ['name' => 'Michael Anderson', 'student_id' => 'STU006', 'class_id' => $class9->id, 'section_id' => $sectionB->id],
            
            // Class 9, Section C
            ['name' => 'Patricia Martinez', 'student_id' => 'STU007', 'class_id' => $class9->id, 'section_id' => $sectionC->id],
            ['name' => 'Daniel Rodriguez', 'student_id' => 'STU008', 'class_id' => $class9->id, 'section_id' => $sectionC->id],
            
            // Class 10, Section A
            ['name' => 'John Doe', 'student_id' => 'STU009', 'class_id' => $class10->id, 'section_id' => $sectionA->id],
            ['name' => 'Jane Smith', 'student_id' => 'STU010', 'class_id' => $class10->id, 'section_id' => $sectionA->id],
            ['name' => 'Jennifer Garcia', 'student_id' => 'STU011', 'class_id' => $class10->id, 'section_id' => $sectionA->id],
            
            // Class 10, Section B
            ['name' => 'Sarah Williams', 'student_id' => 'STU012', 'class_id' => $class10->id, 'section_id' => $sectionB->id],
            ['name' => 'David Brown', 'student_id' => 'STU013', 'class_id' => $class10->id, 'section_id' => $sectionB->id],
            
            // Class 10, Section C
            ['name' => 'Mike Johnson', 'student_id' => 'STU014', 'class_id' => $class10->id, 'section_id' => $sectionC->id],
            ['name' => 'Linda Hernandez', 'student_id' => 'STU015', 'class_id' => $class10->id, 'section_id' => $sectionC->id],
            
            // Class 11, Section A
            ['name' => 'Christopher Lee', 'student_id' => 'STU016', 'class_id' => $class11->id, 'section_id' => $sectionA->id],
            ['name' => 'Amanda White', 'student_id' => 'STU017', 'class_id' => $class11->id, 'section_id' => $sectionA->id],
            
            // Class 11, Section B
            ['name' => 'Matthew Harris', 'student_id' => 'STU018', 'class_id' => $class11->id, 'section_id' => $sectionB->id],
            ['name' => 'Jessica Martin', 'student_id' => 'STU019', 'class_id' => $class11->id, 'section_id' => $sectionB->id],
            
            // Class 11, Section C
            ['name' => 'Kevin Thompson', 'student_id' => 'STU020', 'class_id' => $class11->id, 'section_id' => $sectionC->id],
            
            // Class 12, Section A
            ['name' => 'Ashley Garcia', 'student_id' => 'STU021', 'class_id' => $class12->id, 'section_id' => $sectionA->id],
            ['name' => 'Joshua Robinson', 'student_id' => 'STU022', 'class_id' => $class12->id, 'section_id' => $sectionA->id],
            
            // Class 12, Section B
            ['name' => 'Brittany Clark', 'student_id' => 'STU023', 'class_id' => $class12->id, 'section_id' => $sectionB->id],
            ['name' => 'Andrew Lewis', 'student_id' => 'STU024', 'class_id' => $class12->id, 'section_id' => $sectionB->id],
            
            // Class 12, Section C
            ['name' => 'Samantha Walker', 'student_id' => 'STU025', 'class_id' => $class12->id, 'section_id' => $sectionC->id],
        ];

        foreach ($students as $student) {
            Student::create($student);
        }

        $this->command->info('✅ Created ' . count($students) . ' students');
        $this->command->info('   - Class 9, Section A: 3 students');
        $this->command->info('   - Class 9, Section B: 3 students');
        $this->command->info('   - Class 9, Section C: 2 students');
        $this->command->info('   - Class 10, Section A: 3 students');
        $this->command->info('   - Class 10, Section B: 2 students');
        $this->command->info('   - Class 10, Section C: 2 students');
        $this->command->info('   - Class 11, Section A: 2 students');
        $this->command->info('   - Class 11, Section B: 2 students');
        $this->command->info('   - Class 11, Section C: 1 student');
        $this->command->info('   - Class 12, Section A: 2 students');
        $this->command->info('   - Class 12, Section B: 2 students');
        $this->command->info('   - Class 12, Section C: 1 student');
    }
}
