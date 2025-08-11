<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentApplicationResource;
use App\Models\StudentApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Admin - Applications",
 *     description="Admin API Endpoints for application management"
 * )
 */
class ApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StudentApplication::with(['user:id,name,phone', 'program:id,title']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'message' => 'Applications retrieved successfully',
            'data' => StudentApplicationResource::collection($applications),
            'meta' => [
                'current_page' => $applications->currentPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
                'last_page' => $applications->lastPage(),
            ],
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $application = StudentApplication::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:under_review,accepted,rejected',
            'reject_reason' => 'required_if:status,rejected|string|max:1000',
        ]);

        $application->update($validated);

        return response()->json([
            'message' => 'Application status updated successfully',
            'data' => new StudentApplicationResource($application->load(['user:id,name,phone', 'program:id,title'])),
        ]);
    }
}
