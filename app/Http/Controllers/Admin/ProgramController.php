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
        $programs = Program::with('category')
            ->orderBy('created_at', 'desc')
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
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'goal_amount' => 'required|numeric|min:0',
            'status' => 'sometimes|in:draft,active,paused,archived',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
        ]);

        $program = Program::create($validated);

        return response()->json([
            'message' => 'Program created successfully',
            'data' => new ProgramResource($program->load('category')),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $program = Program::with('category')->findOrFail($id);

        return response()->json([
            'message' => 'Program retrieved successfully',
            'data' => new ProgramResource($program),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $program = Program::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'goal_amount' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:draft,active,paused,archived',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
        ]);

        $program->update($validated);

        return response()->json([
            'message' => 'Program updated successfully',
            'data' => new ProgramResource($program->load('category')),
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
