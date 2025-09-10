<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\SupportProgramResource;
use App\Models\Category;
use App\Models\Program;
use App\Services\DonationsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Public Catalog",
 *     description="Public API Endpoints for browsing categories and programs"
 * )
 */
class CatalogController extends Controller
{
    public function __construct(
        private DonationsService $donationsService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     summary="Get all active categories",
     *     tags={"Public Catalog"},
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categories retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CategoryResource"))
     *         )
     *     )
     * )
     */
    public function categories(): JsonResponse
    {
        $categories = Category::active()->withCount('programs')->get();

        return response()->json([
            'message' => 'Categories retrieved successfully',
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/programs",
     *     summary="Get programs with optional filtering",
     *     tags={"Public Catalog"},
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
     *         description="Programs retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Programs retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ProgramResource")),
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
    public function programs(Request $request): JsonResponse
    {
        $query = Program::active()->with(['category', 'donations' => function ($query) {
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

        $programs = $query->orderBy('created_at', 'desc')
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

    /**
     * @OA\Get(
     *     path="/api/v1/programs/{id}",
     *     summary="Get a specific program with detailed information",
     *     tags={"Public Catalog"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Program ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Program retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Program retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProgramResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Program not found"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $program = Program::active()
            ->with(['category', 'donations' => function ($query) {
                $query->where('status', 'paid');
            }])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Program retrieved successfully',
            'data' => new ProgramResource($program),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/donations/recent",
     *     summary="Get recent donations",
     *     tags={"Public Catalog"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of recent donations to return",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recent donations retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Recent donations retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", description="Database ID of the donation"),
     *                 @OA\Property(property="donation_id", type="string", description="Unique donation identifier"),
     *                 @OA\Property(property="donor_name", type="string"),
     *                 @OA\Property(property="amount", type="number"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="program_title", type="string"),
     *                 @OA\Property(property="campaign_title", type="string"),
     *                 @OA\Property(property="paid_at", type="string", format="date-time"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             ))
     *         )
     *     )
     * )
     */
    public function recentDonations(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $donations = $this->donationsService->getRecentDonations($limit);

        return response()->json([
            'message' => 'Recent donations retrieved successfully',
            'data' => $donations,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/programs/support",
     *     summary="Get support programs for student registration",
     *     tags={"Public Catalog"},
     *     @OA\Response(
     *         response=200,
     *         description="Support programs retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Support programs retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="برنامج فرص التعليم العالي"),
     *                 @OA\Property(property="description", type="string", example="برنامج لدعم الطلاب في الحصول على فرص التعليم العالي والمنح الدراسية"),
     *                 @OA\Property(property="status", type="string", example="active")
     *             ))
     *         )
     *     )
     * )
     */
    public function supportPrograms()
    {
        // البحث عن فئة برامج الدعم الطلابي
        $supportCategory = Category::where('name', 'برامج الدعم الطلابي')->first();
        
        if (!$supportCategory) {
            return response()->json([
                'message' => 'Support category not found',
                'data' => []
            ], 404);
        }

        $programs = Program::where('category_id', $supportCategory->id)
            ->where('status', 'active')
            ->with('category')
            ->orderBy('title')
            ->get();

        return response()->json([
            'message' => 'Support programs retrieved successfully',
            'data' => SupportProgramResource::collection($programs)
        ]);
    }
}
