<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AttendanceGenerateReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-report {month} {class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly attendance report for a specific class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->argument('month');
        $class = $this->argument('class');
        
        $this->info("Generating attendance report for {$class} in {$month}...");
        
        try {
            $service = app(\App\Services\AttendanceService::class);
            $report = $service->generateMonthlyReport($month, $class);
            
            $this->info("\nAttendance Report for Class: {$class} - Month: {$month}");
            $this->line(str_repeat('=', 80));
            
            $headers = ['Student ID', 'Name', 'Total Days', 'Present', 'Absent', 'Late', 'Percentage'];
            $rows = [];
            
            foreach ($report as $record) {
                $rows[] = [
                    $record['student_number'],
                    $record['student_name'],
                    $record['total_days'],
                    $record['present'],
                    $record['absent'],
                    $record['late'],
                    $record['attendance_percentage'] . '%',
                ];
            }
            
            $this->table($headers, $rows);
            
            $this->info("\nTotal Students: " . count($report));
            $this->success("Report generated successfully!");
            
        } catch (\Exception $e) {
            $this->error("Failed to generate report: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
