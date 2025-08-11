<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Http\Requests\Students\CreateApplicationRequest;
use App\Http\Resources\StudentApplicationResource;
use App\Models\StudentApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Student Applications",
 *     description="API Endpoints for student application management"
 * )
 */
class ApplicationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/students/applications",
     *     summary="Create a new student application",
     *     tags={"Student Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"program_id","personal","academic","financial"},
     *             @OA\Property(property="program_id", type="integer", example=1),
     *             @OA\Property(property="personal", type="object",
     *                 @OA\Property(property="full_name", type="string", example="Ahmed Mohammed Ali"),
     *                 @OA\Property(property="national_id", type="string", example="1234567890"),
     *                 @OA\Property(property="date_of_birth", type="string", format="date", example="2000-05-15"),
     *                 @OA\Property(property="gender", type="string", enum={"male", "female"}, example="male"),
     *                 @OA\Property(property="address", type="string", example="Riyadh, Saudi Arabia"),
     *                 @OA\Property(property="phone", type="string", example="+966501234567"),
     *                 @OA\Property(property="email", type="string", format="email", example="ahmed@example.com")
     *             ),
     *             @OA\Property(property="academic", type="object",
     *                 @OA\Property(property="university", type="string", example="King Saud University"),
     *                 @OA\Property(property="faculty", type="string", example="Computer Science"),
     *                 @OA\Property(property="department", type="string", example="Software Engineering"),
     *                 @OA\Property(property="student_id", type="string", example="CS123456"),
     *                 @OA\Property(property="gpa", type="number", format="float", example=3.8),
     *                 @OA\Property(property="academic_year", type="integer", example=3)
     *             ),
     *             @OA\Property(property="financial", type="object",
     *                 @OA\Property(property="family_income", type="number", example=5000.00),
     *                 @OA\Property(property="family_size", type="integer", example=6),
     *                 @OA\Property(property="father_occupation", type="string", example="Teacher"),
     *                 @OA\Property(property="mother_occupation", type="string", example="Housewife"),
     *                 @OA\Property(property="monthly_expenses", type="number", example=3000.00),
     *                 @OA\Property(property="other_sources", type="string", example="Part-time job")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Application created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Application created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StudentApplicationResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(CreateApplicationRequest $request): JsonResponse
    {
        $user = $request->user();

        $application = StudentApplication::create([
            'user_id' => $user->id,
            'program_id' => $request->program_id,
            'personal_json' => $request->personal,
            'academic_json' => $request->academic,
            'financial_json' => $request->financial,
            'status' => 'under_review',
        ]);

        return response()->json([
            'message' => 'Application created successfully',
            'data' => new StudentApplicationResource($application->load(['program:id,title', 'user:id,name'])),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/students/applications",
     *     summary="Get current user's applications",
     *     tags={"Student Applications"},
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
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"under_review", "accepted", "rejected"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Applications retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Applications retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/StudentApplicationResource")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = StudentApplication::where('user_id', $user->id)
            ->with(['program:id,title']);

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

    /**
     * @OA\Get(
     *     path="/api/v1/students/applications/{id}",
     *     summary="Get a specific application",
     *     tags={"Student Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Application ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Application retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StudentApplicationResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Application not found"
     *     )
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $application = StudentApplication::where('user_id', $user->id)
            ->with(['program:id,title', 'user:id,name'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Application retrieved successfully',
            'data' => new StudentApplicationResource($application),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/students/applications/{id}/documents",
     *     summary="Upload documents for an application",
     *     tags={"Student Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Application ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="documents[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Upload supporting documents (PDF, JPG, PNG)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Documents uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Documents uploaded successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="uploaded_files", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Application not found"
     *     )
     * )
     */
    public function uploadDocuments(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $application = StudentApplication::where('user_id', $user->id)
            ->findOrFail($id);

        $request->validate([
            'documents.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
        ]);

        $uploadedFiles = [];
        
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store("students/{$application->id}/documents", 'public');
                $uploadedFiles[] = $path;
            }
        }

        return response()->json([
            'message' => 'Documents uploaded successfully',
            'data' => [
                'uploaded_files' => $uploadedFiles,
            ],
        ]);
    }
}
