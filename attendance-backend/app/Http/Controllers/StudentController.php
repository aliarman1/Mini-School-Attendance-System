<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /**
     * Display a listing of students with pagination, search, and filter
     */
    public function index(Request $request): JsonResponse
    {
        $query = Student::query();
        
        // Search by name or student_id
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }
        
        // Filter by class
        if ($request->filled('class')) {
            $query->where('class', $request->input('class'));
        }
        
        // Filter by section
        if ($request->filled('section')) {
            $query->where('section', $request->input('section'));
        }
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $students = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => StudentResource::collection($students),
            'pagination' => [
                'total' => $students->total(),
                'per_page' => $students->perPage(),
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'from' => $students->firstItem(),
                'to' => $students->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created student
     */
    public function store(StoreStudentRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('students', 'public');
        }
        
        $student = Student::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Student created successfully',
            'data' => new StudentResource($student),
        ], 201);
    }

    /**
     * Display the specified student
     */
    public function show(Student $student): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new StudentResource($student),
        ]);
    }

    /**
     * Update the specified student
     */
    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        $data = $request->validated();
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            $data['photo'] = $request->file('photo')->store('students', 'public');
        }
        
        $student->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully',
            'data' => new StudentResource($student),
        ]);
    }

    /**
     * Remove the specified student
     */
    public function destroy(Student $student): JsonResponse
    {
        // Delete photo if exists
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }
        
        $student->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully',
        ]);
    }
}
