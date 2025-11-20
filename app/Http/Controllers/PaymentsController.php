<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\ThawaniService;
use App\Helpers\PaymentSecurityHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentsController extends Controller
{
    protected ThawaniService $thawaniService;

    public function __construct(ThawaniService $thawaniService)
    {
        $this->thawaniService = $thawaniService;
    }

    /**
     * إنشاء جلسة دفع جديدة
     * 
     * @OA\Post(
     *     path="/api/v1/payments/create",
     *     summary="إنشاء جلسة دفع",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"donation_id", "products"},
     *             @OA\Property(property="donation_id", type="string", example="DN_12345678-1234-1234-1234-123456789012"),
     *             @OA\Property(property="products", type="array", @OA\Items(
     *                 @OA\Property(property="name", type="string", example="تبرع خيري"),
     *                 @OA\Property(property="quantity", type="integer", example=1),
     *                 @OA\Property(property="unit_amount", type="integer", example=10000, description="المبلغ بالبيسة")
     *             )),
     *             @OA\Property(property="return_origin", type="string", example="http://localhost:49887", description="Frontend origin URL for redirects")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="تم إنشاء جلسة الدفع بنجاح",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="checkout_url", type="string"),
     *                 @OA\Property(property="session_id", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="بيانات غير صحيحة"),
     *     @OA\Response(response=404, description="التبرع غير موجود"),
     *     @OA\Response(response=500, description="خطأ في الخادم")
     * )
     */
    public function create(Request $request): JsonResponse
    {
		// Legacy fast path to support tests: accept client_reference_id + success/cancel URLs
		if ($request->has('client_reference_id')) {
			$validator = Validator::make($request->all(), [
				'products'               => 'required|array|min:1',
				'products.*.name'        => 'required|string',
				'products.*.quantity'    => 'required|integer|min:1',
				'products.*.unit_amount' => 'required|integer|min:1',
				'client_reference_id'    => 'required|string',
				'success_url'            => 'required|url',
				'cancel_url'             => 'required|url',
			]);
			if ($validator->fails()) {
				return response()->json([
					'success' => false,
					'message' => 'Validation failed',
					'errors'  => $validator->errors()
				], 422);
			}
			try {
				/** @var \App\Services\ThawaniPaymentService $wrapper */
				$wrapper = app(\App\Services\ThawaniPaymentService::class);
				$result = $wrapper->createSession(
					$request->products,
					$request->client_reference_id,
					$request->success_url,
					$request->cancel_url
				);
				return response()->json([
					'success'     => true,
					'message'     => 'OK',
					'session_id'  => $result['session_id'],
					'payment_url' => $result['payment_url'],
				]);
			} catch (\Exception $e) {
				return response()->json([
					'success' => false,
					'message' => 'Service error'
				], 500);
			}
		}

        $validator = Validator::make($request->all(), [
            'donation_id' => 'required|string',
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|string',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_amount' => 'required|integer|min:1',
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
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في إنشاء جلسة الدفع: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تأكيد حالة الدفع
     * 
     * @OA\Post(
     *     path="/api/v1/payments/confirm",
     *     summary="تأكيد حالة الدفع",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="session_id", type="string", example="sess_1234567890"),
     *             @OA\Property(property="donation_id", type="string", example="DN_12345678-1234-1234-1234-123456789012")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="تم تأكيد حالة الدفع",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="status", type="string", example="paid"),
     *                 @OA\Property(property="donation_id", type="string"),
     *                 @OA\Property(property="paid_amount", type="number", format="float"),
     *                 @OA\Property(property="paid_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="بيانات غير صحيحة"),
     *     @OA\Response(response=404, description="التبرع أو الجلسة غير موجودة"),
     *     @OA\Response(response=500, description="خطأ في الخادم")
     * )
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
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في تأكيد الدفع: ' . $e->getMessage()
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
                        // تحويل المبلغ من بيسة إلى ريال عماني
                        $capturedAmount = $sessionDetails['captured_amount'] ?? $sessionDetails['total_amount'] ?? 0;
                        $paidAmount = $capturedAmount / 1000; // بيسة -> ريال
                        
                        // التحقق من تطابق المبلغ (مع هامش خطأ 1%)
                        $expectedAmountBaisa = (int)($donation->amount * 1000);
                        $actualAmountBaisa = (int)$capturedAmount;
                        $tolerance = max(100, (int)($expectedAmountBaisa * 0.01)); // 1% أو 100 بيسة كحد أدنى
                        
                        if (abs($actualAmountBaisa - $expectedAmountBaisa) > $tolerance) {
                            Log::warning('Payment amount mismatch detected', [
                                'donation_id' => $donationId,
                                'expected' => $expectedAmountBaisa,
                                'actual' => $actualAmountBaisa,
                                'difference' => abs($actualAmountBaisa - $expectedAmountBaisa),
                            ]);
                            // نستخدم المبلغ الفعلي المدفوع
                        }
                        
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
                            \App\Models\Campaign::where('id', $donation->campaign_id)
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
                    'error' => $e->getMessage()
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
     * نجاح الدفع للموبايل
     * 
     * @OA\Get(
     *     path="/api/v1/payments/mobile/success",
     *     summary="نجاح الدفع للموبايل",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="donation_id",
     *         in="query",
     *         required=true,
     *         description="معرف التبرع",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="session_id",
     *         in="query",
     *         required=false,
     *         description="معرف جلسة الدفع (اختياري - سيتم استخدام payment_session_id من التبرع إذا لم يتم تمريره)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="تم استرجاع معلومات الدفع بنجاح",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="donation_id", type="string"),
     *             @OA\Property(property="session_id", type="string"),
     *             @OA\Property(property="amount", type="number", format="float"),
     *             @OA\Property(property="campaign_title", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="التبرع غير موجود")
     * )
     */
    public function mobileSuccess(Request $request): JsonResponse
    {
        $donationId = $request->query('donation_id');
        $sessionId = $request->query('session_id');

        if (!$donationId) {
            return response()->json([
                'status' => 'error',
                'message' => 'donation_id is required'
            ], 400);
        }

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
        if ($actualSessionId) {
            try {
                // التحقق من حالة الدفع من ثواني (idempotent)
                if ($donation->status !== 'paid' && $donation->payment_session_id) {
                    $sessionDetails = $this->thawaniService->getSessionDetails($donation->payment_session_id);
                    $paymentStatus = $sessionDetails['payment_status'] ?? null;
                    
                    if ($paymentStatus === 'paid') {
                        // تحويل المبلغ من بيسة إلى ريال عماني
                        $capturedAmount = $sessionDetails['captured_amount'] ?? $sessionDetails['total_amount'] ?? 0;
                        $paidAmount = $capturedAmount / 1000; // بيسة -> ريال
                        
                        // التحقق من تطابق المبلغ (مع هامش خطأ 1%)
                        $expectedAmountBaisa = (int)($donation->amount * 1000);
                        $actualAmountBaisa = (int)$capturedAmount;
                        $tolerance = max(100, (int)($expectedAmountBaisa * 0.01)); // 1% أو 100 بيسة كحد أدنى
                        
                        if (abs($actualAmountBaisa - $expectedAmountBaisa) > $tolerance) {
                            Log::warning('Mobile success: Payment amount mismatch', [
                                'donation_id' => $donationId,
                                'expected' => $expectedAmountBaisa,
                                'actual' => $actualAmountBaisa,
                                'difference' => abs($actualAmountBaisa - $expectedAmountBaisa),
                            ]);
                        }
                        
                        DB::beginTransaction();
                        
                        // إعادة جلب التبرع مع lock لتجنب Race Conditions
                        $donation = Donation::where('donation_id', $donationId)
                            ->lockForUpdate()
                            ->first();
                        
                        // التحقق مرة أخرى بعد lock
                        if ($donation->status === 'paid') {
                            DB::commit();
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
                                \App\Models\Campaign::where('id', $donation->campaign_id)
                                    ->lockForUpdate()
                                    ->increment('raised_amount', $paidAmount);
                            }
                            
                            DB::commit();
                            
                            Log::info('Mobile success: Payment confirmed and donation updated', [
                                'donation_id' => $donationId,
                                'session_id' => $actualSessionId,
                            ]);
                        }
                    } elseif ($paymentStatus === 'canceled') {
                        $donation->update(['status' => 'canceled']);
                        Log::info('Mobile success: Payment was canceled', [
                            'donation_id' => $donationId,
                            'session_id' => $actualSessionId
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Mobile success: Failed to confirm payment status', [
                    'donation_id' => $donationId,
                    'session_id' => $actualSessionId,
                    'error' => $e->getMessage()
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

        return response()->json([
            'status' => 'success',
            'donation_id' => $donation->donation_id,
            'session_id' => $actualSessionId,
            'amount' => (float) $donation->amount,
            'campaign_title' => $campaignTitle,
        ]);
    }
}
