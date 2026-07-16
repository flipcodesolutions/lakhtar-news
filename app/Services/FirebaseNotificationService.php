<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Exception\RuntimeException as FirebaseRuntimeException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Throwable;

class FirebaseNotificationService
{
    public function sendToToken(
        string $fcmToken,
        string $title,
        string $body,
        array $data = []
    ): array {
        $message = CloudMessage::new()
            ->toToken($fcmToken)
            ->withNotification(Notification::create($title, $body));

        if ($data !== []) {
            $message = $message->withData($this->stringifyData($data));
        }

        return $this->sendMessage($message);
    }

    public function sendToTokens(
        array $fcmTokens,
        string $title,
        string $body,
        array $data = []
    ): array {
        $fcmTokens = array_values(array_unique(array_filter($fcmTokens)));

        if ($fcmTokens === []) {
            return [
                'success_count' => 0,
                'failure_count' => 0,
            ];
        }

        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body));

        if ($data !== []) {
            $message = $message->withData($this->stringifyData($data));
        }

        $successCount = 0;
        $failureCount = 0;

        foreach (array_chunk($fcmTokens, 500) as $chunk) {
            $report = Firebase::messaging()->sendMulticast($message, $chunk);
            $successCount += $report->successes()->count();
            $failureCount += $report->failures()->count();

            if ($report->hasFailures()) {
                foreach ($report->failures()->getItems() as $failure) {
                    Log::warning('Multicast push failed for token', [
                        'error' => $failure->error()->getMessage(),
                    ]);
                }
            }
        }

        return [
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ];
    }

    public function sendToTopic(
        string $topic,
        string $title,
        string $body,
        array $data = []
    ): array {
        $message = CloudMessage::new()
            ->toTopic($topic)
            ->withNotification(Notification::create($title, $body));

        if ($data !== []) {
            $message = $message->withData($this->stringifyData($data));
        }

        return $this->sendMessage($message);
    }

    protected function sendMessage(CloudMessage $message): array
    {
        try {
            return Firebase::messaging()->send($message);
        } catch (FirebaseRuntimeException $e) {
            Log::error('Firebase is not configured correctly', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function stringifyData(array $data): array
    {
        $stringData = [];

        foreach ($data as $key => $value) {
            $stringData[(string) $key] = is_scalar($value) || $value === null
                ? (string) $value
                : json_encode($value);
        }

        return $stringData;
    }

    /**
     * @return array{success: bool, message: string, response?: array, error?: string}
     */
    public function testNotification(
        string $fcmToken,
        ?string $title = null,
        ?string $body = null,
        array $data = []
    ): array {
        $title = $title ?: 'Lakhtar News Test';
        $body = $body ?: 'Firebase push notification is working!';

        try {
            $response = $this->sendToToken($fcmToken, $title, $body, $data);

            return [
                'success' => true,
                'message' => 'Push notification sent successfully',
                'response' => $response,
            ];
        } catch (MessagingException $e) {
            $fcmErrorCode = $this->extractFcmErrorCode($e);

            return [
                'success' => false,
                'message' => 'Failed to send push notification',
                'error' => $e->getMessage(),
                'fcm_error_code' => $fcmErrorCode,
                'hint' => $this->getErrorHint($fcmErrorCode),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to send push notification',
                'error' => $e->getMessage(),
                'hint' => 'Check FIREBASE_CREDENTIALS on the server and run php artisan config:clear',
            ];
        }
    }

    private function extractFcmErrorCode(MessagingException $e): ?string
    {
        $details = $e->errors()['error']['details'] ?? [];

        foreach ($details as $detail) {
            if (isset($detail['errorCode'])) {
                return $detail['errorCode'];
            }
        }

        return null;
    }

    private function getErrorHint(?string $fcmErrorCode): ?string
    {
        return match ($fcmErrorCode) {
            'UNREGISTERED' => 'The FCM token is invalid or expired. Open the app to get a fresh token, save it via /api/store-fcm-token, then test again. Also verify the app uses the same Firebase project (lakhtar-news-update) as your server credentials.',
            'INVALID_ARGUMENT' => 'The FCM token format is invalid. Pass the full token from FirebaseMessaging.getToken() on the device.',
            'SENDER_ID_MISMATCH' => 'The app Firebase project does not match the server credentials project (lakhtar-news-update). Update google-services.json / GoogleService-Info.plist to match.',
            default => null,
        };
    }
}
