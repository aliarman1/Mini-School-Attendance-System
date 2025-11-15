<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Http\Requests\StoreSectionRequest;
use App\Http\Requests\UpdateSectionRequest;
use App\Http\Resources\SectionResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SectionController extends Controller
{
    /**
     * Display a listing of sections with pagination and search
     */
    public function index(Request $request): JsonResponse
    {
        $query = Section::query()->withCount('students');
        
        // Search by name
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $sections = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => SectionResource::collection($sections),
            'pagination' => [
                'total' => $sections->total(),
                'per_page' => $sections->perPage(),
                'current_page' => $sections->currentPage(),
                'last_page' => $sections->lastPage(),
                'from' => $sections->firstItem(),
                'to' => $sections->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created section
     */
    public function store(StoreSectionRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        $section = Section::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Section created successfully',
            'data' => new SectionResource($section),
        ], 201);
    }

    /**
     * Display the specified section
     */
    public function show(Section $section): JsonResponse
    {
        $section->loadCount('students');
        
        return response()->json([
            'success' => true,
            'data' => new SectionResource($section),
        ]);
    }

    /**
     * Update the specified section
     */
    public function update(UpdateSectionRequest $request, Section $section): JsonResponse
    {
        $data = $request->validated();
        
        $section->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Section updated successfully',
            'data' => new SectionResource($section),
        ]);
    }

    /**
     * Remove the specified section
     */
    public function destroy(Section $section): JsonResponse
    {
        $section->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully',
        ]);
    }
}
