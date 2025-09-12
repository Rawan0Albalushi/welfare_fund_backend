<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ThawaniService
{
    private string $apiKey;
    private string $publishableKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey         = (string) config('services.thawani.secret_key');
        $this->publishableKey = (string) config('services.thawani.publishable_key');
        $this->baseUrl        = rtrim((string) config('services.thawani.base_url', 'https://uatcheckout.thawani.om/api/v1'), '/');

        if (empty($this->apiKey)) {
            throw new Exception('THAWANI_SECRET_KEY is not configured');
        }
    }

    /**
     * إنشاء جلسة دفع
     *
     * @param object $donation           كائن التبرع
     * @param array  $products           [ ['name','quantity','unit_amount(بيسة)'], ... ]
     * @param string $successUrl         رابط نجاح (https - عام)
     * @param string $cancelUrl          رابط إلغاء (https - عام)
     * @return array ['session_id','payment_url','raw']
     * @throws Exception
     */
    public function createSession($donation, array $products, string $successUrl, string $cancelUrl): array
    {
        try {
            $payload = [
                'client_reference_id' => $donation->donation_id,
                'mode'                => 'payment',
                'products'            => array_map(function ($p) {
                    return [
                        'name'        => $p['name'],
                        'quantity'    => (int) $p['quantity'],
                        'unit_amount' => (int) $p['unit_amount'], // بيسة
                    ];
                }, $products),
                'success_url' => $successUrl,
                'cancel_url'  => $cancelUrl,
                'metadata'    => [
                    'donation_db_id' => $donation->id
                ],
            ];

            Log::info('Thawani createSession request', ['payload' => $payload]);

            $response = Http::withHeaders([
                'Content-Type'    => 'application/json',
                'thawani-api-key' => $this->apiKey,
            ])->timeout(30)
              ->post($this->baseUrl . '/checkout/session', $payload);

            Log::info('Thawani createSession response', [
                'status_code' => $response->status(),
                'response'    => $response->json(),
            ]);

            if (!$response->successful()) {
                throw new Exception('Request failed: '.$response->status().' - '.$response->body());
            }

            $data      = $response->json();
            $sessionId = $data['data']['session_id'] ?? null;

            if (!$sessionId) {
                throw new Exception('Invalid response format: session_id not found');
            }

            // نبني رابط الدفع (الـ pay URL) باستخدام المفتاح العام
            $paymentBase = str_replace('/api/v1', '', $this->baseUrl); // => https://uatcheckout.thawani.om
            if (empty($this->publishableKey)) {
                // في الغالب ضروري لفتح صفحة الدفع
                throw new Exception('THAWANI_PUBLISHABLE_KEY is not configured');
            }
            $paymentUrl = "{$paymentBase}/pay/{$sessionId}?key={$this->publishableKey}";

            // تحديث معلومات الدفع في التبرع فوراً
            $donation->update([
                'payment_session_id' => $sessionId,
                'payment_url'        => $paymentUrl,
                'status'             => 'pending',
                'expires_at'         => $data['expires_at'] ?? now()->addDays(7),
            ]);

            return [
                'session_id'   => $sessionId,
                'payment_url'  => $paymentUrl,
                'raw'          => $data,
            ];

        } catch (Exception $e) {
            Log::error('Thawani createSession error', ['error' => $e->getMessage()]);
            throw new Exception('Failed to create payment session: ' . $e->getMessage());
        }
    }

    /**
     * جلب تفاصيل الجلسة + توحيد مفتاح الحالة
     *
     * @param string $sessionId
     * @return array  مثال: ['payment_status' => 'paid', ... باقي حقول data ...]
     * @throws Exception
     */
    public function getSessionDetails(string $sessionId): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type'    => 'application/json',
                'thawani-api-key' => $this->apiKey,
            ])->timeout(30)
              ->get($this->baseUrl . "/checkout/session/{$sessionId}");

            Log::info('Thawani getSessionDetails response', [
                'status_code' => $response->status(),
                'response'    => $response->json(),
            ]);

            if (!$response->successful()) {
                throw new Exception('Request failed: '.$response->status().' - '.$response->body());
            }

            $json = $response->json();
            $data = $json['data'] ?? null;
            if (!$data) {
                throw new Exception('Invalid response format: data not found');
            }

            // نضمن وجود payment_status في المستوى الأعلى من المصفوفة المرجعة
            $data['payment_status'] = $data['payment_status'] ?? ($json['payment_status'] ?? null);

            return $data;

        } catch (Exception $e) {
            Log::error('Thawani getSessionDetails error', [
                'error'      => $e->getMessage(),
                'session_id' => $sessionId,
            ]);
            throw new Exception('Failed to get session details: ' . $e->getMessage());
        }
    }

    /**
     * تأكيد الدفع وتحديث حالة التبرع
     *
     * @param string $sessionId
     * @return array ['status', 'donation_id', 'paid_amount', 'paid_at']
     * @throws Exception
     */
    public function confirmPayment(string $sessionId): array
    {
        try {
            $sessionDetails = $this->getSessionDetails($sessionId);
            $paymentStatus = $sessionDetails['payment_status'] ?? null;
            
            if (!$paymentStatus) {
                throw new Exception('Payment status not found in session details');
            }

            // البحث عن التبرع باستخدام session_id
            $donation = \App\Models\Donation::where('payment_session_id', $sessionId)->first();
            
            if (!$donation) {
                throw new Exception('Donation not found for session: ' . $sessionId);
            }

            // إذا كان التبرع مدفوعاً بالفعل، لا نكرر التحديث (idempotent)
            if ($donation->status === 'paid') {
                return [
                    'status' => 'paid',
                    'donation_id' => $donation->donation_id,
                    'paid_amount' => $donation->paid_amount,
                    'paid_at' => $donation->paid_at,
                ];
            }

            $updateData = [
                'payload' => $sessionDetails,
            ];

            if ($paymentStatus === 'paid') {
                // تحويل المبلغ من بيسة إلى ريال عماني
                $capturedAmount = $sessionDetails['captured_amount'] ?? $sessionDetails['total_amount'] ?? 0;
                $paidAmount = $capturedAmount / 1000; // بيسة -> ريال
                
                $updateData = array_merge($updateData, [
                    'status' => 'paid',
                    'paid_amount' => $paidAmount,
                    'paid_at' => isset($sessionDetails['paid_at']) 
                        ? \Carbon\Carbon::parse($sessionDetails['paid_at'])->setTimezone(config('app.timezone'))
                        : now(),
                ]);
            } else {
                // تحديث الحالة حسب حالة الدفع
                $statusMap = [
                    'canceled' => 'canceled',
                    'expired' => 'expired',
                    'failed' => 'failed',
                ];
                
                $updateData['status'] = $statusMap[$paymentStatus] ?? 'failed';
            }

            $donation->update($updateData);

            return [
                'status' => $updateData['status'],
                'donation_id' => $donation->donation_id,
                'paid_amount' => $updateData['paid_amount'] ?? null,
                'paid_at' => $updateData['paid_at'] ?? null,
            ];

        } catch (Exception $e) {
            Log::error('Thawani confirmPayment error', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);
            throw new Exception('Failed to confirm payment: ' . $e->getMessage());
        }
    }

    /**
     * استرداد (Refund) — اختياري
     */
    public function refundPayment(string $chargeId, ?string $reason = null): array
    {
        try {
            $payload = ['charge_id' => $chargeId] + ($reason ? ['reason' => $reason] : []);

            $response = Http::withHeaders([
                'Content-Type'    => 'application/json',
                'thawani-api-key' => $this->apiKey,
            ])->timeout(30)
              ->post($this->baseUrl . '/refund', $payload);

            if (!$response->successful()) {
                throw new Exception('Request failed: '.$response->status().' - '.$response->body());
            }

            $json = $response->json();
            if (!isset($json['data'])) {
                throw new Exception('Invalid response format: data not found');
            }
            return $json['data'];

        } catch (Exception $e) {
            Log::error('Thawani refundPayment error', [
                'error'     => $e->getMessage(),
                'charge_id' => $chargeId,
            ]);
            throw new Exception('Failed to refund payment: ' . $e->getMessage());
        }
    }
}
