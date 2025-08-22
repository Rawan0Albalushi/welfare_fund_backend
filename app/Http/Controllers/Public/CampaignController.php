<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampaignResource;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Public Campaigns",
 *     description="Public API Endpoints for browsing donation campaigns"
 * )
 */
class CampaignController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/campaigns",
     *     summary="Get campaigns with optional filtering",
     *     tags={"Public Campaigns"},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in title and description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="urgent",
     *         in="query",
     *         description="Show only urgent campaigns (ending within 7 days)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="featured",
     *         in="query",
     *         description="Show only featured campaigns",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Campaigns retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Campaigns retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CampaignResource")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Campaign::active()->with(['category', 'donations' => function ($query) {
            $query->where('status', 'paid');
        }]);

        // Filter by category
        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }

        // Search functionality
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filter urgent campaigns
        if ($request->boolean('urgent')) {
            $query->urgent();
        }

        // Filter featured campaigns
        if ($request->boolean('featured')) {
            $query->featured();
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

    /**
     * @OA\Get(
     *     path="/api/v1/campaigns/{id}",
     *     summary="Get a specific campaign with detailed information",
     *     tags={"Public Campaigns"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Campaign ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Campaign retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Campaign retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/CampaignResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Campaign not found"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $campaign = Campaign::active()
            ->with(['category', 'donations' => function ($query) {
                $query->where('status', 'paid');
            }])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Campaign retrieved successfully',
            'data' => new CampaignResource($campaign),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/campaigns/urgent",
     *     summary="Get urgent campaigns (ending within 7 days)",
     *     tags={"Public Campaigns"},
     *     @OA\Response(
     *         response=200,
     *         description="Urgent campaigns retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Urgent campaigns retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CampaignResource"))
     *         )
     *     )
     * )
     */
    public function urgent(): JsonResponse
    {
        $campaigns = Campaign::active()
            ->with(['category', 'donations' => function ($query) {
                $query->where('status', 'paid');
            }])
            ->urgent()
            ->get();

        return response()->json([
            'message' => 'Urgent campaigns retrieved successfully',
            'data' => CampaignResource::collection($campaigns),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/campaigns/featured",
     *     summary="Get featured campaigns",
     *     tags={"Public Campaigns"},
     *     @OA\Response(
     *         response=200,
     *         description="Featured campaigns retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Featured campaigns retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CampaignResource"))
     *         )
     *     )
     * )
     */
    public function featured(): JsonResponse
    {
        $campaigns = Campaign::active()
            ->with(['category', 'donations' => function ($query) {
                $query->where('status', 'paid');
            }])
            ->featured()
            ->limit(6)
            ->get();

        return response()->json([
            'message' => 'Featured campaigns retrieved successfully',
            'data' => CampaignResource::collection($campaigns),
        ]);
    }
}
