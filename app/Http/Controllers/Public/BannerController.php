<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Public Banners",
 *     description="Public API Endpoints for browsing banners"
 * )
 */
class BannerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/banners",
     *     summary="Get active banners",
     *     tags={"Public Banners"},
     *     @OA\Parameter(
     *         name="featured",
     *         in="query",
     *         description="Show only featured banners",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limit number of results",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Banners retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Banners retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/BannerResource"))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Banner::active();

        // Filter featured banners
        if ($request->boolean('featured')) {
            $query->featured();
        }

        $limit = $request->get('limit', 10);
        $banners = $query->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'message' => 'Banners retrieved successfully',
            'data' => BannerResource::collection($banners),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/banners/{id}",
     *     summary="Get a specific banner",
     *     tags={"Public Banners"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Banner ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Banner retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Banner retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/BannerResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Banner not found"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $banner = Banner::active()->findOrFail($id);

        return response()->json([
            'message' => 'Banner retrieved successfully',
            'data' => new BannerResource($banner),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/banners/featured",
     *     summary="Get featured banners",
     *     tags={"Public Banners"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limit number of results",
     *         required=false,
     *         @OA\Schema(type="integer", default=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Featured banners retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Featured banners retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/BannerResource"))
     *         )
     *     )
     * )
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $banners = Banner::active()
            ->featured()
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'message' => 'Featured banners retrieved successfully',
            'data' => BannerResource::collection($banners),
        ]);
    }
}
