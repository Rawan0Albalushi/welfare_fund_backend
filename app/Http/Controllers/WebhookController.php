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
		// التحقق من التوقيع (إلزامي في الإنتاج، اختياري في التطوير)
		$secret = (string) config('services.thawani.webhook_secret');
		$isProduction = app()->environment('production');
		
		if ($isProduction && empty($secret)) {
			Log::error('Webhook secret not configured in production');
			return response()->json(['ok' => false, 'message' => 'Webhook secret required'], 500);
		}

		if (!empty($secret)) {
			$headerName = (string) config('services.thawani.webhook_signature_header', 'X-Webhook-Signature');
			$signature  = $request->header($headerName);
			
			if (!$this->isValidSignature($request->getContent(), $signature, $secret)) {
				Log::warning('Webhook signature invalid', [
					'header' => $headerName,
					'has_signature' => !empty($signature),
					'ip' => $request->ip(),
				]);
				return response()->json(['ok' => false], 401);
			}
		}

		// تسجيل محدود (بدون بيانات حساسة كاملة)
		Log::info('Thawani Webhook received', [
			'signature_verified' => !empty($secret),
			'ip' => $request->ip(),
		]);

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

        // منع التكرار: لو حالتها Paid لا نعيد الزيادة
        if ($status === 'paid' && $donation->status !== 'paid') {
            DB::transaction(function () use ($donation, $amountBaisa, $sessionId) {
                // إعادة جلب التبرع مع lock لتجنب Race Conditions
                $donation = Donation::where('payment_session_id', $sessionId)
                    ->lockForUpdate()
                    ->first();
                
                // التحقق مرة أخرى بعد lock
                if ($donation->status === 'paid') {
                    Log::info('Webhook: Donation already paid, skipping', ['session_id' => $sessionId]);
                    return;
                }

                // التحقق من صحة المبلغ المدفوع
                $paidAmount = $donation->amount; // القيمة الافتراضية
                if (is_numeric($amountBaisa)) {
                    $paidAmount = ((int) $amountBaisa) / 1000.0;
                    
                    // التحقق من تطابق المبلغ مع مبلغ التبرع المحفوظ
                    $expectedAmountBaisa = (int)($donation->amount * 1000);
                    $actualAmountBaisa = (int)$amountBaisa;
                    $tolerance = max(100, (int)($expectedAmountBaisa * 0.01)); // 1% أو 100 بيسة كحد أدنى
                    
                    if (abs($actualAmountBaisa - $expectedAmountBaisa) > $tolerance) {
                        Log::warning('Webhook: Payment amount mismatch', [
                            'session_id' => $sessionId,
                            'donation_id' => $donation->donation_id,
                            'expected' => $expectedAmountBaisa,
                            'actual' => $actualAmountBaisa,
                            'difference' => abs($actualAmountBaisa - $expectedAmountBaisa),
                            'tolerance' => $tolerance,
                        ]);
                        // نستخدم المبلغ الفعلي المدفوع لكن نسجل التحذير
                    }
                }

                $donation->update([
                    'status'      => 'paid',
                    'paid_amount' => $paidAmount,
                ]);

                // تحديث مبلغ الحملة مع lock
                if ($donation->campaign_id) {
                    Campaign::where('id', $donation->campaign_id)
                        ->lockForUpdate()
                        ->increment('raised_amount', $paidAmount);
                }
                
                Log::info('Webhook: Payment processed successfully', [
                    'session_id' => $sessionId,
                    'donation_id' => $donation->donation_id,
                    'amount' => $paidAmount,
                ]);
            });
        } elseif (in_array($status, ['cancelled', 'canceled', 'failed'])) {
            if ($donation->status !== 'cancelled' && $donation->status !== 'paid') {
                $donation->update(['status' => 'cancelled']);
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * التحقق من صحة توقيع الويبهوك
     * 
     * @param string $rawBody
     * @param string|null $signatureHeader
     * @param string $secret
     * @return bool
     */
	private function isValidSignature(string $rawBody, ?string $signatureHeader, string $secret): bool
	{
		if (empty($signatureHeader) || empty($secret)) {
			return false;
		}
		
		// حساب التوقيع المتوقع
		$expected = hash_hmac('sha256', $rawBody, $secret);
		
		// مقارنة آمنة لتجنب timing attacks
		return hash_equals($expected, $signatureHeader);
	}
}
