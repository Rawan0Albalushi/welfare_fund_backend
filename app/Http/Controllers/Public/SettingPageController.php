<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingPageResource;
use App\Models\SettingPage;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Public - Settings Pages",
 *     description="Public API Endpoints for settings pages"
 * )
 */
class SettingPageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/settings-pages",
     *     summary="Get all settings pages",
     *     tags={"Public - Settings Pages"},
     *     @OA\Response(
     *         response=200,
     *         description="Settings pages retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Settings pages retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SettingPageResource"))
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $pages = SettingPage::orderBy('created_at', 'asc')->get();

        return response()->json([
            'message' => 'Settings pages retrieved successfully',
            'data' => SettingPageResource::collection($pages),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/settings-pages/{key}",
     *     summary="Get a specific settings page by key",
     *     tags={"Public - Settings Pages"},
     *     @OA\Parameter(
     *         name="key",
     *         in="path",
     *         description="Settings page key (privacy_policy, about_app, security, contact_us)",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Settings page retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Settings page retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/SettingPageResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Settings page not found"
     *     )
     * )
     */
    public function show(string $key): JsonResponse
    {
        $page = SettingPage::findByKey($key);

        if (!$page) {
            return response()->json([
                'message' => 'Settings page not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Settings page retrieved successfully',
            'data' => new SettingPageResource($page),
        ]);
    }
}
