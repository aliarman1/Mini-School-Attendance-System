<?php

namespace App\Console\Commands;

use App\Models\ClassModel;
use App\Models\Section;
use App\Services\AttendanceService;
use Illuminate\Console\Command;

class AttendanceGenerateReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-report 
                            {month : Month in YYYY-MM format (e.g., 2025-11)} 
                            {class : Class name (e.g., 9, 10, 11, 12) or class ID}
                            {--section= : Optional section name (e.g., A, B, C) or section ID}
                            {--format=table : Output format: table, csv, json}
                            {--output= : Output file path (for csv/json format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly attendance report for a specific class and optional section';

    protected AttendanceService $attendanceService;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->attendanceService = app(AttendanceService::class);
        
        $month = $this->argument('month');
        $classInput = $this->argument('class');
        $sectionInput = $this->option('section');
        $format = $this->option('format');
        $outputPath = $this->option('output');
        
        // Validate month format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->error('Invalid month format. Use YYYY-MM (e.g., 2025-11)');
            return 1;
        }
        
        try {
            // Find the class
            $class = $this->findClass($classInput);
            if (!$class) {
                $this->error("Class '{$classInput}' not found.");
                $this->info("\nAvailable classes:");
                $this->showAvailableClasses();
                return 1;
            }
            
            // Find the section if provided
            $section = null;
            if ($sectionInput) {
                $section = $this->findSection($sectionInput);
                if (!$section) {
                    $this->error("Section '{$sectionInput}' not found.");
                    $this->info("\nAvailable sections:");
                    $this->showAvailableSections();
                    return 1;
                }
            }
            
            // Generate report
            $this->info("Generating attendance report...");
            $this->line("Class: {$class->name}" . ($section ? " - Section: {$section->name}" : ""));
            $this->line("Month: {$month}");
            $this->newLine();
            
            $report = $this->attendanceService->generateMonthlyReport($month, $class->id);
            
            // Filter by section if provided
            if ($section) {
                $report = $this->filterReportBySection($report, $section->id);
            }
            
            if (empty($report)) {
                $this->warn("No attendance records found for the specified criteria.");
                return 0;
            }
            
            // Output based on format
            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($report, $outputPath, $class, $section, $month);
                case 'json':
                    return $this->exportToJson($report, $outputPath, $class, $section, $month);
                case 'table':
                default:
                    return $this->displayTable($report, $class, $section, $month);
            }
            
        } catch (\Exception $e) {
            $this->error("Failed to generate report: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Find class by name or ID
     */
    protected function findClass($input): ?ClassModel
    {
        // Try to find by ID first
        if (is_numeric($input)) {
            $class = ClassModel::find($input);
            if ($class) {
                return $class;
            }
        }
        
        // Try to find by name
        return ClassModel::where('name', $input)->first();
    }
    
    /**
     * Find section by name or ID
     */
    protected function findSection($input): ?Section
    {
        // Try to find by ID first
        if (is_numeric($input)) {
            $section = Section::find($input);
            if ($section) {
                return $section;
            }
        }
        
        // Try to find by name
        return Section::where('name', $input)->first();
    }
    
    /**
     * Filter report by section
     */
    protected function filterReportBySection(array $report, int $sectionId): array
    {
        return array_filter($report, function ($record) use ($sectionId) {
            $student = \App\Models\Student::find($record['student_id']);
            return $student && $student->section_id == $sectionId;
        });
    }
    
    /**
     * Display report as table
     */
    protected function displayTable(array $report, ClassModel $class, ?Section $section, string $month): int
    {
        $this->info("Attendance Report");
        $this->line("Class: {$class->name}" . ($section ? " - Section: {$section->name}" : ""));
        $this->line("Month: {$month}");
        $this->line(str_repeat('=', 100));
        $this->newLine();
        
        $headers = ['Student ID', 'Name', 'Total Days', 'Present', 'Absent', 'Late', 'Percentage'];
        $rows = [];
        
        $totalPresent = 0;
        $totalAbsent = 0;
        $totalLate = 0;
        $totalDays = 0;
        
        foreach ($report as $record) {
            $rows[] = [
                $record['student_number'],
                $record['student_name'],
                $record['total_days'],
                $record['present'],
                $record['absent'],
                $record['late'],
                number_format($record['attendance_percentage'], 2) . '%',
            ];
            
            $totalPresent += $record['present'];
            $totalAbsent += $record['absent'];
            $totalLate += $record['late'];
            $totalDays += $record['total_days'];
        }
        
        $this->table($headers, $rows);
        
        $this->newLine();
        $this->info("Summary Statistics:");
        $this->line("Total Students: " . count($report));
        $this->line("Total Days Recorded: {$totalDays}");
        $this->line("Total Present: {$totalPresent}");
        $this->line("Total Absent: {$totalAbsent}");
        $this->line("Total Late: {$totalLate}");
        
        if ($totalDays > 0) {
            $overallPercentage = ($totalPresent / $totalDays) * 100;
            $this->line("Overall Attendance: " . number_format($overallPercentage, 2) . '%');
        }
        
        $this->newLine();
        $this->info("✓ Report generated successfully!");
        
        return 0;
    }
    
    /**
     * Export report to CSV
     */
    protected function exportToCsv(array $report, ?string $outputPath, ClassModel $class, ?Section $section, string $month): int
    {
        if (!$outputPath) {
            $filename = 'attendance_report_' . $class->name . ($section ? '_' . $section->name : '') . '_' . str_replace('-', '', $month) . '.csv';
            $outputPath = storage_path('app/reports/' . $filename);
        }
        
        // Ensure directory exists
        $directory = dirname($outputPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $fp = fopen($outputPath, 'w');
        
        // Write header info
        fputcsv($fp, ['Attendance Report']);
        fputcsv($fp, ['Class', $class->name]);
        if ($section) {
            fputcsv($fp, ['Section', $section->name]);
        }
        fputcsv($fp, ['Month', $month]);
        fputcsv($fp, ['Generated', now()->toDateTimeString()]);
        fputcsv($fp, []);
        
        // Write column headers
        fputcsv($fp, ['Student ID', 'Name', 'Total Days', 'Present', 'Absent', 'Late', 'Percentage']);
        
        // Write data
        foreach ($report as $record) {
            fputcsv($fp, [
                $record['student_number'],
                $record['student_name'],
                $record['total_days'],
                $record['present'],
                $record['absent'],
                $record['late'],
                $record['attendance_percentage'] . '%',
            ]);
        }
        
        // Write summary
        $totalPresent = array_sum(array_column($report, 'present'));
        $totalAbsent = array_sum(array_column($report, 'absent'));
        $totalLate = array_sum(array_column($report, 'late'));
        $totalDays = array_sum(array_column($report, 'total_days'));
        
        fputcsv($fp, []);
        fputcsv($fp, ['Summary']);
        fputcsv($fp, ['Total Students', count($report)]);
        fputcsv($fp, ['Total Days', $totalDays]);
        fputcsv($fp, ['Total Present', $totalPresent]);
        fputcsv($fp, ['Total Absent', $totalAbsent]);
        fputcsv($fp, ['Total Late', $totalLate]);
        
        if ($totalDays > 0) {
            $overallPercentage = ($totalPresent / $totalDays) * 100;
            fputcsv($fp, ['Overall Attendance', number_format($overallPercentage, 2) . '%']);
        }
        
        fclose($fp);
        
        $this->info("✓ Report exported to: {$outputPath}");
        
        return 0;
    }
    
    /**
     * Export report to JSON
     */
    protected function exportToJson(array $report, ?string $outputPath, ClassModel $class, ?Section $section, string $month): int
    {
        if (!$outputPath) {
            $filename = 'attendance_report_' . $class->name . ($section ? '_' . $section->name : '') . '_' . str_replace('-', '', $month) . '.json';
            $outputPath = storage_path('app/reports/' . $filename);
        }
        
        // Ensure directory exists
        $directory = dirname($outputPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $totalPresent = array_sum(array_column($report, 'present'));
        $totalAbsent = array_sum(array_column($report, 'absent'));
        $totalLate = array_sum(array_column($report, 'late'));
        $totalDays = array_sum(array_column($report, 'total_days'));
        
        $output = [
            'metadata' => [
                'class' => $class->name,
                'class_id' => $class->id,
                'section' => $section ? $section->name : null,
                'section_id' => $section ? $section->id : null,
                'month' => $month,
                'generated_at' => now()->toIso8601String(),
            ],
            'summary' => [
                'total_students' => count($report),
                'total_days' => $totalDays,
                'total_present' => $totalPresent,
                'total_absent' => $totalAbsent,
                'total_late' => $totalLate,
                'overall_percentage' => $totalDays > 0 ? round(($totalPresent / $totalDays) * 100, 2) : 0,
            ],
            'students' => array_values($report),
        ];
        
        file_put_contents($outputPath, json_encode($output, JSON_PRETTY_PRINT));
        
        $this->info("✓ Report exported to: {$outputPath}");
        
        return 0;
    }
    
    /**
     * Show available classes
     */
    protected function showAvailableClasses(): void
    {
        $classes = ClassModel::withCount('students')->get();
        
        if ($classes->isEmpty()) {
            $this->warn("No classes found in the database.");
            return;
        }
        
        $rows = [];
        foreach ($classes as $class) {
            $rows[] = [
                $class->id,
                $class->name,
                $class->students_count,
            ];
        }
        
        $this->table(['ID', 'Name', 'Students'], $rows);
    }
    
    /**
     * Show available sections
     */
    protected function showAvailableSections(): void
    {
        $sections = Section::withCount('students')->get();
        
        if ($sections->isEmpty()) {
            $this->warn("No sections found in the database.");
            return;
        }
        
        $rows = [];
        foreach ($sections as $section) {
            $rows[] = [
                $section->id,
                $section->name,
                $section->students_count,
            ];
        }
        
        $this->table(['ID', 'Name', 'Students'], $rows);
    }
}
