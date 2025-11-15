<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();
        $users = User::all();
        
        if ($students->isEmpty()) {
            $this->command->warn('⚠️  No students found. Run StudentSeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('⚠️  No users found. Run UserSeeder first.');
            return;
        }

        $statuses = ['present', 'absent', 'late'];
        $recordCount = 0;

        // Create attendance records for the past 14 days
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $randomUser = $users->random();
            
            foreach ($students as $student) {
                // 85% chance of being present, 10% absent, 5% late
                $rand = rand(1, 100);
                if ($rand <= 85) {
                    $status = 'present';
                    $note = null;
                } elseif ($rand <= 95) {
                    $status = 'absent';
                    $note = ['Sick', 'Family emergency', 'Medical appointment'][array_rand(['Sick', 'Family emergency', 'Medical appointment'])];
                } else {
                    $status = 'late';
                    $note = 'Arrived late';
                }
                
                Attendance::create([
                    'student_id' => $student->id,
                    'user_id' => $randomUser->id,
                    'date' => $date,
                    'status' => $status,
                    'note' => $note,
                    'recorded_by' => $randomUser->name,
                ]);
                
                $recordCount++;
            }
        }

        $this->command->info('✅ Created ' . $recordCount . ' attendance records');
        $this->command->info('   - Date range: ' . Carbon::now()->subDays(13)->format('Y-m-d') . ' to ' . Carbon::now()->format('Y-m-d'));
        $this->command->info('   - Records per day: ' . $students->count());
    }
}
