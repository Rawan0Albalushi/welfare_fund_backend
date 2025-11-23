<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingPageResource;
use App\Models\SettingPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Admin - Settings Pages",
 *     description="Admin API Endpoints for settings pages management"
 * )
 */
class SettingPageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/settings-pages",
     *     summary="Get all settings pages (admin)",
     *     tags={"Admin - Settings Pages"},
     *     security={{"bearerAuth":{}}},
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
     *         description="Settings pages retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Settings pages retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SettingPageResource")),
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
        $pages = SettingPage::orderBy('created_at', 'asc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'message' => 'Settings pages retrieved successfully',
            'data' => SettingPageResource::collection($pages),
            'meta' => [
                'current_page' => $pages->currentPage(),
                'per_page' => $pages->perPage(),
                'total' => $pages->total(),
                'last_page' => $pages->lastPage(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/settings-pages/{key}",
     *     summary="Get a specific settings page by key (admin)",
     *     tags={"Admin - Settings Pages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="key",
     *         in="path",
     *         description="Settings page key",
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

    /**
     * @OA\Post(
     *     path="/api/v1/admin/settings-pages",
     *     summary="Create a new settings page",
     *     tags={"Admin - Settings Pages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"key", "title_ar", "title_en", "content_ar", "content_en"},
     *             @OA\Property(property="key", type="string", example="privacy_policy"),
     *             @OA\Property(property="title_ar", type="string", example="سياسة الخصوصية"),
     *             @OA\Property(property="title_en", type="string", example="Privacy Policy"),
     *             @OA\Property(property="content_ar", type="string", example="محتوى سياسة الخصوصية بالعربية"),
     *             @OA\Property(property="content_en", type="string", example="Privacy policy content in English")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Settings page created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Settings page created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/SettingPageResource")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:setting_pages,key',
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'content_ar' => 'required|string',
            'content_en' => 'required|string',
        ]);

        $page = SettingPage::create($validated);

        return response()->json([
            'message' => 'Settings page created successfully',
            'data' => new SettingPageResource($page),
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/settings-pages/{key}",
     *     summary="Update a settings page",
     *     tags={"Admin - Settings Pages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="key",
     *         in="path",
     *         description="Settings page key",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title_ar", type="string", example="سياسة الخصوصية"),
     *             @OA\Property(property="title_en", type="string", example="Privacy Policy"),
     *             @OA\Property(property="content_ar", type="string", example="محتوى سياسة الخصوصية بالعربية"),
     *             @OA\Property(property="content_en", type="string", example="Privacy policy content in English")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Settings page updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Settings page updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/SettingPageResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Settings page not found"
     *     )
     * )
     */
    public function update(Request $request, string $key): JsonResponse
    {
        $page = SettingPage::findByKey($key);

        if (!$page) {
            return response()->json([
                'message' => 'Settings page not found',
            ], 404);
        }

        $validated = $request->validate([
            'title_ar' => 'sometimes|string|max:255',
            'title_en' => 'sometimes|string|max:255',
            'content_ar' => 'sometimes|string',
            'content_en' => 'sometimes|string',
        ]);

        $page->update($validated);

        return response()->json([
            'message' => 'Settings page updated successfully',
            'data' => new SettingPageResource($page),
        ]);
    }
}
