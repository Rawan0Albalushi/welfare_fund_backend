<?php

namespace App\Http\Controllers;

use App\Services\ThawaniService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    private ThawaniService $thawaniService;

    public function __construct(ThawaniService $thawaniService)
    {
        $this->thawaniService = $thawaniService;
    }

    /**
     * Create a payment session
     * POST /api/payments/create
     */
    public function createPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|string|max:255',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_amount' => 'required|integer|min:1',
            'client_reference_id' => 'required|string|max:255',
            'success_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $successUrl = $request->success_url ?? config('services.thawani.success_url');
        $cancelUrl = $request->cancel_url ?? config('services.thawani.cancel_url');

        try {
            $result = $this->thawaniService->createSession(
                $request->client_reference_id,
                $request->products,
                $successUrl,
                $cancelUrl
            );

            return response()->json([
                'success' => true,
                'session_id' => $result['session_id'],
                'payment_url' => $result['payment_url'],
            ]);

        } catch (\Exception $e) {
            \Log::error('Thawani payment session creation failed', [
                'error' => $e->getMessage(),
                'client_reference_id' => $request->client_reference_id,
                'products' => $request->products
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment session: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment status
     * GET /api/payments/status/{sessionId}
     */
    public function getPaymentStatus(string $sessionId): JsonResponse
    {
        try {
            $result = $this->thawaniService->getSessionDetails($sessionId);

            return response()->json([
                'success' => true,
                'payment_status' => $result['payment_status'] ?? 'unknown',
                'session_id' => $sessionId,
                'raw_response' => $result,
            ]);

        } catch (\Exception $e) {
            \Log::error('Thawani payment status retrieval failed', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
