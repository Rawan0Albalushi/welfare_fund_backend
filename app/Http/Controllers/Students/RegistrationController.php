<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Http\Requests\Students\CreateApplicationRequest;
use App\Http\Resources\StudentRegistrationResource;
use App\Models\StudentRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Student Registration",
 *     description="API Endpoints for student registration management"
 * )
 */
class RegistrationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/students/registration",
     *     summary="Create a new student registration",
     *     tags={"Student Registration"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"program_id","personal","academic","financial"},
     *                 @OA\Property(property="program_id", type="integer", example=1),
     *                 @OA\Property(property="personal", type="object",
     *                     @OA\Property(property="full_name", type="string", example="Ahmed Mohammed Ali"),
     *                     @OA\Property(property="student_id", type="string", example="CS123456"),
     *                     @OA\Property(property="email", type="string", format="email", example="ahmed@example.com"),
     *                     @OA\Property(property="phone", type="string", example="+966501234567"),
     *                     @OA\Property(property="gender", type="string", enum={"male", "female"}, example="male")
     *                 ),
     *                 @OA\Property(property="academic", type="object",
     *                     @OA\Property(property="university", type="string", example="King Saud University"),
     *                     @OA\Property(property="college", type="string", example="Computer Science"),
     *                     @OA\Property(property="major", type="string", example="Software Engineering"),
     *                     @OA\Property(property="program", type="string", example="Bachelor of Computer Science"),
     *                     @OA\Property(property="academic_year", type="integer", example=3),
     *                     @OA\Property(property="gpa", type="number", format="float", example=3.8)
     *                 ),
     *                 @OA\Property(property="financial", type="object",
     *                     @OA\Property(property="income_level", type="string", enum={"low", "medium", "high"}, example="medium"),
     *                     @OA\Property(property="family_size", type="string", enum={"1-3", "4-6", "7-9", "10+"}, example="4-6")
     *                 ),
     *                 @OA\Property(property="id_card_image", type="string", format="binary", description="ID card image (JPG, PNG, PDF)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registration created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Registration created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StudentRegistrationResource")
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

        // Handle file upload if provided
        $idCardPath = null;
        if ($request->hasFile('id_card_image')) {
            $idCardPath = $request->file('id_card_image')->store('students/id_cards', 'public');
        }

        $application = StudentRegistration::create([
            'user_id' => $user->id,
            'program_id' => $request->program_id,
            'personal_json' => $request->personal,
            'academic_json' => $request->academic,
            'financial_json' => $request->financial,
            'status' => 'under_review',
            'id_card_image' => $idCardPath,
        ]);

        return response()->json([
            'message' => 'Registration created successfully',
            'data' => new StudentRegistrationResource($application->load(['program:id,title_ar,title_en', 'user:id,name'])),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/students/registration",
     *     summary="Get current user's registrations",
     *     tags={"Student Registration"},
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
     *         description="Registrations retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Registrations retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/StudentRegistrationResource")),
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
        
        $query = StudentRegistration::where('user_id', $user->id)
            ->with(['program:id,title_ar,title_en']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'message' => 'Registrations retrieved successfully',
            'data' => StudentRegistrationResource::collection($applications),
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
     *     path="/api/v1/students/registration/my-registration",
     *     summary="Get current user's latest registration status",
     *     tags={"Student Registration"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Registration status retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Registration status retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="registration_id", type="string", example="REG_e7e01d2b-960c-43c1-b0c5-c2f8b5d0d8f8"),
     *                 @OA\Property(property="status", type="string", enum={"under_review", "accepted", "rejected"}, example="under_review"),
     *                 @OA\Property(property="rejection_reason", type="string", nullable=true, example="Incomplete documentation"),
     *                 @OA\Property(property="personal", type="object",
     *                     @OA\Property(property="full_name", type="string", example="اسم الطالب"),
     *                     @OA\Property(property="student_id", type="string", example="رقم الطالب"),
     *                     @OA\Property(property="email", type="string", example="البريد الإلكتروني"),
     *                     @OA\Property(property="phone", type="string", example="رقم الهاتف"),
     *                     @OA\Property(property="gender", type="string", enum={"male", "female"}, example="male")
     *                 ),
     *                 @OA\Property(property="academic", type="object",
     *                     @OA\Property(property="university", type="string", example="اسم الجامعة"),
     *                     @OA\Property(property="college", type="string", example="اسم الكلية"),
     *                     @OA\Property(property="major", type="string", example="التخصص"),
     *                     @OA\Property(property="program", type="string", example="اسم البرنامج"),
     *                     @OA\Property(property="academic_year", type="integer", example=3),
     *                     @OA\Property(property="gpa", type="number", format="float", example=3.8)
     *                 ),
     *                 @OA\Property(property="financial", type="object",
     *                     @OA\Property(property="income_level", type="string", enum={"low", "medium", "high"}, example="low"),
     *                     @OA\Property(property="family_size", type="integer", example=6)
     *                 ),
     *                 @OA\Property(property="program", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Emergency Financial Aid")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-12T19:33:12.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-12T19:33:12.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No registration found for current user",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No registration found"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function myRegistration(Request $request): JsonResponse
    {
        $user = $request->user();
        
		$registration = StudentRegistration::where('user_id', $user->id)
			->with(['program:id,title,title_ar,title_en'])
            ->latest()
            ->first();

        if (!$registration) {
            return response()->json([
                'message' => 'No registration found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Registration status retrieved successfully',
            'data' => [
                'id' => $registration->id,
                'registration_id' => $registration->registration_id,
                'status' => $registration->status,
                'rejection_reason' => $registration->reject_reason,
                'personal' => $registration->personal_json,
                'academic' => $registration->academic_json,
                'financial' => $registration->financial_json,
                'program' => $registration->program ? [
                    'id' => $registration->program->id,
                    'title' => $registration->program->title,
                ] : null,
                'created_at' => $registration->created_at?->toISOString(),
                'updated_at' => $registration->updated_at?->toISOString(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/students/registration/{id}",
     *     summary="Get a specific registration",
     *     tags={"Student Registration"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Registration ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registration retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Registration retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StudentRegistrationResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Registration not found"
     *     )
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $application = StudentRegistration::where('user_id', $user->id)
            ->with(['program:id,title_ar,title_en', 'user:id,name'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Registration retrieved successfully',
            'data' => new StudentRegistrationResource($application),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/students/registration/{id}/documents",
     *     summary="Upload documents for a registration",
     *     tags={"Student Registration"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Registration ID",
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
     *         description="Registration not found"
     *     )
     * )
     */
    public function uploadDocuments(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $application = StudentRegistration::where('user_id', $user->id)
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

    /**
     * @OA\Put(
     *     path="/api/v1/students/registration/{id}",
     *     summary="Update a rejected registration",
     *     tags={"Student Registration"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Registration ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"program_id","personal","academic","financial"},
     *                 @OA\Property(property="program_id", type="integer", example=1),
     *                 @OA\Property(property="personal", type="object",
     *                     @OA\Property(property="full_name", type="string", example="Ahmed Mohammed Ali"),
     *                     @OA\Property(property="student_id", type="string", example="CS123456"),
     *                     @OA\Property(property="email", type="string", format="email", example="ahmed@example.com"),
     *                     @OA\Property(property="phone", type="string", example="+966501234567"),
     *                     @OA\Property(property="gender", type="string", enum={"male", "female"}, example="male")
     *                 ),
     *                 @OA\Property(property="academic", type="object",
     *                     @OA\Property(property="university", type="string", example="King Saud University"),
     *                     @OA\Property(property="college", type="string", example="Computer Science"),
     *                     @OA\Property(property="major", type="string", example="Software Engineering"),
     *                     @OA\Property(property="program", type="string", example="Bachelor of Computer Science"),
     *                     @OA\Property(property="academic_year", type="integer", example=3),
     *                     @OA\Property(property="gpa", type="number", format="float", example=3.8)
     *                 ),
     *                 @OA\Property(property="financial", type="object",
     *                     @OA\Property(property="income_level", type="string", enum={"low", "medium", "high"}, example="medium"),
     *                     @OA\Property(property="family_size", type="string", enum={"1-3", "4-6", "7-9", "10+"}, example="4-6")
     *                 ),
     *                 @OA\Property(property="id_card_image", type="string", format="binary", description="ID card image (JPG, PNG, PDF)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registration updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Registration updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/StudentRegistrationResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Registration not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Registration cannot be updated (not rejected)"
     *     )
     * )
     */
    public function update(CreateApplicationRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $registration = StudentRegistration::where('user_id', $user->id)
            ->findOrFail($id);

        // Only allow updates for rejected registrations
        if ($registration->status !== 'rejected') {
            return response()->json([
                'message' => 'Registration cannot be updated. Only rejected registrations can be updated.',
            ], 403);
        }

        // Handle file upload if provided
        $idCardPath = $registration->id_card_image; // Keep existing image
        if ($request->hasFile('id_card_image')) {
            $idCardPath = $request->file('id_card_image')->store('students/id_cards', 'public');
        }

        $registration->update([
            'program_id' => $request->program_id,
            'personal_json' => $request->personal,
            'academic_json' => $request->academic,
            'financial_json' => $request->financial,
            'status' => 'under_review', // Reset to under review
            'reject_reason' => null, // Clear rejection reason
            'id_card_image' => $idCardPath,
        ]);

        return response()->json([
            'message' => 'Registration updated successfully',
            'data' => new StudentRegistrationResource($registration->load(['program:id,title_ar,title_en', 'user:id,name'])),
        ]);
    }
}
