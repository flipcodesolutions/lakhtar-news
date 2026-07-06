<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\AppNotificationService;
use App\Services\FirebaseNotificationService;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        protected FirebaseNotificationService $firebaseNotification,
        protected AppNotificationService $appNotification
    ) {}

    public function index(Request $request)
    {
        try {
            $perPage = (int) $request->input('per_page', 15);
            $perPage = min(max($perPage, 1), 50);

            $notifications = UserNotification::with('notification')
                ->where('user_id', Auth::id())
                ->orderByDesc('id')
                ->paginate($perPage);

            $data = $notifications->getCollection()->map(function (UserNotification $userNotification) {
                $notification = $userNotification->notification;

                return [
                    'id' => $userNotification->id,
                    'notification_id' => $notification?->id,
                    'title' => $notification?->title,
                    'message' => $notification?->message,
                    'type' => $notification?->type,
                    'reference_type' => $notification?->reference_type,
                    'reference_id' => $notification?->reference_id,
                    'is_read' => $userNotification->is_read,
                    'read_at' => $userNotification->read_at,
                    'created_at' => $userNotification->created_at,
                    'updated_at' => $userNotification->updated_at,
                ];
            });

            return Util::getSuccessMessage('Notifications fetched successfully', [
                'notifications' => $data,
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    public function unreadCount()
    {
        try {
            $count = UserNotification::where('user_id', Auth::id())
                ->where('is_read', false)
                ->count();

            return Util::getSuccessMessage('Unread notification count fetched successfully', [
                'unread_count' => $count,
            ]);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    public function markAsRead($id)
    {
        try {
            $userNotification = UserNotification::where('user_id', Auth::id())
                ->where('id', $id)
                ->first();

            if (! $userNotification) {
                return Util::getErrorMessage('Notification not found');
            }

            if (! $userNotification->is_read) {
                $userNotification->is_read = true;
                $userNotification->read_at = now();
                $userNotification->save();
            }

            return Util::getSuccessMessage('Notification marked as read');
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    public function markAllAsRead()
    {
        try {
            UserNotification::where('user_id', Auth::id())
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            return Util::getSuccessMessage('All notifications marked as read');
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    /**
     * Test endpoint to send a push notification to a device FCM token.
     * Only available when APP_DEBUG=true.
     */
    public function testPushNotification(Request $request)
    {
        if (! config('app.debug')) {
            return Util::getErrorMessage('Test push notifications are disabled in production');
        }

        try {
            $request->validate([
                'fcm_token' => 'nullable|string',
                'title' => 'nullable|string|max:255',
                'body' => 'nullable|string|max:1000',
                'data' => 'nullable|array',
            ]);

            $title = $request->input('title') ?: 'Lakhtar News Test';
            $body = $request->input('body') ?: 'Firebase push notification is working!';
            $data = $request->input('data', []);

            if ($request->filled('fcm_token')) {
                $result = $this->firebaseNotification->testNotification(
                    $request->fcm_token,
                    $title,
                    $body,
                    $data
                );

                if (! $result['success']) {
                    return Util::getErrorMessage($result['message'], [
                        'error' => $result['error'] ?? null,
                        'fcm_error_code' => $result['fcm_error_code'] ?? null,
                        'hint' => $result['hint'] ?? null,
                    ]);
                }

                return Util::getSuccessMessage($result['message'], [
                    'firebase_response' => $result['response'] ?? null,
                    'fcm_token_preview' => substr($request->fcm_token, 0, 20) . '...',
                ]);
            }

            $tokens = User::query()
                ->whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '')
                ->pluck('fcm_token')
                ->unique()
                ->values()
                ->all();

            if ($tokens === []) {
                return Util::getErrorMessage('No FCM tokens found in the database. Store tokens via /api/store-fcm-token or pass a single fcm_token in the request body.');
            }

            $result = $this->firebaseNotification->sendToTokens($tokens, $title, $body, $data);

            return Util::getSuccessMessage('Push notifications sent to all users with FCM tokens', [
                'token_count' => count($tokens),
                'success_count' => $result['success_count'],
                'failure_count' => $result['failure_count'],
            ]);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }
}
