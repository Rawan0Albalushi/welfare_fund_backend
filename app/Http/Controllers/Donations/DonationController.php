<?php

namespace App\Http\Controllers\Donations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Donations\CreateDonationRequest;
use App\Http\Requests\Donations\CreateGiftDonationRequest;
use App\Http\Resources\DonationResource;
use App\Services\DonationsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(
 *     name="Donations",
 *     description="API Endpoints for donation management"
 * )
 */
class DonationController extends Controller
{
    public function __construct(
        private DonationsService $donationsService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/donations",
     *     summary="Create a quick donation",
     *     tags={"Donations"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"program_id","amount"},
     *             @OA\Property(property="program_id", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", example=100.00),
     *             @OA\Property(property="donor_name", type="string", example="John Doe"),
     *             @OA\Property(property="note", type="string", example="For a good cause")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Donation created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Donation created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="donation_id", type="string"),
     *                 @OA\Property(property="payment_url", type="string"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(CreateDonationRequest $request): JsonResponse
    {
        // Check for idempotency
        $idempotencyKey = $request->header('Idempotency-Key');
        if ($idempotencyKey) {
            $duplicate = $this->donationsService->checkDuplicateDonation($idempotencyKey);
            if ($duplicate) {
                return response()->json([
                    'message' => 'Donation already exists',
                    'data' => [
                        'donation_id' => $duplicate->donation_id,
                        'payment_url' => $this->generatePaymentUrl($duplicate),
                        'expires_at' => $duplicate->expires_at?->toISOString(),
                    ],
                ], 200);
            }
        }

        $donation = $this->donationsService->createQuickDonation(
            $request->validated(),
            $request->user()?->id
        );

        return response()->json([
            'message' => 'Donation created successfully',
            'data' => [
                'donation_id' => $donation->donation_id,
                'payment_url' => $this->generatePaymentUrl($donation),
                'expires_at' => $donation->expires_at?->toISOString(),
            ],
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/donations/gift",
     *     summary="Create a gift donation",
     *     tags={"Donations"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"program_id","amount","recipient"},
     *             @OA\Property(property="program_id", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", example=100.00),
     *             @OA\Property(property="recipient", type="object",
     *                 @OA\Property(property="name", type="string", example="Jane Doe"),
     *                 @OA\Property(property="phone", type="string", example="+966501234567"),
     *                 @OA\Property(property="message", type="string", example="Happy Birthday!")
     *             ),
     *             @OA\Property(property="sender", type="object",
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="phone", type="string", example="+966501234568"),
     *                 @OA\Property(property="hide_identity", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Gift donation created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gift donation created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="donation_id", type="string"),
     *                 @OA\Property(property="payment_url", type="string"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function gift(CreateGiftDonationRequest $request): JsonResponse
    {
        // Check for idempotency
        $idempotencyKey = $request->header('Idempotency-Key');
        if ($idempotencyKey) {
            $duplicate = $this->donationsService->checkDuplicateDonation($idempotencyKey);
            if ($duplicate) {
                return response()->json([
                    'message' => 'Gift donation already exists',
                    'data' => [
                        'donation_id' => $duplicate->donation_id,
                        'payment_url' => $this->generatePaymentUrl($duplicate),
                        'expires_at' => $duplicate->expires_at?->toISOString(),
                    ],
                ], 200);
            }
        }

        $donation = $this->donationsService->createGiftDonation(
            $request->validated(),
            $request->user()?->id
        );

        return response()->json([
            'message' => 'Gift donation created successfully',
            'data' => [
                'donation_id' => $donation->donation_id,
                'payment_url' => $this->generatePaymentUrl($donation),
                'expires_at' => $donation->expires_at?->toISOString(),
            ],
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/donations/{id}/status",
     *     summary="Check donation status",
     *     tags={"Donations"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Donation ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Donation status retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Donation status retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="amount", type="number"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time"),
     *                 @OA\Property(property="paid_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Donation not found"
     *     )
     * )
     */
    public function status(string $id): JsonResponse
    {
        $status = $this->donationsService->getDonationStatus($id);

        if (!$status) {
            return response()->json([
                'message' => 'Donation not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Donation status retrieved successfully',
            'data' => $status,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/payments/callback",
     *     summary="Payment callback endpoint",
     *     tags={"Donations"},
     *     @OA\Parameter(
     *         name="donation_id",
     *         in="query",
     *         description="Donation ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Payment status",
     *         required=true,
     *         @OA\Schema(type="string", enum={"success", "failed"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment callback processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Payment processed successfully")
     *         )
     *     )
     * )
     */
    public function callback(Request $request): JsonResponse
    {
        $donationId = $request->get('donation_id');
        $status = $request->get('status');

        if (!$donationId || !$status) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing required parameters',
            ], 400);
        }

        // Process the callback
        $webhookData = [
            'donation_id' => $donationId,
            'status' => $status === 'success' ? 'paid' : 'failed',
            'amount' => 0, // Will be updated from webhook
            'paid_at' => $status === 'success' ? now() : null,
        ];

        $this->donationsService->processPaymentWebhook($webhookData);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment callback processed successfully',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/webhook",
     *     summary="Payment webhook endpoint",
     *     tags={"Donations"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"donation_id","status","amount"},
     *             @OA\Property(property="donation_id", type="string"),
     *             @OA\Property(property="status", type="string", enum={"paid", "failed"}),
     *             @OA\Property(property="amount", type="number"),
     *             @OA\Property(property="paid_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webhook processed successfully"
     *     )
     * )
     */
    public function webhook(Request $request): JsonResponse
    {
        // Verify webhook signature (implement based on payment provider)
        // $this->verifyWebhookSignature($request);

        $webhookData = $request->all();
        
        $this->donationsService->processPaymentWebhook($webhookData);

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Generate payment URL for donation.
     */
    private function generatePaymentUrl($donation): string
    {
        // This would integrate with your payment provider
        // For now, return a placeholder URL
        return config('app.url') . '/payments/' . $donation->donation_id;
    }
}
