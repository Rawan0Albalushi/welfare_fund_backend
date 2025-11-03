<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampaignResource;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Admin - Campaigns",
 *     description="Admin API Endpoints for campaign management"
 * )
 */
class CampaignController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Campaign::with('category');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $campaigns = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'message' => 'Campaigns retrieved successfully',
            'data' => CampaignResource::collection($campaigns),
            'meta' => [
                'current_page' => $campaigns->currentPage(),
                'per_page' => $campaigns->perPage(),
                'total' => $campaigns->total(),
                'last_page' => $campaigns->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            'image' => 'nullable|string',
            'goal_amount' => 'required|numeric|min:0',
            'status' => 'sometimes|in:draft,active,paused,completed,archived',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'target_donors' => 'nullable|integer|min:0',
            'impact_description_ar' => 'nullable|string',
            'impact_description_en' => 'nullable|string',
            'campaign_highlights' => 'nullable|array',
        ]);

        $campaign = Campaign::create($validated);

        return response()->json([
            'message' => 'Campaign created successfully',
            'data' => new CampaignResource($campaign->load('category')),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $campaign = Campaign::with('category')->findOrFail($id);

        return response()->json([
            'message' => 'Campaign retrieved successfully',
            'data' => new CampaignResource($campaign),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $campaign = Campaign::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'title_ar' => 'sometimes|string|max:255',
            'title_en' => 'sometimes|string|max:255',
            'description_ar' => 'sometimes|string',
            'description_en' => 'sometimes|string',
            'image' => 'nullable|string',
            'goal_amount' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:draft,active,paused,completed,archived',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'target_donors' => 'nullable|integer|min:0',
            'impact_description_ar' => 'nullable|string',
            'impact_description_en' => 'nullable|string',
            'campaign_highlights' => 'nullable|array',
        ]);

        $campaign->update($validated);

        return response()->json([
            'message' => 'Campaign updated successfully',
            'data' => new CampaignResource($campaign->load('category')),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->delete();

        return response()->json([
            'message' => 'Campaign deleted successfully',
        ]);
    }
}


