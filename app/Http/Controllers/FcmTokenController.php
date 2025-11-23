<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FcmTokenController extends Controller
{
    /**
     * Store or update FCM token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
            'fcm_token' => 'required|string',
            'platform' => 'required|in:android,ios',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user(); // Get authenticated user (always available since route is protected)

        // Check if token already exists for this device_id
        $fcmToken = FcmToken::where('device_id', $request->device_id)->first();

        if ($fcmToken) {
            // Update existing token
            $fcmToken->update([
                'fcm_token' => $request->fcm_token,
                'platform' => $request->platform,
                'user_id' => $user->id,
            ]);
        } else {
            // Create new token
            $fcmToken = FcmToken::create([
                'device_id' => $request->device_id,
                'fcm_token' => $request->fcm_token,
                'platform' => $request->platform,
                'user_id' => $user->id,
            ]);
        }

        return response()->json([
            'message' => 'FCM token saved successfully',
            'data' => [
                'id' => $fcmToken->id,
                'device_id' => $fcmToken->device_id,
                'platform' => $fcmToken->platform,
                'user_id' => $fcmToken->user_id,
            ],
        ], 200);
    }
}
