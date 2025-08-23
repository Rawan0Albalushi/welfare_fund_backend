<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Donation; // أو الموديل اللي يخزن التبرعات عندك

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        // سجل البيانات في اللوق عشان تتأكد من اللي يوصلك من ثواني
        Log::info('Thawani Webhook received', $request->all());

        $event = $request->input('event_type');
        $data  = $request->input('data');

        if ($event === 'checkout.session.paid') {
            $sessionId = $data['id'] ?? null;
            $amount    = $data['total_amount'] ?? null;

            if ($sessionId) {
                // ابحث عن التبرع اللي له نفس session_id
                $donation = Donation::where('payment_session_id', $sessionId)->first();

                if ($donation) {
                    $donation->status = 'paid';
                    $donation->paid_amount = $amount;
                    $donation->save();
                }
            }
        }

        if ($event === 'checkout.session.cancelled') {
            $sessionId = $data['id'] ?? null;

            if ($sessionId) {
                $donation = Donation::where('payment_session_id', $sessionId)->first();

                if ($donation) {
                    $donation->status = 'cancelled';
                    $donation->save();
                }
            }
        }

        // رد بـ 200 عشان ثواني يعرف إن السيرفر استقبل الحدث
        return response()->json(['status' => 'ok']);
    }
}
