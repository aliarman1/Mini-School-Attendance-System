<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test students
        $students = [
            ['name' => 'John Doe', 'student_id' => 'STU001', 'class' => '10A', 'section' => 'Science'],
            ['name' => 'Jane Smith', 'student_id' => 'STU002', 'class' => '10A', 'section' => 'Science'],
            ['name' => 'Mike Johnson', 'student_id' => 'STU003', 'class' => '10A', 'section' => 'Science'],
            ['name' => 'Sarah Williams', 'student_id' => 'STU004', 'class' => '10B', 'section' => 'Arts'],
            ['name' => 'David Brown', 'student_id' => 'STU005', 'class' => '10B', 'section' => 'Arts'],
            ['name' => 'Emily Davis', 'student_id' => 'STU006', 'class' => '9A', 'section' => 'General'],
            ['name' => 'Robert Miller', 'student_id' => 'STU007', 'class' => '9A', 'section' => 'General'],
            ['name' => 'Lisa Wilson', 'student_id' => 'STU008', 'class' => '9B', 'section' => 'General'],
            ['name' => 'James Moore', 'student_id' => 'STU009', 'class' => '9B', 'section' => 'General'],
            ['name' => 'Maria Taylor', 'student_id' => 'STU010', 'class' => '10A', 'section' => 'Science'],
        ];

        foreach ($students as $student) {
            \App\Models\Student::create($student);
        }

        // Create sample attendance records for the past 7 days
        $studentIds = \App\Models\Student::pluck('id')->toArray();
        $statuses = ['present', 'absent', 'late'];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            
            foreach ($studentIds as $studentId) {
                // 80% chance of being present
                $status = rand(1, 10) <= 8 ? 'present' : $statuses[array_rand($statuses)];
                
                \App\Models\Attendance::create([
                    'student_id' => $studentId,
                    'date' => $date,
                    'status' => $status,
                    'note' => $status === 'absent' ? 'Absent' : null,
                    'recorded_by' => 'System',
                ]);
            }
        }

        $this->command->info('✅ Seeded ' . count($students) . ' students');
        $this->command->info('✅ Seeded attendance records for the past 7 days');
    }
}
