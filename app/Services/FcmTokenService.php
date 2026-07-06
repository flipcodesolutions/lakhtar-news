<?php

namespace App\Services;

use App\Models\User;
use App\Support\FirebaseCredentials;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class FcmTokenService
{
    public function syncToken(User $user, string $fcmToken): void
    {
        $fcmToken = trim($fcmToken);

        if ($fcmToken === '') {
            return;
        }

        if ($user->fcm_token !== $fcmToken) {
            $user->forceFill(['fcm_token' => $fcmToken])->save();
        }

        $this->subscribeUserToTopics($user, $fcmToken);
    }

    protected function subscribeUserToTopics(User $user, string $fcmToken): void
    {
        foreach ($this->topicsForUser($user) as $topic) {
            try {
                $this->subscribeToTopic($fcmToken, $topic);

                Log::info('FCM topic subscription succeeded', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'topic' => $topic,
                ]);
            } catch (Throwable $e) {
                Log::warning('FCM topic subscription failed', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'topic' => $topic,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @return list<string>
     */
    protected function topicsForUser(User $user): array
    {
        if (! $user->is_active) {
            return [];
        }

        $topics = [];

        if ($user->role === 'user') {
            $topic = config('services.fcm.users_topic');

            if (is_string($topic) && $topic !== '') {
                $topics[] = $topic;
            }
        }

        if ($user->role === 'reporter') {
            $prefix = config('services.fcm.reporter_topic_prefix', 'reporter_');
            $topics[] = $prefix.$user->id;
        }

        return $topics;
    }

    public function subscribeTokensToTopic(array $fcmTokens, string $topic): void
    {
        $fcmTokens = array_values(array_unique(array_filter(array_map('trim', $fcmTokens))));

        if ($fcmTokens === [] || $topic === '') {
            return;
        }

        $accessToken = $this->getAccessToken();

        foreach (array_chunk($fcmTokens, 1000) as $chunk) {
            $response = Http::withToken($accessToken)
                ->post('https://iid.googleapis.com/iid/v1:batchAdd', [
                    'to' => '/topics/'.$topic,
                    'registration_tokens' => $chunk,
                ]);

            if (! $response->successful()) {
                throw new \RuntimeException(trim($response->body()));
            }
        }
    }

    protected function subscribeToTopic(string $fcmToken, string $topic): void
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post('https://iid.googleapis.com/iid/v1:batchAdd', [
                'to' => '/topics/'.$topic,
                'registration_tokens' => [$fcmToken],
            ]);

        if ($response->successful()) {
            return;
        }

        $legacyResponse = Http::withToken($accessToken)
            ->withBody('', 'application/json')
            ->post(
                'https://iid.googleapis.com/iid/v1/'.urlencode($fcmToken).'/rel/topics/'.urlencode($topic)
            );

        if (! $legacyResponse->successful()) {
            throw new \RuntimeException(trim($legacyResponse->body() ?: $response->body()));
        }
    }

    protected function getAccessToken(): string
    {
        $credentials = FirebaseCredentials::resolve();

        if (is_string($credentials)) {
            $credentials = json_decode((string) file_get_contents($credentials), true);
        }

        if (! is_array($credentials)) {
            throw new \RuntimeException('Firebase credentials are not configured.');
        }

        $auth = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $credentials
        );

        $token = $auth->fetchAuthToken();

        if (! isset($token['access_token'])) {
            throw new \RuntimeException('Unable to fetch Firebase access token.');
        }

        return $token['access_token'];
    }
}
