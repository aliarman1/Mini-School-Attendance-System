<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Http\Requests\StoreClassRequest;
use App\Http\Requests\UpdateClassRequest;
use App\Http\Resources\ClassResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClassController extends Controller
{
    /**
     * Display a listing of classes with pagination and search
     */
    public function index(Request $request): JsonResponse
    {
        $query = ClassModel::query()->withCount('students');
        
        // Search by name
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $classes = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => ClassResource::collection($classes),
            'pagination' => [
                'total' => $classes->total(),
                'per_page' => $classes->perPage(),
                'current_page' => $classes->currentPage(),
                'last_page' => $classes->lastPage(),
                'from' => $classes->firstItem(),
                'to' => $classes->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created class
     */
    public function store(StoreClassRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        $class = ClassModel::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Class created successfully',
            'data' => new ClassResource($class),
        ], 201);
    }

    /**
     * Display the specified class
     */
    public function show(ClassModel $class): JsonResponse
    {
        $class->loadCount('students');
        
        return response()->json([
            'success' => true,
            'data' => new ClassResource($class),
        ]);
    }

    /**
     * Update the specified class
     */
    public function update(UpdateClassRequest $request, ClassModel $class): JsonResponse
    {
        $data = $request->validated();
        
        $class->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Class updated successfully',
            'data' => new ClassResource($class),
        ]);
    }

    /**
     * Remove the specified class
     */
    public function destroy(ClassModel $class): JsonResponse
    {
        $class->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Class deleted successfully',
        ]);
    }
}
