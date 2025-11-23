<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use App\Models\NotificationsLog;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct(
        private FcmService $fcmService
    ) {
    }

    /**
     * Send push notifications to all users or selected users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendNotification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'data' => 'nullable|array', // Additional data to send with notification
        ]);

        $title = $validated['title'];
        $body = $validated['body'];
        $userIds = $validated['user_ids'] ?? [];
        $data = $validated['data'] ?? [];

        try {
            // Get FCM tokens based on user_ids
            $query = FcmToken::with('user');
            
            if (!empty($userIds)) {
                // Send to selected users
                $query->whereIn('user_id', $userIds);
            } else {
                // Send to all users (get all tokens)
                $query->whereNotNull('user_id');
            }

            $fcmTokens = $query->get();

            if ($fcmTokens->isEmpty()) {
                return response()->json([
                    'message' => 'No FCM tokens found for the specified users',
                    'data' => [
                        'total_tokens' => 0,
                        'sent' => 0,
                        'failed' => 0,
                    ],
                ], 404);
            }

            // Extract token strings
            $tokens = $fcmTokens->pluck('fcm_token')->toArray();

            // Send notifications using multicast
            $results = $this->fcmService->sendToMultipleTokens($tokens, $title, $body, $data);

            $successfulTokens = $results['successful'];
            $failedTokens = $results['failed'];

            // Create a map of token -> fcmToken record for logging
            $tokenMap = $fcmTokens->keyBy('fcm_token');

            // Log successful notifications
            foreach ($successfulTokens as $token) {
                $fcmTokenRecord = $tokenMap->get($token);
                if ($fcmTokenRecord) {
                    NotificationsLog::create([
                        'title' => $title,
                        'body' => $body,
                        'user_id' => $fcmTokenRecord->user_id,
                        'fcm_token' => $token,
                        'status' => 'sent',
                        'data' => $data,
                    ]);
                }
            }

            // Log failed notifications
            foreach ($failedTokens as $token) {
                $fcmTokenRecord = $tokenMap->get($token);
                if ($fcmTokenRecord) {
                    NotificationsLog::create([
                        'title' => $title,
                        'body' => $body,
                        'user_id' => $fcmTokenRecord->user_id,
                        'fcm_token' => $token,
                        'status' => 'failed',
                        'error_message' => 'Failed to send notification',
                        'data' => $data,
                    ]);
                }
            }

            $totalSent = count($successfulTokens);
            $totalFailed = count($failedTokens);

            return response()->json([
                'message' => 'Notifications sent successfully',
                'data' => [
                    'total_tokens' => count($tokens),
                    'sent' => $totalSent,
                    'failed' => $totalFailed,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error sending notifications: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Failed to send notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
