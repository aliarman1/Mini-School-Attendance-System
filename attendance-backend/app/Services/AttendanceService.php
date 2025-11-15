<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use App\Events\AttendanceRecorded;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Record bulk attendance
     */
    public function recordBulkAttendance(array $data, $userId = null): array
    {
        DB::beginTransaction();
        
        try {
            $attendances = [];
            $date = Carbon::parse($data['date']);
            $recordedBy = $data['recorded_by'] ?? null;
            
            foreach ($data['attendances'] as $record) {
                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id' => $record['student_id'],
                        'date' => $date,
                    ],
                    [
                        'status' => $record['status'],
                        'note' => $record['note'] ?? null,
                        'recorded_by' => $recordedBy,
                        'user_id' => $userId,
                    ]
                );
                
                $attendances[] = $attendance;
                
                // Fire event for each attendance record
                event(new AttendanceRecorded($attendance));
            }
            
            DB::commit();
            
            // Clear cached statistics
            $this->clearStatisticsCache();
            
            return $attendances;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate monthly attendance report with eager loading
     */
    public function generateMonthlyReport(string $month, string $class): array
    {
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();
        
        $students = Student::where('class', $class)
            ->with(['attendances' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }])
            ->get();
        
        $report = [];
        
        foreach ($students as $student) {
            $totalDays = $student->attendances->count();
            $presentDays = $student->attendances->where('status', 'present')->count();
            $absentDays = $student->attendances->where('status', 'absent')->count();
            $lateDays = $student->attendances->where('status', 'late')->count();
            $percentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0;
            
            $report[] = [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'student_number' => $student->student_id,
                'total_days' => $totalDays,
                'present' => $presentDays,
                'absent' => $absentDays,
                'late' => $lateDays,
                'attendance_percentage' => $percentage,
            ];
        }
        
        return $report;
    }

    /**
     * Get attendance statistics with Redis caching
     */
    public function getStatistics(): array
    {
        return Cache::remember('attendance_statistics', 3600, function () {
            $totalStudents = Student::count();
            $today = Carbon::today();
            
            $todayAttendance = Attendance::where('date', $today)->get();
            $todayPresent = $todayAttendance->where('status', 'present')->count();
            $todayAbsent = $todayAttendance->where('status', 'absent')->count();
            $todayLate = $todayAttendance->where('status', 'late')->count();
            
            // Monthly statistics
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();
            
            $monthlyAttendance = Attendance::whereBetween('date', [$monthStart, $monthEnd])->get();
            $monthlyPresent = $monthlyAttendance->where('status', 'present')->count();
            $monthlyTotal = $monthlyAttendance->count();
            $monthlyPercentage = $monthlyTotal > 0 ? round(($monthlyPresent / $monthlyTotal) * 100, 2) : 0;
            
            return [
                'total_students' => $totalStudents,
                'today' => [
                    'total_recorded' => $todayAttendance->count(),
                    'present' => $todayPresent,
                    'absent' => $todayAbsent,
                    'late' => $todayLate,
                    'percentage' => $todayAttendance->count() > 0 
                        ? round(($todayPresent / $todayAttendance->count()) * 100, 2) 
                        : 0,
                ],
                'monthly' => [
                    'total_records' => $monthlyTotal,
                    'present' => $monthlyPresent,
                    'percentage' => $monthlyPercentage,
                ],
            ];
        });
    }

    /**
     * Get today's attendance
     */
    public function getTodayAttendance(): array
    {
        $today = Carbon::today();
        
        $attendances = Attendance::with('student')
            ->where('date', $today)
            ->get();
        
        return [
            'date' => $today->format('Y-m-d'),
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'records' => $attendances,
        ];
    }

    /**
     * Clear cached statistics
     */
    protected function clearStatisticsCache(): void
    {
        Cache::forget('attendance_statistics');
    }
}
