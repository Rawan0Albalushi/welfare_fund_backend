<?php

namespace App\Http\Controllers;

use App\Services\ThawaniService;
use App\Models\Donation;
use App\Models\Program;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    private ThawaniService $thawaniService;

    public function __construct(ThawaniService $thawaniService)
    {
        $this->thawaniService = $thawaniService;
    }

    /**
     * GET /api/v1/payments?session_id=...
     */
    public function index(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'Session ID is required',
            ], 400);
        }

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
     *
     * Body example:
     * {
     *   "products": [{"name":"Donation","quantity":1,"unit_amount":1000}],
     *   "program_id": 1, // or "campaign_id": 5
     *   "donor_name": "اسم المتبرع",
     *   "note": "ملاحظة",
     *   "type": "quick" // or "gift"
     * }
     */
    public function createPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'products'               => 'required|array|min:1',
            'products.*.name'        => 'required|string|max:255',
            'products.*.quantity'    => 'required|integer|min:1',
            'products.*.unit_amount' => 'required|integer|min:1', // بيسة
            'client_reference_id'    => 'nullable|string|max:255',
            'success_url'            => 'nullable|url',
            'cancel_url'             => 'nullable|url',

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

        $successUrl = $request->success_url ?? config('services.thawani.success_url');
        $cancelUrl  = $request->cancel_url  ?? config('services.thawani.cancel_url');

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

            // لا نزيد raised_amount الآن — عند التأكد من الدفع فقط

            // إنشاء جلسة الدفع على ثواني
            $result = $this->thawaniService->createSession(
                $donation, // تمرير كائن التبرع بدلاً من donation_id
                $request->products,
                $successUrl,
                $cancelUrl
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

            \Log::error('Thawani payment session creation failed', [
                'error'               => $e->getMessage(),
                'client_reference_id' => $request->client_reference_id ?? null,
                'products'            => $request->products
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment session: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/payments/status/{sessionId}
     * - يستعلم من ثواني ويثبّت الحالة في DB (paid/cancelled)
     */
    public function getPaymentStatus(string $sessionId): JsonResponse
    {
        try {
            $session = $this->thawaniService->getSessionDetails($sessionId);
            $paymentStatus = $session['payment_status'] ?? ($session['data']['payment_status'] ?? 'unknown');

            $donation = Donation::where('payment_session_id', $sessionId)->first();

            if ($donation) {
                if ($paymentStatus === 'paid' && $donation->status !== 'paid') {
                    DB::transaction(function () use ($donation) {
                        $donation->update([
                            'status'      => 'paid',
                            'paid_amount' => $donation->amount,
                        ]);

                        if ($donation->campaign_id) {
                            Campaign::where('id', $donation->campaign_id)
                                ->increment('raised_amount', $donation->amount);
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
            \Log::error('Thawani payment status retrieval failed', [
                'error'      => $e->getMessage(),
                'session_id' => $sessionId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment status: ' . $e->getMessage(),
            ], 500);
        }
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
}

