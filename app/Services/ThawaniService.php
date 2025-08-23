<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ThawaniService
{
    private string $apiKey;
    private string $baseUrl;
    private string $publishableKey;

    public function __construct()
    {
        $this->apiKey = config('services.thawani.secret_key');
        $this->publishableKey = config('services.thawani.publishable_key');
        $this->baseUrl = rtrim(config('services.thawani.base_url', 'https://uatcheckout.thawani.om/api/v1'), '/');

        if (empty($this->apiKey)) {
            throw new Exception('THAWANI_SECRET_KEY is not configured');
        }
    }

    /**
     * Create a payment session with Thawani API
     *
     * @param string $clientReferenceId Unique reference ID for the payment
     * @param array $products Array of products with name, quantity, unit_amount
     * @param string $successUrl URL to redirect after successful payment
     * @param string $cancelUrl URL to redirect after cancelled payment
     * @return array Returns session data including session_id and payment_url
     * @throws Exception
     */
    public function createSession(string $clientReferenceId, array $products, string $successUrl, string $cancelUrl): array
    {
        try {
            $payload = [
                'client_reference_id' => $clientReferenceId,
                'mode' => 'payment',
                'products' => $products,
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ];

            Log::info('Thawani createSession request', ['payload' => $payload]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'thawani-api-key' => $this->apiKey,
            ])
            ->timeout(30)
            ->post($this->baseUrl . '/checkout/session', $payload);

            Log::info('Thawani createSession response', [
                'status_code' => $response->status(),
                'response' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']['session_id'])) {
                    $sessionId = $data['data']['session_id'];
                    return [
                        'session_id' => $sessionId,
                        'payment_url' => str_replace('/api/v1', '', $this->baseUrl) . "/pay/{$sessionId}?key={$this->publishableKey}",
                    ];
                }

                throw new Exception('Invalid response format: session_id not found');
            }

            throw new Exception('Request failed: ' . $response->status() . ' - ' . $response->body());

        } catch (Exception $e) {
            Log::error('Thawani createSession error', ['error' => $e->getMessage()]);
            throw new Exception('Failed to create payment session: ' . $e->getMessage());
        }
    }

    /**
     * Get session details from Thawani API
     *
     * @param string $sessionId The session ID to retrieve
     * @return array Returns the full session response including payment_status
     * @throws Exception
     */
    public function getSessionDetails(string $sessionId): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'thawani-api-key' => $this->apiKey,
            ])
            ->timeout(30)
            ->get($this->baseUrl . "/checkout/session/{$sessionId}");

            Log::info('Thawani getSessionDetails response', [
                'status_code' => $response->status(),
                'response' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data'])) {
                    return $data['data'];
                }
                throw new Exception('Invalid response format: data not found');
            }

            throw new Exception('Request failed: ' . $response->status() . ' - ' . $response->body());

        } catch (Exception $e) {
            Log::error('Thawani getSessionDetails error', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId
            ]);
            throw new Exception('Failed to get session details: ' . $e->getMessage());
        }
    }

    /**
     * Refund a payment using Thawani API
     *
     * @param string $chargeId The charge ID to refund
     * @param string|null $reason Optional reason for refund
     * @return array Returns the refund response
     * @throws Exception
     */
    public function refundPayment(string $chargeId, ?string $reason = null): array
    {
        try {
            $payload = ['charge_id' => $chargeId];
            if ($reason) {
                $payload['reason'] = $reason;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'thawani-api-key' => $this->apiKey,
            ])
            ->timeout(30)
            ->post($this->baseUrl . '/refund', $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data'])) {
                    return $data['data'];
                }
                throw new Exception('Invalid response format: data not found');
            }

            throw new Exception('Request failed: ' . $response->status() . ' - ' . $response->body());

        } catch (Exception $e) {
            Log::error('Thawani refundPayment error', [
                'error' => $e->getMessage(),
                'charge_id' => $chargeId
            ]);
            throw new Exception('Failed to refund payment: ' . $e->getMessage());
        }
    }

    /**
     * Test the connection to Thawani API
     *
     * @return bool Returns true if connection is successful
     * @throws Exception
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'thawani-api-key' => $this->apiKey,
            ])
            ->timeout(10)
            ->get($this->baseUrl);

            return $response->successful();
        } catch (Exception $e) {
            throw new Exception('Connection test failed: ' . $e->getMessage());
        }
    }
}
