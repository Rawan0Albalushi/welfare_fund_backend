<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProgramResource;
use App\Models\Category;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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
        // Accept minimal payload: title (required) and optional status. Other fields defaulted.
        $validated = $request->validate([
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            'status' => 'sometimes|nullable|in:draft,active,paused,archived',
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'goal_amount' => 'sometimes|nullable|numeric|min:0',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date|after:start_date',
        ]);

        try {
            // Resolve defaults for required DB columns
            $categoryId = $validated['category_id'] ?? Category::active()->value('id');
            if (!$categoryId) {
                $category = Category::create(['name' => 'Student Support', 'status' => 'active']);
                $categoryId = $category->id;
            }

            // Build payload only with columns that exist (for legacy DBs)
            $attributes = [
                'category_id' => $categoryId,
                'title' => $validated['title'],
            ];
            if (Schema::hasColumn('programs', 'description')) {
                $attributes['description'] = $validated['description'] ?? '';
            }
            if (Schema::hasColumn('programs', 'goal_amount')) {
                $attributes['goal_amount'] = $validated['goal_amount'] ?? 0;
            }
            if (Schema::hasColumn('programs', 'status')) {
                $attributes['status'] = $validated['status'] ?? 'active';
            }
            if (Schema::hasColumn('programs', 'start_date')) {
                $attributes['start_date'] = $validated['start_date'] ?? null;
            }
            if (Schema::hasColumn('programs', 'end_date')) {
                $attributes['end_date'] = $validated['end_date'] ?? null;
            }

            $program = Program::create($attributes);

            return response()->json([
                'message' => 'Program created successfully',
                'data' => new ProgramResource($program->load('category')),
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
            'title_ar' => 'sometimes|string|max:255',
            'title_en' => 'sometimes|string|max:255',
            'description_ar' => 'sometimes|string',
            'description_en' => 'sometimes|string',
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
