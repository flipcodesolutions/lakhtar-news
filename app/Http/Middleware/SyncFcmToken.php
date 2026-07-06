<?php

namespace App\Http\Middleware;

use App\Services\FcmTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SyncFcmToken
{
    public function __construct(
        protected FcmTokenService $fcmTokenService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();

        if (! $user) {
            return $response;
        }

        $fcmToken = $request->header('X-FCM-Token')
            ?? $request->input('fcm_token');

        if (! is_string($fcmToken) || trim($fcmToken) === '') {
            return $response;
        }

        $this->fcmTokenService->syncToken($user, $fcmToken);

        return $response;
    }
}
