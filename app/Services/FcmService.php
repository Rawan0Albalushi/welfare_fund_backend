<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FcmService
{
    public function sendToToken($token, $title, $body, array $data = [])
    {
        $messaging = Firebase::messaging();

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        return $messaging->send($message);
    }

    /**
     * Send notification to multiple tokens
     *
     * @param array $tokens Array of FCM tokens
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data
     * @return array Results with successful and failed tokens
     */
    public function sendToMultipleTokens(array $tokens, $title, $body, array $data = []): array
    {
        if (empty($tokens)) {
            return [
                'successful' => [],
                'failed' => [],
            ];
        }

        $messaging = Firebase::messaging();

        // Create a CloudMessage with notification and data
        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        // Send multicast - the SDK will create individual messages for each token
        $report = $messaging->sendMulticast($message, $tokens);

        // Get successful tokens using validTokens() method
        $successfulTokens = $report->validTokens();

        // Get failed tokens by extracting from failure reports
        $failedTokens = [];
        foreach ($report->failures()->getItems() as $failureReport) {
            $target = $failureReport->target();
            if ($target->type() === MessageTarget::TOKEN) {
                $failedTokens[] = $target->value();
            }
        }

        return [
            'successful' => $successfulTokens,
            'failed' => $failedTokens,
        ];
    }
}

