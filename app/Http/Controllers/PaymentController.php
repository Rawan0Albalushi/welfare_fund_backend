<?php

namespace App\Http\Controllers;

use App\Services\ThawaniService;
use App\Models\Donation;
use App\Models\Program;
use App\Models\Campaign;
use App\Helpers\PaymentSecurityHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private ThawaniService $thawaniService;

    public function __construct(ThawaniService $thawaniService)
    {
        $this->thawaniService = $thawaniService;
    }

    /**
     * GET /api/v1/payments?session_id=...
     * استرجاع معلومات الدفع بناءً على session_id
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Session ID is required',
                'errors' => $validator->errors()
            ], 400);
        }

        $sessionId = $request->query('session_id');
        $donation = Donation::where('payment_session_id', $sessionId)->first();

        if (!$donation) {
            return response()->json([
                'success' => false,
                'message' => 'Donation not found for this session',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment information retrieved successfully',
            'data' => [
                'donation' => [
                    'id'         => $donation->donation_id,
                    'amount'     => $donation->amount,
                    'donor_name' => $donation->donor_name,
                    'status'     => $donation->status,
                    'created_at' => $donation->created_at,
                ],
                'session_id'  => $sessionId,
                'payment_url' => $donation->payment_url,
            ],
        ]);
    }

    /**
     * POST /api/v1/payments/create
     * إنشاء جلسة دفع جديدة (الطريقة الموحدة)
     *
     * Body example:
     * {
     *   "products": [{"name":"Donation","quantity":1,"unit_amount":1000}],
     *   "program_id": 1, // or "campaign_id": 5
     *   "donor_name": "اسم المتبرع",
     *   "note": "ملاحظة",
     *   "type": "quick", // or "gift"
     *   "return_origin": "https://example.com"
     * }
     */
    public function createPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'products'               => 'required|array|min:1',
            'products.*.name'        => 'required|string|max:255',
            'products.*.quantity'    => 'required|integer|min:1',
            // حد أدنى: 1 ريال (1000 بيسة)، حد أقصى: 100,000 ريال (100,000,000 بيسة)
            'products.*.unit_amount' => 'required|integer|min:1000|max:100000000',
            'return_origin'          => 'required|string|url',

            // Donation related
            'program_id'             => 'nullable|integer|exists:programs,id',
            'campaign_id'            => 'nullable|integer|exists:campaigns,id',
            'donor_name'             => 'nullable|string|max:255',
            'note'                   => 'nullable|string|max:1000',
            'type'                   => 'nullable|string|in:quick,gift',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        // التحقق من return_origin باستخدام قائمة بيضاء
        try {
            $returnOrigin = PaymentSecurityHelper::validateReturnOrigin($request->input('return_origin'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid return_origin: ' . $e->getMessage()
            ], 400);
        }

        // Either program_id or campaign_id must exist
        if (!$request->has('program_id') && !$request->has('campaign_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Either program_id or campaign_id is required',
            ], 422);
        }

        // Validate active program/campaign
        if ($request->has('program_id')) {
            $program = Program::active()->find($request->program_id);
            if (!$program) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program not found or not active',
                ], 404);
            }
        }

        if ($request->has('campaign_id')) {
            $campaign = Campaign::active()->find($request->campaign_id);
            if (!$campaign) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campaign not found or not active',
                ], 404);
            }
        }

        try {
            DB::beginTransaction();

            // إنشاء التبرع - تحويل المبلغ من بيسة إلى ريال (على أول منتج)
            $donation = Donation::create([
                'program_id'   => $request->program_id,
                'campaign_id'  => $request->campaign_id,
                'amount'       => $request->products[0]['unit_amount'] / 1000, // بيسة -> ريال
                'donor_name'   => $request->donor_name ?? 'مجهول',
                'note'         => $request->note,
                'type'         => $request->type ?? 'quick',
                'status'       => 'pending',
                'user_id'      => $request->user()?->id, // ربط التبرع بالمستخدم إذا كان مسجل دخول
                'expires_at'   => now()->addDays(7),
            ]);

            // إنشاء جلسة الدفع على ثواني
            $result = $this->thawaniService->createSession(
                $donation,
                $request->products,
                $returnOrigin
            );

            // استخراج البيانات من النتيجة
            $sessionId   = $result['session_id'];
            $redirectUrl = $result['payment_url'];

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'OK',
                'data'        => [
                    'donation'         => $donation,
                    'payment_session'  => [
                        'session_id'   => $sessionId,
                        'redirect_url' => $redirectUrl,
                    ],
                ],
                'payment_url' => $redirectUrl,
                'session_id'  => $sessionId,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Thawani payment session creation failed', [
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
                'products'  => $request->products
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment session: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/v1/payments/create-with-donation
     * إنشاء جلسة دفع لتبرع موجود مسبقاً
     */
    public function createWithDonation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'donation_id' => 'required|string',
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|string',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_amount' => 'required|integer|min:1000|max:100000000',
            'return_origin' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // البحث عن التبرع
            $donation = Donation::where('donation_id', $request->donation_id)->first();
            
            if (!$donation) {
                return response()->json([
                    'success' => false,
                    'message' => 'التبرع غير موجود'
                ], 404);
            }

            // التحقق من أن التبرع في حالة pending
            if ($donation->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'التبرع ليس في حالة انتظار الدفع'
                ], 400);
            }

            // التحقق من انتهاء الجلسة
            if ($donation->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'انتهت صلاحية جلسة الدفع'
                ], 400);
            }

            DB::beginTransaction();

            // التحقق من return_origin باستخدام قائمة بيضاء
            $returnOrigin = null;
            try {
                $inputOrigin = $request->input('return_origin');
                if ($inputOrigin) {
                    $returnOrigin = PaymentSecurityHelper::validateReturnOrigin($inputOrigin);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid return_origin: ' . $e->getMessage()
                ], 400);
            }

            // تسجيل محدود (بدون بيانات حساسة)
            Log::info('Payment creation request', [
                'donation_id' => $request->donation_id,
                'return_origin_sanitized' => $returnOrigin ? PaymentSecurityHelper::sanitizeUrlForLogging($returnOrigin) : null,
            ]);

            // إنشاء جلسة الدفع مع تمرير return_origin
            $result = $this->thawaniService->createSession(
                $donation,
                $request->products,
                $returnOrigin
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء جلسة الدفع بنجاح',
                'data' => [
                    'checkout_url' => $result['payment_url'],
                    'session_id' => $result['session_id']
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Payment session creation failed', [
                'donation_id' => $request->donation_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في إنشاء جلسة الدفع: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/v1/payments/confirm
     * تأكيد حالة الدفع
     */
    public function confirm(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required_without:donation_id|string',
            'donation_id' => 'required_without:session_id|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'يجب توفير session_id أو donation_id',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $sessionId = $request->session_id;
            
            // إذا تم تمرير donation_id بدلاً من session_id
            if (!$sessionId && $request->donation_id) {
                $donation = Donation::where('donation_id', $request->donation_id)->first();
                
                if (!$donation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'التبرع غير موجود'
                    ], 404);
                }
                
                if (!$donation->payment_session_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'لا توجد جلسة دفع مرتبطة بهذا التبرع'
                    ], 400);
                }
                
                $sessionId = $donation->payment_session_id;
            }

            // تأكيد الدفع
            $result = $this->thawaniService->confirmPayment($sessionId);

            return response()->json([
                'success' => true,
                'message' => 'تم تأكيد حالة الدفع بنجاح',
                'data' => [
                    'status' => $result['status'],
                    'donation_id' => $result['donation_id'],
                    'paid_amount' => $result['paid_amount'],
                    'paid_at' => $result['paid_at'],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Payment confirmation failed', [
                'session_id' => $request->session_id,
                'donation_id' => $request->donation_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في تأكيد الدفع: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/v1/payments/status/{sessionId}
     * يستعلم من ثواني ويثبّت الحالة في DB (paid/cancelled)
     */
    public function getPaymentStatus(string $sessionId): JsonResponse
    {
        try {
            $session = $this->thawaniService->getSessionDetails($sessionId);
            $paymentStatus = $session['payment_status'] ?? ($session['data']['payment_status'] ?? 'unknown');

            $donation = Donation::where('payment_session_id', $sessionId)->first();

            if ($donation) {
                // التحقق من انتهاء الجلسة
                if ($donation->isExpired() && $paymentStatus !== 'paid') {
                    if ($donation->status !== 'expired') {
                        $donation->update(['status' => 'expired']);
                    }
                    return response()->json([
                        'success'        => true,
                        'payment_status' => 'expired',
                        'session_id'     => $sessionId,
                        'message'        => 'Payment session has expired',
                    ]);
                }

                if ($paymentStatus === 'paid' && $donation->status !== 'paid') {
                    DB::transaction(function () use ($donation, $session) {
                        // إعادة جلب التبرع مع lock لتجنب Race Conditions
                        $donation = Donation::where('payment_session_id', $donation->payment_session_id)
                            ->lockForUpdate()
                            ->first();
                        
                        // التحقق مرة أخرى بعد lock
                        if ($donation->status === 'paid') {
                            return;
                        }
                        
                        // استخدام المبلغ الفعلي من session
                        $paidAmount = $this->extractPaidAmount($donation, $session);
                        
                        $donation->update([
                            'status'      => 'paid',
                            'paid_amount' => $paidAmount,
                        ]);

                        // تحديث مبلغ الحملة مع lock
                        if ($donation->campaign_id) {
                            Campaign::where('id', $donation->campaign_id)
                                ->lockForUpdate()
                                ->increment('raised_amount', $paidAmount);
                        }
                    });
                } elseif (in_array($paymentStatus, ['cancelled', 'canceled', 'failed'])) {
                    if ($donation->status !== 'cancelled') {
                        $donation->update(['status' => 'cancelled']);
                    }
                }
            }

            return response()->json([
                'success'        => true,
                'payment_status' => $paymentStatus,
                'session_id'     => $sessionId,
                'raw_response'   => $session,
            ]);
        } catch (\Exception $e) {
            Log::error('Thawani payment status retrieval failed', [
                'error'      => $e->getMessage(),
                'session_id' => $sessionId,
                'trace'      => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bridge endpoint for successful payment redirects
     */
    public function bridgeSuccess(Request $request)
    {
        $donationId = $request->query('donation_id');
        $originInput = $request->query('origin');
        
        // التحقق من origin باستخدام قائمة بيضاء
        $origin = null;
        try {
            if ($originInput) {
                $origin = PaymentSecurityHelper::validateReturnOrigin($originInput);
            }
        } catch (\Exception $e) {
            Log::warning('Bridge Success: Invalid origin', [
                'donation_id' => $donationId,
                'origin_input' => $originInput ? PaymentSecurityHelper::sanitizeUrlForLogging($originInput) : null,
                'error' => $e->getMessage()
            ]);
            // استخدام URL آمن افتراضي
            $defaultOrigin = env('FRONTEND_ORIGIN', 'http://localhost:3000');
            $origin = $defaultOrigin . '/payment/success';
        }

        // Fallback آمن
        if (!$origin) {
            $defaultOrigin = env('FRONTEND_ORIGIN', 'http://localhost:3000');
            $origin = $defaultOrigin . '/payment/success';
        }
        
        Log::info('Bridge Success called', [
            'donation_id' => $donationId,
            'origin_sanitized' => PaymentSecurityHelper::sanitizeUrlForLogging($origin),
        ]);
        
        if (!$donationId) {
            $errorUrl = rtrim($origin, '/') . '?status=error&message=' . urlencode('Donation ID is required');
            return redirect()->away($errorUrl);
        }

        $donation = Donation::where('donation_id', $donationId)->first();
        
        if (!$donation) {
            $errorUrl = rtrim($origin, '/') . '?status=error&message=' . urlencode('Donation not found');
            return redirect()->away($errorUrl);
        }

        // تأكيد الحالة من ثواني (idempotent) مع معالجة Race Conditions
        if ($donation->status !== 'paid' && $donation->payment_session_id) {
            try {
                DB::beginTransaction();
                
                // إعادة جلب التبرع مع lock لتجنب Race Conditions
                $donation = Donation::where('donation_id', $donationId)
                    ->lockForUpdate()
                    ->first();
                
                // التحقق مرة أخرى بعد lock
                if ($donation->status === 'paid') {
                    DB::commit();
                } else {
                    $sessionDetails = $this->thawaniService->getSessionDetails($donation->payment_session_id);
                    $paymentStatus = $sessionDetails['payment_status'] ?? null;
                    
                    if ($paymentStatus === 'paid') {
                        $paidAmount = $this->extractPaidAmount($donation, $sessionDetails);
                        
                        $donation->update([
                            'status' => 'paid',
                            'paid_amount' => $paidAmount,
                            'paid_at' => isset($sessionDetails['paid_at']) 
                                ? \Carbon\Carbon::parse($sessionDetails['paid_at'])->setTimezone(config('app.timezone'))
                                : now(),
                            'payload' => $sessionDetails,
                        ]);

                        // تحديث مبلغ الحملة باستخدام lock
                        if ($donation->campaign_id) {
                            Campaign::where('id', $donation->campaign_id)
                                ->lockForUpdate()
                                ->increment('raised_amount', $paidAmount);
                        }
                        
                        DB::commit();
                    } elseif ($paymentStatus === 'canceled') {
                        $donation->update(['status' => 'canceled']);
                        DB::commit();
                    } else {
                        DB::rollBack();
                    }
                }
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to confirm payment status in bridge', [
                    'donation_id' => $donationId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // origin الآن يحتوي على URL كامل للواجهة الأمامية
        // تمرير تفاصيل التبرع للواجهة الأمامية
        $queryParams = [
            'donation_id' => $donationId,
            'amount' => $donation->amount,
            'donor_name' => $donation->donor_name,
            'status' => $donation->status,
            'paid_amount' => $donation->paid_amount ?? $donation->amount,
        ];
        
        $redirectUrl = rtrim($origin, '/') . '?' . http_build_query($queryParams);
        
        Log::info('Bridge Success redirecting', [
            'redirect_url_sanitized' => PaymentSecurityHelper::sanitizeUrlForLogging($redirectUrl),
            'donation_id' => $donationId,
            'amount' => $donation->amount,
            'paid_amount' => $donation->paid_amount ?? $donation->amount,
        ]);
        
        return redirect()->away($redirectUrl);
    }

    /**
     * Bridge endpoint for canceled payment redirects
     */
    public function bridgeCancel(Request $request)
    {
        $donationId = $request->query('donation_id');
        $originInput = $request->query('origin');
        
        // التحقق من origin باستخدام قائمة بيضاء
        $origin = null;
        try {
            if ($originInput) {
                $origin = PaymentSecurityHelper::validateReturnOrigin($originInput);
            }
        } catch (\Exception $e) {
            Log::warning('Bridge Cancel: Invalid origin', [
                'donation_id' => $donationId,
                'origin_input' => $originInput ? PaymentSecurityHelper::sanitizeUrlForLogging($originInput) : null,
                'error' => $e->getMessage()
            ]);
            // استخدام URL آمن افتراضي
            $defaultOrigin = env('FRONTEND_ORIGIN', 'http://localhost:3000');
            $origin = $defaultOrigin . '/payment/cancel';
        }

        // Fallback آمن
        if (!$origin) {
            $defaultOrigin = env('FRONTEND_ORIGIN', 'http://localhost:3000');
            $origin = $defaultOrigin . '/payment/cancel';
        }
        
        Log::info('Bridge Cancel called', [
            'donation_id' => $donationId,
            'origin_sanitized' => PaymentSecurityHelper::sanitizeUrlForLogging($origin),
        ]);
        
        // البحث عن التبرع لتمرير تفاصيله
        $donation = null;
        if ($donationId) {
            $donation = Donation::where('donation_id', $donationId)->first();
        }
        
        // origin الآن يحتوي على URL كامل للواجهة الأمامية
        // تمرير تفاصيل التبرع للواجهة الأمامية
        $queryParams = [
            'donation_id' => $donationId,
            'status' => 'canceled',
        ];
        
        if ($donation) {
            $queryParams['amount'] = $donation->amount;
            $queryParams['donor_name'] = $donation->donor_name;
        }
        
        $redirectUrl = rtrim($origin, '/') . '?' . http_build_query($queryParams);
        
        Log::info('Bridge Cancel redirecting', [
            'redirect_url_sanitized' => PaymentSecurityHelper::sanitizeUrlForLogging($redirectUrl),
            'donation_id' => $donationId,
            'amount' => $donation?->amount,
        ]);
        
        return redirect()->away($redirectUrl);
    }

    /**
     * GET /api/v1/payments/mobile/success
     * نجاح الدفع للموبايل
     */
    public function mobileSuccess(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'donation_id' => 'required|string',
            'session_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'donation_id is required'
            ], 400);
        }

        $donationId = $request->query('donation_id');
        $sessionId = $request->query('session_id');

        // جلب التبرع مع العلاقات
        $donation = Donation::with('campaign')->where('donation_id', $donationId)->first();

        if (!$donation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Donation not found'
            ], 404);
        }

        // استخدام session_id من query parameters أو من التبرع
        $actualSessionId = $sessionId ?? $donation->payment_session_id;

        // إذا كان لدينا session_id، تأكد من حالة الدفع من ثواني وتحديث قاعدة البيانات
        $paymentStatusFromThawani = null;
        if ($actualSessionId) {
            try {
                // التحقق من حالة الدفع من ثواني (idempotent)
                if ($donation->status !== 'paid' && $donation->payment_session_id) {
                    // التحقق من انتهاء الجلسة
                    if ($donation->isExpired()) {
                        if ($donation->status !== 'expired') {
                            $donation->update(['status' => 'expired']);
                        }
                        return response()->json([
                            'status' => 'expired',
                            'donation_status' => 'expired',
                            'donation_id' => $donation->donation_id,
                            'message' => 'Payment session has expired'
                        ]);
                    }

                    $sessionDetails = $this->thawaniService->getSessionDetails($donation->payment_session_id);
                    $paymentStatusFromThawani = $sessionDetails['payment_status'] ?? null;
                    
                    Log::info('Mobile success: Payment status check', [
                        'donation_id' => $donationId,
                        'session_id' => $actualSessionId,
                        'payment_status_from_thawani' => $paymentStatusFromThawani,
                        'donation_status' => $donation->status,
                    ]);
                    
                    if ($paymentStatusFromThawani === 'paid') {
                        $paidAmount = $this->extractPaidAmount($donation, $sessionDetails);
                        
                        DB::beginTransaction();
                        
                        // إعادة جلب التبرع مع lock لتجنب Race Conditions
                        $donation = Donation::where('donation_id', $donationId)
                            ->lockForUpdate()
                            ->first();
                        
                        // التحقق مرة أخرى بعد lock
                        if ($donation->status === 'paid') {
                            DB::commit();
                            Log::info('Mobile success: Donation already paid (after lock)', [
                                'donation_id' => $donationId,
                            ]);
                        } else {
                            $donation->update([
                                'status' => 'paid',
                                'paid_amount' => $paidAmount,
                                'paid_at' => isset($sessionDetails['paid_at']) 
                                    ? \Carbon\Carbon::parse($sessionDetails['paid_at'])->setTimezone(config('app.timezone'))
                                    : now(),
                                'payload' => $sessionDetails,
                            ]);

                            // تحديث مبلغ الحملة مع lock
                            if ($donation->campaign_id) {
                                Campaign::where('id', $donation->campaign_id)
                                    ->lockForUpdate()
                                    ->increment('raised_amount', $paidAmount);
                            }
                            
                            DB::commit();
                            
                            Log::info('Mobile success: Payment confirmed and donation updated', [
                                'donation_id' => $donationId,
                                'session_id' => $actualSessionId,
                                'paid_amount' => $paidAmount,
                            ]);
                        }
                    } elseif ($paymentStatusFromThawani === 'canceled' || $paymentStatusFromThawani === 'cancel') {
                        $donation->update(['status' => 'cancelled']);
                        Log::info('Mobile success: Payment was canceled', [
                            'donation_id' => $donationId,
                            'session_id' => $actualSessionId,
                            'payment_status' => $paymentStatusFromThawani
                        ]);
                    } elseif ($paymentStatusFromThawani === 'unpaid' || $paymentStatusFromThawani === null) {
                        Log::warning('Mobile success: Payment status is unpaid or null', [
                            'donation_id' => $donationId,
                            'session_id' => $actualSessionId,
                            'payment_status_from_thawani' => $paymentStatusFromThawani,
                            'donation_status' => $donation->status,
                            'message' => 'Payment has not been confirmed yet. This may be a timing issue or the payment was not completed.'
                        ]);
                    } else {
                        Log::warning('Mobile success: Unexpected payment status', [
                            'donation_id' => $donationId,
                            'session_id' => $actualSessionId,
                            'payment_status_from_thawani' => $paymentStatusFromThawani,
                            'donation_status' => $donation->status,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Mobile success: Failed to confirm payment status', [
                    'donation_id' => $donationId,
                    'session_id' => $actualSessionId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // نستمر في إرجاع البيانات حتى لو فشل التحقق
            }
        }

        // إعادة جلب التبرع للحصول على البيانات المحدثة
        $donation->refresh();

        // جلب معلومات الحملة إذا كانت موجودة
        $campaignTitle = null;
        if ($donation->campaign_id && $donation->campaign) {
            $campaignTitle = $donation->campaign->title;
        }

        // إرجاع حالة التبرع الفعلية بدلاً من "success" دائماً
        return response()->json([
            'status' => $donation->status === 'paid' ? 'success' : 'pending',
            'donation_status' => $donation->status,
            'donation_id' => $donation->donation_id,
            'session_id' => $actualSessionId,
            'amount' => (float) $donation->amount,
            'campaign_title' => $campaignTitle,
            'payment_status_from_thawani' => $paymentStatusFromThawani,
            'message' => $donation->status === 'paid' 
                ? 'تم التبرع بنجاح' 
                : ($paymentStatusFromThawani === 'unpaid' 
                    ? 'قيد انتظار تأكيد الدفع' 
                    : 'حالة التبرع: ' . $donation->status)
        ]);
    }

    /**
     * GET /api/v1/payments/success
     * صفحة عرض فقط (لا تعتمد على session_id)
     */
    public function paymentSuccess()
    {
        return response(
            '<!doctype html><html lang="ar"><meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1" />
            <body style="font-family: sans-serif; display:flex; align-items:center; justify-content:center; height:100vh;">
            <div style="text-align:center;">
                <h2>تم الدفع بنجاح</h2>
                <p>يمكنك إغلاق هذه الصفحة والعودة للتطبيق.</p>
            </div></body></html>',
            200,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    /**
     * GET /api/v1/payments/cancel
     * صفحة عرض فقط (لا تعتمد على session_id)
     */
    public function paymentCancel()
    {
        return response(
            '<!doctype html><html lang="ar"><meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1" />
            <body style="font-family: sans-serif; display:flex; align-items:center; justify-content:center; height:100vh;">
            <div style="text-align:center;">
                <h2>تم إلغاء العملية</h2>
                <p>يمكنك إغلاق هذه الصفحة والعودة للتطبيق.</p>
            </div></body></html>',
            200,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    /**
     * استخراج المبلغ المدفوع من session مع التحقق من التطابق
     * 
     * @param Donation $donation
     * @param array $sessionDetails
     * @return float
     */
    private function extractPaidAmount(Donation $donation, array $sessionDetails): float
    {
        // استخدام المبلغ الفعلي من session إن وجد
        $paidAmount = $donation->amount; // القيمة الافتراضية
        if (isset($sessionDetails['captured_amount']) || isset($sessionDetails['total_amount'])) {
            $capturedAmount = $sessionDetails['captured_amount'] ?? $sessionDetails['total_amount'] ?? 0;
            $paidAmount = $capturedAmount / 1000; // بيسة -> ريال
            
            // التحقق من تطابق المبلغ (مع هامش خطأ محسّن)
            // Tolerance محسّن: 0.1% مع حد أقصى 5 ريال (500 بيسة)
            $expectedAmountBaisa = (int)($donation->amount * 1000);
            $actualAmountBaisa = (int)$capturedAmount;
            $tolerance = min(
                max(50, (int)($expectedAmountBaisa * 0.001)), // 0.1% أو 50 بيسة كحد أدنى
                500 // حد أقصى 5 ريال (500 بيسة)
            );
            
            if (abs($actualAmountBaisa - $expectedAmountBaisa) > $tolerance) {
                Log::warning('Payment amount mismatch detected', [
                    'donation_id' => $donation->donation_id,
                    'expected' => $expectedAmountBaisa,
                    'actual' => $actualAmountBaisa,
                    'difference' => abs($actualAmountBaisa - $expectedAmountBaisa),
                    'tolerance' => $tolerance,
                ]);
                // نستخدم المبلغ الفعلي المدفوع
            }
        }
        
        return $paidAmount;
    }
}
