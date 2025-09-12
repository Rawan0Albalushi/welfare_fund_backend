<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\DonationResource;
use App\Models\Donation;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Campaign; // Added this import for Campaign model

/**
 * @OA\Tag(
 *     name="Public Donations",
 *     description="Public API Endpoints for making donations"
 * )
 */
class DonationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/donations",
     *     summary="Create a new donation",
     *     tags={"Public Donations"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount", "donor_name"},
     *             @OA\Property(property="program_id", type="integer", example=1),
     *             @OA\Property(property="campaign_id", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", format="float", example=100.00),
     *             @OA\Property(property="donor_name", type="string", example="أحمد محمد"),
     *             @OA\Property(property="note", type="string", example="تبرع خيري"),
     *             @OA\Property(property="type", type="string", enum={"quick", "gift"}, example="quick")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Donation created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Donation created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/DonationResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Program or Campaign not found"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'program_id' => 'nullable|integer|exists:programs,id',
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'amount' => 'required|numeric|min:1',
            'donor_name' => 'required|string|max:255',
            'note' => 'nullable|string|max:1000',
            'type' => 'nullable|string|in:quick,gift',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // التحقق من أن إما program_id أو campaign_id موجود
        if (!$request->has('program_id') && !$request->has('campaign_id')) {
            return response()->json([
                'message' => 'Either program_id or campaign_id is required',
            ], 422);
        }

        // التحقق من أن البرنامج أو الحملة نشطة
        if ($request->has('program_id')) {
            $program = Program::active()->find($request->program_id);
            if (!$program) {
                return response()->json([
                    'message' => 'Program not found or not active',
                ], 404);
            }
        }

        if ($request->has('campaign_id')) {
            $campaign = Campaign::active()->find($request->campaign_id);
            if (!$campaign) {
                return response()->json([
                    'message' => 'Campaign not found or not active',
                ], 404);
            }
        }

        // إنشاء التبرع
        $donation = Donation::create([
            'program_id' => $request->program_id,
            'campaign_id' => $request->campaign_id,
            'amount' => $request->amount,
            'donor_name' => $request->donor_name,
            'note' => $request->note,
            'type' => $request->type ?? 'quick',
            'status' => 'pending',
            'user_id' => $request->user()?->id, // ربط التبرع بالمستخدم (اختياري للتبرعات المجهولة)
            'expires_at' => now()->addDays(7), // التبرع صالح لمدة أسبوع
        ]);

        // تحديث المبلغ المجمع في الحملة فقط (برامج الدعم لا تحتاج لحقول التبرع)
        if ($request->has('campaign_id')) {
            $campaign->increment('raised_amount', $request->amount);
        }

        return response()->json([
            'message' => 'Donation created successfully',
            'data' => new DonationResource($donation),
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/donations/with-payment",
     *     summary="Create a donation with payment session",
     *     tags={"Public Donations"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount", "donor_name"},
     *             @OA\Property(property="program_id", type="integer", example=1),
     *             @OA\Property(property="campaign_id", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", format="float", example=100.00),
     *             @OA\Property(property="donor_name", type="string", example="أحمد محمد"),
     *             @OA\Property(property="note", type="string", example="تبرع خيري"),
     *             @OA\Property(property="type", type="string", enum={"quick", "gift"}, example="quick"),
     *             @OA\Property(property="success_url", type="string", example="https://app.com/success"),
     *             @OA\Property(property="cancel_url", type="string", example="https://app.com/cancel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Donation and payment session created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Donation and payment session created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="donation", ref="#/components/schemas/DonationResource"),
     *                 @OA\Property(property="payment_session", type="object",
     *                     @OA\Property(property="session_id", type="string"),
     *                     @OA\Property(property="payment_url", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Program or Campaign not found"
     *     )
     * )
     */
    public function storeWithPayment(Request $request): JsonResponse
    {
        // المستخدم اختياري - يمكن أن يكون مسجل دخول أو مجهول
        // محاولة قراءة المستخدم من الـ token إذا كان موجوداً
        $user = null;
        if ($request->hasHeader('Authorization')) {
            try {
                $user = auth('sanctum')->user();
            } catch (\Exception $e) {
                \Log::warning('Failed to authenticate user from token', [
                    'error' => $e->getMessage(),
                    'auth_header' => $request->header('Authorization')
                ]);
            }
        }
        
        \Log::info('DonationController storeWithPayment user check', [
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'has_auth_header' => $request->hasHeader('Authorization'),
            'auth_header' => $request->header('Authorization') ? 'present' : 'missing',
            'auth_header_value' => $request->header('Authorization'),
            'guard' => auth()->getDefaultDriver(),
            'check_auth' => auth()->check(),
            'current_user' => auth()->user()?->id
        ]);
        $validator = Validator::make($request->all(), [
            'program_id' => 'nullable|integer|exists:programs,id',
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'amount' => 'required|numeric|min:1',
            'donor_name' => 'required|string|max:255',
            'note' => 'nullable|string|max:1000',
            'type' => 'nullable|string|in:quick,gift',
            'success_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // التحقق من أن إما program_id أو campaign_id موجود
        if (!$request->has('program_id') && !$request->has('campaign_id')) {
            return response()->json([
                'message' => 'Either program_id or campaign_id is required',
            ], 422);
        }

        // التحقق من أن البرنامج أو الحملة نشطة
        if ($request->has('program_id')) {
            $program = Program::active()->find($request->program_id);
            if (!$program) {
                return response()->json([
                    'message' => 'Program not found or not active',
                ], 404);
            }
        }

        if ($request->has('campaign_id')) {
            $campaign = Campaign::active()->find($request->campaign_id);
            if (!$campaign) {
                return response()->json([
                    'message' => 'Campaign not found or not active',
                ], 404);
            }
        }

        // إنشاء التبرع
        $donation = Donation::create([
            'program_id' => $request->program_id,
            'campaign_id' => $request->campaign_id,
            'amount' => $request->amount,
            'donor_name' => $request->donor_name,
            'note' => $request->note,
            'type' => $request->type ?? 'quick',
            'status' => 'pending',
            'user_id' => $user?->id, // ربط التبرع بالمستخدم إذا كان مسجل دخول
            'expires_at' => now()->addDays(7), // التبرع صالح لمدة أسبوع
        ]);
        
        \Log::info('DonationController donation created', [
            'donation_id' => $donation->donation_id,
            'user_id' => $donation->user_id,
            'donor_name' => $donation->donor_name,
            'amount' => $donation->amount
        ]);

        // تحديث المبلغ المجمع في الحملة فقط (برامج الدعم لا تحتاج لحقول التبرع)
        if ($request->has('campaign_id')) {
            $campaign->increment('raised_amount', $request->amount);
        }

        // استخدام return_origin لإنشاء URLs ديناميكية
        $returnOrigin = $request->input('return_origin');
        
        \Log::info('DonationController storeWithPayment', [
            'donation_id' => $donation->donation_id,
            'return_origin' => $returnOrigin,
            'all_input' => $request->all()
        ]);
        
        $successUrl = $returnOrigin ? 
            rtrim($returnOrigin, '/') . '/payment/success' : 
            'http://localhost:59860/payment/success';
            
        $cancelUrl = $returnOrigin ? 
            rtrim($returnOrigin, '/') . '/payment/cancel' : 
            'http://localhost:59860/payment/cancel';

        \Log::info('DonationController URLs constructed', [
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'return_origin' => $returnOrigin
        ]);

        // إنشاء جلسة الدفع
        try {
            $thawaniService = app(\App\Services\ThawaniPaymentService::class);
            
            $products = [
                [
                    'name' => 'Donation',
                    'quantity' => 1,
                    'unit_amount' => (int)($request->amount * 1000), // Convert to baisa
                ]
            ];

            $paymentSession = $thawaniService->createSession(
                $donation, // تمرير كائن التبرع
                $products,
                $successUrl,
                $cancelUrl,
                $returnOrigin // تمرير return_origin
            );

            return response()->json([
                'message' => 'Donation and payment session created successfully',
                'data' => [
                    'donation' => $donation,
                    'payment_session' => [
                        'session_id' => $paymentSession['session_id'],
                        'payment_url' => $paymentSession['payment_url'],
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            // Log the error
            \Log::error('Payment session creation failed for donation', [
                'donation_id' => $donation->donation_id,
                'error' => $e->getMessage()
            ]);

            // Return donation created but payment failed
            return response()->json([
                'message' => 'Donation created but payment session failed',
                'data' => [
                    'donation' => $donation,
                    'payment_error' => $e->getMessage()
                ]
            ], 201);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/programs/{id}/donations",
     *     summary="Get donations for a specific program",
     *     tags={"Public Donations"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Program ID",
     *         required=true,
     *         @OA\Schema(type="integer")
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
     *         description="Donations retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Donations retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/DonationResource")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Program not found"
     *     )
     * )
     */
    public function programDonations(int $id, Request $request): JsonResponse
    {
        $program = Program::active()->find($id);
        if (!$program) {
            return response()->json([
                'message' => 'Program not found',
            ], 404);
        }

        $donations = $program->donations()
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'message' => 'Donations retrieved successfully',
            'data' => DonationResource::collection($donations),
            'meta' => [
                'current_page' => $donations->currentPage(),
                'per_page' => $donations->perPage(),
                'total' => $donations->total(),
                'last_page' => $donations->lastPage(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/donations/{id}",
     *     summary="Get a specific donation",
     *     tags={"Public Donations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Donation ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Donation retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Donation retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/DonationResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Donation not found or access denied"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        // البحث عن التبرع مع التحقق من ملكية المستخدم
        $donation = Donation::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere(function ($q) use ($user) {
                          $q->whereNull('user_id')
                            ->whereJsonContains('payload->phone', $user->phone);
                      })
                      ->orWhere(function ($q) use ($user) {
                          $q->whereNull('user_id')
                            ->where('donor_name', $user->name);
                      });
            })
            ->with(['program:id,title', 'giftMeta', 'campaign:id,title'])
            ->first();

        if (!$donation) {
            return response()->json([
                'message' => 'Donation not found or access denied',
            ], 404);
        }

        return response()->json([
            'message' => 'Donation retrieved successfully',
            'data' => new DonationResource($donation),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/donations/quick-amounts",
     *     summary="Get quick donation amounts",
     *     tags={"Public Donations"},
     *     @OA\Response(
     *         response=200,
     *         description="Quick amounts retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Quick amounts retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="amount", type="number", example=50),
     *                 @OA\Property(property="label", type="string", example="50 ريال")
     *             ))
     *         )
     *     )
     * )
     */
    public function quickAmounts(): JsonResponse
    {
        $quickAmounts = [
            ['amount' => 50, 'label' => '50 ريال'],
            ['amount' => 100, 'label' => '100 ريال'],
            ['amount' => 200, 'label' => '200 ريال'],
            ['amount' => 500, 'label' => '500 ريال'],
            ['amount' => 1000, 'label' => '1000 ريال'],
        ];

        return response()->json([
            'message' => 'Quick amounts retrieved successfully',
            'data' => $quickAmounts,
        ]);
    }

    /**
     * Create anonymous donation with payment session (no authentication required)
     */
    public function storeWithPaymentAnonymous(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'program_id' => 'nullable|integer|exists:programs,id',
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'amount' => 'required|numeric|min:1',
            'donor_name' => 'required|string|max:255',
            'note' => 'nullable|string|max:1000',
            'type' => 'nullable|string|in:quick,gift',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // التحقق من أن إما program_id أو campaign_id موجود
        if (!$request->has('program_id') && !$request->has('campaign_id')) {
            return response()->json([
                'message' => 'Either program_id or campaign_id is required',
            ], 422);
        }

        // التحقق من أن البرنامج أو الحملة نشطة
        if ($request->has('program_id')) {
            $program = Program::active()->find($request->program_id);
            if (!$program) {
                return response()->json([
                    'message' => 'Program not found or not active',
                ], 404);
            }
        }

        if ($request->has('campaign_id')) {
            $campaign = Campaign::active()->find($request->campaign_id);
            if (!$campaign) {
                return response()->json([
                    'message' => 'Campaign not found or not active',
                ], 404);
            }
        }

        // إنشاء التبرع (بدون user_id)
        $donation = Donation::create([
            'program_id' => $request->program_id,
            'campaign_id' => $request->campaign_id,
            'amount' => $request->amount,
            'donor_name' => $request->donor_name,
            'note' => $request->note,
            'type' => $request->type ?? 'quick',
            'status' => 'pending',
            'user_id' => null, // تبرع مجهول
            'expires_at' => now()->addDays(7),
        ]);

        // تحديث المبلغ المجمع في الحملة
        if ($request->has('campaign_id')) {
            $campaign->increment('raised_amount', $request->amount);
        }

        // Provide default URLs if not provided
        $successUrl = $request->success_url ?? 'https://studentwelfarefund.com/payment/success';
        $cancelUrl = $request->cancel_url ?? 'https://studentwelfarefund.com/payment/cancel';

        // إنشاء جلسة الدفع
        try {
            $thawaniService = app(\App\Services\ThawaniPaymentService::class);
            
            $result = $thawaniService->createSession(
                $donation, // تمرير كائن التبرع
                [
                    [
                        'name' => $request->donor_name,
                        'quantity' => 1,
                        'unit_amount' => $request->amount * 1000, // تحويل إلى بيسة
                    ]
                ],
                $successUrl,
                $cancelUrl
            );

            $sessionId = $result['session_id'];
            $redirectUrl = $result['payment_url'];

            return response()->json([
                'message' => 'Anonymous donation and payment session created successfully',
                'data' => [
                    'donation' => new DonationResource($donation),
                    'payment_session' => [
                        'session_id' => $sessionId,
                        'payment_url' => $redirectUrl,
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Donation created but payment session failed',
                'data' => [
                    'donation' => new DonationResource($donation),
                    'payment_session' => null,
                    'error' => $e->getMessage(),
                ],
            ], 201);
        }
    }
}
