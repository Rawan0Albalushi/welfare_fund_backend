<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ThawaniPaymentService
{
    private Client $httpClient;
    private string $secretKey;
    private string $publishableKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = env('THAWANI_SECRET_KEY', '');
        $this->publishableKey = env('THAWANI_PUBLISHABLE_KEY', '');
        $this->baseUrl = env('THAWANI_BASE_URL', 'https://uatcheckout.thawani.om/api/v1');
        
        $this->httpClient = new Client([
            'headers' => [
                'Content-Type' => 'application/json',
                'thawani-api-key' => $this->secretKey,
            ],
            'timeout' => 30,
        ]);
    }

    /**
     * Create a payment session with Thawani API
     *
     * @param array $products Array of products with name, quantity, unit_amount
     * @param string $clientReferenceId Unique reference ID for the payment
     * @param string $successUrl URL to redirect after successful payment
     * @param string $cancelUrl URL to redirect after cancelled payment
     * @return array Returns session_id and payment_url
     * @throws \Exception
     */
    public function createSession(array $products, string $clientReferenceId, string $successUrl, string $cancelUrl): array
    {
        if (empty($this->secretKey)) {
            throw new \Exception('THAWANI_SECRET_KEY is not configured');
        }

        if (empty($this->publishableKey)) {
            throw new \Exception('THAWANI_PUBLISHABLE_KEY is not configured');
        }

        try {
            $payload = [
                'client_reference_id' => $clientReferenceId,
                'mode' => 'payment',
                'products' => $products,
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ];

            Log::info('Thawani createSession request', [
                'payload' => $payload,
                'base_url' => $this->baseUrl,
                'client_reference_id' => $clientReferenceId
            ]);

            $response = $this->httpClient->post($this->baseUrl . '/checkout/session', [
                'json' => $payload
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            Log::info('Thawani createSession response', [
                'status_code' => $response->getStatusCode(),
                'response' => $responseData
            ]);

            if ($response->getStatusCode() === 200 && isset($responseData['data']['session_id'])) {
                // Build the payment URL with the publishable key
                $sessionId = $responseData['data']['session_id'];
                // Remove /api/v1 from base URL to get the correct payment URL
                $paymentBaseUrl = str_replace('/api/v1', '', $this->baseUrl);
                $paymentUrl = "{$paymentBaseUrl}/pay/{$sessionId}?key={$this->publishableKey}";
                
                return [
                    'session_id' => $sessionId,
                    'payment_url' => $paymentUrl,
                    'raw_response' => $responseData
                ];
            } else {
                throw new \Exception('Invalid response from Thawani API: ' . json_encode($responseData));
            }

        } catch (GuzzleException $e) {
            $errorMessage = 'Thawani API request failed: ' . $e->getMessage();
            
            // Try to get more details from the response
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $errorBody = $response->getBody()->getContents();
                $errorMessage .= ' - Response: ' . $errorBody;
            }
            
            Log::error('Thawani createSession failed', [
                'error' => $errorMessage,
                'client_reference_id' => $clientReferenceId,
                'products' => $products
            ]);
            
            throw new \Exception($errorMessage);
        }
    }

    /**
     * Retrieve session details from Thawani API
     *
     * @param string $sessionId The session ID to retrieve
     * @return array Returns session details including payment status
     * @throws \Exception
     */
    public function retrieveSession(string $sessionId): array
    {
        if (empty($this->secretKey)) {
            throw new \Exception('THAWANI_SECRET_KEY is not configured');
        }

        try {
            Log::info('Thawani retrieveSession request', [
                'session_id' => $sessionId
            ]);

            $response = $this->httpClient->get($this->baseUrl . "/checkout/session/{$sessionId}");

            $responseData = json_decode($response->getBody()->getContents(), true);

            Log::info('Thawani retrieveSession response', [
                'status_code' => $response->getStatusCode(),
                'response' => $responseData
            ]);

            if ($response->getStatusCode() === 200 && isset($responseData['data'])) {
                return $responseData['data'];
            } else {
                throw new \Exception('Invalid response from Thawani API: ' . json_encode($responseData));
            }

        } catch (GuzzleException $e) {
            $errorMessage = 'Thawani API request failed: ' . $e->getMessage();
            
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $errorBody = $response->getBody()->getContents();
                $errorMessage .= ' - Response: ' . $errorBody;
            }
            
            Log::error('Thawani retrieveSession failed', [
                'error' => $errorMessage,
                'session_id' => $sessionId
            ]);
            
            throw new \Exception($errorMessage);
        }
    }
}
