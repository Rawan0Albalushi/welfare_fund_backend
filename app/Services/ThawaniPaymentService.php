<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * غلاف (Wrapper) يستدعي ThawaniService الحقيقي
 * الهدف: عدم كسر أي استخدامات قديمة لاسم ThawaniPaymentService
 */
class ThawaniPaymentService
{
    private ThawaniService $core;

    public function __construct(ThawaniService $core)
    {
        $this->core = $core;
    }

    /**
     * ملاحظة: ترتيب الوسائط هنا كان مختلف عندك سابقًا
     * سنقبل الترتيب القديم ونحوّله للترتيب المعتمد في ThawaniService:
     *  - قديم: (array $products, string $clientReferenceId, string $successUrl, string $cancelUrl)
     *  - جديد: (object $donation, array $products, ?string $returnOrigin)
     */
    public function createSession($donationOrProducts, $clientReferenceIdOrProducts = null, $successUrl = null, $cancelUrl = null, $returnOrigin = null): array
    {
        try {
            // إذا كان المعامل الأول array، فهذا يعني الاستخدام القديم
            if (is_array($donationOrProducts)) {
                // الاستخدام القديم: (array $products, string $clientReferenceId, string $successUrl, string $cancelUrl)
                $products = $donationOrProducts;
                $clientReferenceId = $clientReferenceIdOrProducts;
                
                // إنشاء donation مؤقت للتوافق مع التوقيع الجديد
                $tempDonation = (object) [
                    'donation_id' => $clientReferenceId,
                    'id' => null
                ];
                
                $res = $this->core->createSession($tempDonation, $products, $returnOrigin);
            } else {
                // الاستخدام الجديد: (object $donation, array $products, ?string $returnOrigin)
                $donation = $donationOrProducts;
                $products = $clientReferenceIdOrProducts;
                
                $res = $this->core->createSession($donation, $products, $returnOrigin);
            }

            // توحيد أسماء الحقول كما كان يرجعها هذا السيرفس سابقًا
            return [
                'session_id'   => $res['session_id'],
                'payment_url'  => $res['payment_url'], // كان اسمها payment_url عندك
                'raw_response' => $res['raw'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('ThawaniPaymentService createSession failed (wrapper)', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function retrieveSession(string $sessionId): array
    {
        try {
            // هذا يرجّع data + payment_status في المستوى الأعلى
            return $this->core->getSessionDetails($sessionId);
        } catch (Exception $e) {
            Log::error('ThawaniPaymentService retrieveSession failed (wrapper)', [
                'error'      => $e->getMessage(),
                'session_id' => $sessionId,
            ]);
            throw $e;
        }
    }
}
