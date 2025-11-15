<?php

namespace App\Http\Controllers;

use App\Services\AttendanceService;
use App\Http\Requests\BulkAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display a listing of attendance records
     */
    public function index(Request $request): JsonResponse
    {
        $query = Attendance::with(['student.class', 'recordedBy']);
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->input('start_date'));
        }
        
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->input('end_date'));
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        
        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->input('student_id'));
        }
        
        // Filter by class
        if ($request->filled('class_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->input('class_id'));
            });
        }
        
        $perPage = $request->input('per_page', 15);
        $attendances = $query->orderBy('date', 'desc')->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => AttendanceResource::collection($attendances),
            'pagination' => [
                'total' => $attendances->total(),
                'per_page' => $attendances->perPage(),
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
            ],
        ]);
    }

    /**
     * Store bulk attendance records
     */
    public function bulkStore(BulkAttendanceRequest $request): JsonResponse
    {
        try {
            $attendances = $this->attendanceService->recordBulkAttendance(
                $request->validated(),
                $request->user()->id ?? null
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully',
                'data' => AttendanceResource::collection($attendances),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record attendance',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified attendance record
     */
    public function show(Attendance $attendance): JsonResponse
    {
        $attendance->load(['student.class', 'recordedBy']);
        
        return response()->json([
            'success' => true,
            'data' => new AttendanceResource($attendance),
        ]);
    }

    /**
     * Update the specified attendance record
     */
    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:present,absent,late',
            'note' => 'nullable|string|max:500',
        ]);
        
        $attendance->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully',
            'data' => new AttendanceResource($attendance),
        ]);
    }

    /**
     * Remove the specified attendance record
     */
    public function destroy(Attendance $attendance): JsonResponse
    {
        $attendance->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Attendance deleted successfully',
        ]);
    }

    /**
     * Generate monthly attendance report
     */
    public function monthlyReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'class_id' => 'required|exists:classes,id',
        ]);
        
        try {
            $report = $this->attendanceService->generateMonthlyReport(
                $validated['month'],
                $validated['class_id']
            );
            
            return response()->json([
                'success' => true,
                'data' => $report,
                'meta' => [
                    'month' => $validated['month'],
                    'class_id' => $validated['class_id'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get attendance statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->attendanceService->getStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get today's attendance
     */
    public function todayAttendance(): JsonResponse
    {
        try {
            $data = $this->attendanceService->getTodayAttendance();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $data['date'],
                    'total' => $data['total'],
                    'present' => $data['present'],
                    'absent' => $data['absent'],
                    'late' => $data['late'],
                    'records' => AttendanceResource::collection($data['records']),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get today\'s attendance',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
