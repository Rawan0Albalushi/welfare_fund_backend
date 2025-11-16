<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\ThawaniService;
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

            // مراقبة return_origin المستلم
            $returnOrigin = $request->input('return_origin');
            \Log::info('Payment creation request', [
                'donation_id' => $request->donation_id,
                'return_origin' => $returnOrigin,
                'all_input' => $request->all()
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
        $origin = $request->query('origin', 'http://localhost:49887/payment/success'); // fallback
        
        \Log::info('Bridge Success called', [
            'donation_id' => $donationId,
            'origin' => $origin,
            'all_params' => $request->query()
        ]);
        
        if (!$donationId) {
            $errorUrl = str_replace('/payment/success', '/payment/error', $origin) . '?message=' . urlencode('Donation ID is required');
            return redirect()->away($errorUrl);
        }

        $donation = Donation::where('donation_id', $donationId)->first();
        
        if (!$donation) {
            $errorUrl = str_replace('/payment/success', '/payment/error', $origin) . '?message=' . urlencode('Donation not found');
            return redirect()->away($errorUrl);
        }

        // تأكيد الحالة من ثواني (idempotent)
        if ($donation->status !== 'paid' && $donation->payment_session_id) {
            try {
                $sessionDetails = $this->thawaniService->getSessionDetails($donation->payment_session_id);
                $paymentStatus = $sessionDetails['payment_status'] ?? null;
                
                if ($paymentStatus === 'paid') {
                    // تحويل المبلغ من بيسة إلى ريال عماني
                    $capturedAmount = $sessionDetails['captured_amount'] ?? $sessionDetails['total_amount'] ?? 0;
                    $paidAmount = $capturedAmount / 1000; // بيسة -> ريال
                    
                    $donation->update([
                        'status' => 'paid',
                        'paid_amount' => $paidAmount,
                        'paid_at' => isset($sessionDetails['paid_at']) 
                            ? \Carbon\Carbon::parse($sessionDetails['paid_at'])->setTimezone(config('app.timezone'))
                            : now(),
                        'payload' => $sessionDetails,
                    ]);

                    if ($donation->campaign_id) {
                        $donation->campaign()
                            ->where('id', $donation->campaign_id)
                            ->increment('raised_amount', $paidAmount);
                    }
                } elseif ($paymentStatus === 'canceled') {
                    $donation->update(['status' => 'canceled']);
                }
            } catch (\Exception $e) {
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
        
        $redirectUrl = $origin . '?' . http_build_query($queryParams);
        
        \Log::info('Bridge Success redirecting', [
            'redirect_url' => $redirectUrl,
            'donation_id' => $donationId,
            'amount' => $donation->amount,
            'paid_amount' => $donation->paid_amount ?? $donation->amount,
            'origin' => $origin
        ]);
        
        return redirect()->away($redirectUrl);
    }

    /**
     * Bridge endpoint for canceled payment redirects
     */
    public function bridgeCancel(Request $request)
    {
        $donationId = $request->query('donation_id');
        $origin = $request->query('origin', 'http://localhost:49887/payment/cancel'); // fallback
        
        \Log::info('Bridge Cancel called', [
            'donation_id' => $donationId,
            'origin' => $origin,
            'all_params' => $request->query()
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
        
        $redirectUrl = $origin . '?' . http_build_query($queryParams);
        
        \Log::info('Bridge Cancel redirecting', [
            'redirect_url' => $redirectUrl,
            'donation_id' => $donationId,
            'amount' => $donation?->amount,
            'origin' => $origin
        ]);
        
        return redirect()->away($redirectUrl);
    }
}
