<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Donation;
use App\Models\Campaign;

class WebhookController extends Controller
{
    /**
     * POST /api/v1/payments/webhook/thawani
     */
    public function handle(Request $request)
    {
        // سجلي الحمولة للمتابعة (يُفضّل تخفيف اللوق في الإنتاج)
        Log::info('Thawani Webhook received', ['payload' => $request->all()]);

        // (اختياري) تحققي من التوقيع لو ثواني يوفر Signature Header
        // $signature = $request->header('X-Webhook-Signature'); // مثال
        // if (!$this->isValidSignature($request->getContent(), $signature)) { return response()->json(['ok' => false], 401); }

        $payload  = $request->all();

        // بعض الأنظمة ترسل event_type + data
        $event    = $payload['event_type'] ?? $payload['type'] ?? null;
        $data     = $payload['data'] ?? $payload;

        // محاولات متعددة لاستخراج session_id و status و amount
        $sessionId = $data['session_id'] ?? $data['id'] ?? ($data['object']['id'] ?? null);
        $status    = $data['payment_status'] ?? $data['status'] ?? null;
        $amountBaisa = $data['total_amount'] ?? $data['amount'] ?? null; // قد تكون بالبيسة

        if (!$sessionId) {
            // أحيانًا تأتي داخل object.data أو بطريقة مختلفة
            $sessionId = $payload['data']['session']['id'] ?? null;
        }

        if (!$sessionId && isset($payload['object']) && is_array($payload['object'])) {
            $sessionId = $payload['object']['id'] ?? null;
            $status    = $payload['object']['payment_status'] ?? $status;
            $amountBaisa = $payload['object']['total_amount'] ?? $amountBaisa;
        }

        if (!$sessionId) {
            Log::warning('Webhook: session_id not found in payload');
            return response()->json(['ok' => false, 'reason' => 'session_id not found'], 400);
        }

        // توحيد قيم الحالة لو اختلف اسمها
        if (!$status && $event) {
            if (stripos($event, 'paid') !== false) {
                $status = 'paid';
            } elseif (stripos($event, 'cancel') !== false || stripos($event, 'fail') !== false) {
                $status = 'cancelled';
            }
        }

        $donation = Donation::where('payment_session_id', $sessionId)->first();
        if (!$donation) {
            Log::warning('Webhook: donation not found for session', ['session_id' => $sessionId]);
            return response()->json(['ok' => true]); // نرجع 200 عشان ما يعيدوا الإرسال بلا نهاية
        }

        // امنعي التكرار: لو حالتها Paid لا نعيد الزيادة
        if ($status === 'paid' && $donation->status !== 'paid') {
            DB::transaction(function () use ($donation, $amountBaisa) {
                // لو المبلغ جاء بالبيسة نحوله إلى ريال، وإلا نخلي مبلغ التبرع
                $paidAmount = $donation->amount;
                if (is_numeric($amountBaisa)) {
                    $paidAmount = ((int) $amountBaisa) / 1000.0;
                }

                $donation->update([
                    'status'      => 'paid',
                    'paid_amount' => $paidAmount,
                ]);

                if ($donation->campaign_id) {
                    Campaign::where('id', $donation->campaign_id)
                        ->increment('raised_amount', $paidAmount);
                }
            });
        } elseif (in_array($status, ['cancelled', 'canceled', 'failed'])) {
            if ($donation->status !== 'cancelled' && $donation->status !== 'paid') {
                $donation->update(['status' => 'cancelled']);
            }
        }

        return response()->json(['ok' => true]);
    }

    // مثال للتحقق من التوقيع (لو وفرته ثواني). حدثيه بناءً على توثيقهم:
    // private function isValidSignature(string $rawBody, ?string $signatureHeader): bool
    // {
    //     if (!$signatureHeader) return false;
    //     $secret = config('services.thawani.webhook_secret'); // أضيفيه في .env إن وجد
    //     $expected = hash_hmac('sha256', $rawBody, $secret);
    //     return hash_equals($expected, $signatureHeader);
    // }
}
