<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProgramResource;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Admin - Programs",
 *     description="Admin API Endpoints for program management"
 * )
 */
class ProgramController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $programs = Program::orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'message' => 'Programs retrieved successfully',
            'data' => ProgramResource::collection($programs),
            'meta' => [
                'current_page' => $programs->currentPage(),
                'per_page' => $programs->perPage(),
                'total' => $programs->total(),
                'last_page' => $programs->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            'image' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|in:draft,active,paused,archived',
        ]);

        try {
            $attributes = $validated;

            if (!isset($attributes['status']) || $attributes['status'] === null) {
                unset($attributes['status']);
            }

            $program = Program::create($attributes);

            return response()->json([
                'message' => 'Program created successfully',
                'data' => new ProgramResource($program),
            ], 201);
        } catch (\Throwable $e) {
            \Log::error('Failed to create program', [
                'exception' => $e,
            ]);
            return response()->json([
                'message' => 'Failed to create program',
                'error' => app()->environment('production') ? 'Server error' : $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $program = Program::findOrFail($id);

        return response()->json([
            'message' => 'Program retrieved successfully',
            'data' => new ProgramResource($program),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $program = Program::findOrFail($id);

        $validated = $request->validate([
            'title_ar' => 'sometimes|string|max:255',
            'title_en' => 'sometimes|string|max:255',
            'description_ar' => 'sometimes|string',
            'description_en' => 'sometimes|string',
            'image' => 'sometimes|nullable|string',
            'status' => 'sometimes|in:draft,active,paused,archived',
        ]);

        if (array_key_exists('status', $validated) && $validated['status'] === null) {
            unset($validated['status']);
        }

        $program->update($validated);

        return response()->json([
            'message' => 'Program updated successfully',
            'data' => new ProgramResource($program->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $program = Program::findOrFail($id);
        $program->delete();

        return response()->json([
            'message' => 'Program deleted successfully',
        ]);
    }
}
