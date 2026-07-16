<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Comments;
use App\Models\News;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AppNotificationService
{
    public function __construct(
        protected FirebaseNotificationService $firebaseNotification
    ) {}

    public function notifyNewsApproved(News $news): ?Notification
    {
        $news->loadMissing('user');

        if (! $this->isNewsPublishDateToday($news)) {
            Log::info('News approved push skipped: publish date is not today', [
                'news_id' => $news->id,
                'publish_date' => $news->publish_date?->toDateString(),
            ]);

            return null;
        }

        // Notify active app users only; exclude the author and all reporters/admins.
        $notification = $this->dispatchToUsers(
            type: Notification::TYPE_NEWS_APPROVED,
            title: 'New Story Published',
            message: $news->title,
            userIds: $this->getNewsBroadcastRecipientIds($news),
            audience: Notification::AUDIENCE_USER,
            referenceType: 'news',
            referenceId: $news->id,
            pushData: [
                'type' => Notification::TYPE_NEWS_APPROVED,
                'news_id' => (string) $news->id,
                'slug' => (string) $news->slug,
                'publish_date' => $news->publish_date?->toDateString() ?? '',
            ]
        );

        $news->forceFill(['notification_sent' => true])->save();

        return $notification;
    }

    protected function isNewsPublishDateToday(News $news): bool
    {
        if ($news->publish_date === null) {
            return false;
        }

        $timezone = 'Asia/Kolkata';

        return $news->publish_date->timezone($timezone)->isSameDay(now($timezone));
    }

    public function notifyNewsRejected(News $news): ?Notification
    {
        $news->loadMissing('user');

        if (! $news->user_id || ! $news->user || $news->user->role !== 'reporter') {
            return null;
        }

        $message = $news->reject_reason
            ? "Your news \"{$news->title}\" was rejected. Reason: {$news->reject_reason}"
            : "Your news \"{$news->title}\" was rejected.";

        return $this->dispatchToUsers(
            type: Notification::TYPE_NEWS_REJECTED,
            title: 'News Rejected',
            message: $message,
            userIds: [$news->user_id],
            audience: Notification::AUDIENCE_REPORTER,
            referenceType: 'news',
            referenceId: $news->id,
            pushData: [
                'type' => Notification::TYPE_NEWS_REJECTED,
                'news_id' => (string) $news->id,
            ]
        );
    }

    public function notifyNewAlert(Alert $alert): ?Notification
    {
        $alert->refresh();

        if (! $this->isAlertActive($alert)) {
            Log::info('Alert notification skipped because alert is inactive', [
                'alert_id' => $alert->id,
                'status' => $alert->status,
            ]);

            return null;
        }

        return $this->dispatchToUsers(
            type: Notification::TYPE_NEW_ALERT,
            title: $alert->title,
            message: $alert->details,
            userIds: $this->getActiveUserIds(),
            audience: Notification::AUDIENCE_USER,
            referenceType: 'alert',
            referenceId: $alert->id,
            pushData: [
                'type' => Notification::TYPE_NEW_ALERT,
                'alert_id' => (string) $alert->id,
            ]
        );
    }

    public function notifyNewComment(News $news, Comments $comment, User $commenter): ?Notification
    {
        $news->loadMissing('user');

        $owner = $news->user;
        $ownerId = (int) ($news->user_id ?? 0);
        $commenterId = (int) $commenter->id;

        if ($ownerId === 0 || ! $owner) {
            Log::info('Comment notification skipped: news has no owner', [
                'news_id' => $news->id,
            ]);

            return null;
        }

        if ($ownerId === $commenterId) {
            Log::info('Comment notification skipped: commenter is news owner', [
                'news_id' => $news->id,
                'owner_id' => $ownerId,
            ]);

            return null;
        }

        $commenterName = $commenter->name ?: 'Someone';
        $commentPreview = strlen((string) $comment->comment) > 80
            ? substr((string) $comment->comment, 0, 80).'...'
            : (string) $comment->comment;

        $audience = $owner->role === 'reporter'
            ? Notification::AUDIENCE_REPORTER
            : Notification::AUDIENCE_USER;

        return $this->dispatchToUsers(
            type: Notification::TYPE_NEW_COMMENT,
            title: 'New Comment on Your News',
            message: "{$commenterName} commented: {$commentPreview}",
            userIds: [$ownerId],
            audience: $audience,
            referenceType: 'news',
            referenceId: $news->id,
            pushData: [
                'type' => Notification::TYPE_NEW_COMMENT,
                'news_id' => (string) $news->id,
                'comment_id' => (string) $comment->id,
                'reporter_id' => (string) $ownerId,
                'slug' => (string) ($news->slug ?? ''),
            ]
        );
    }

    public function notifyCommentReported(News $news, Comments $comment): ?Notification
    {
        $news->loadMissing('user');

        if (! $news->user_id) {
            Log::info('Comment reported notification skipped: missing news owner', [
                'news_id' => $news->id,
                'comment_id' => $comment->id,
            ]);

            return null;
        }

        if ($news->user?->role !== 'reporter') {
            Log::info('Comment reported notification skipped: news owner is not a reporter', [
                'news_id' => $news->id,
                'owner_id' => $news->user_id,
                'owner_role' => $news->user?->role,
            ]);

            return null;
        }

        $commentPreview = strlen($comment->comment) > 80
            ? substr($comment->comment, 0, 80).'...'
            : $comment->comment;

        return $this->dispatchToUsers(
            type: Notification::TYPE_COMMENT_REPORTED,
            title: 'Comment Reported on Your News',
            message: "A comment was reported on your news: \"{$commentPreview}\"",
            userIds: [$news->user_id],
            audience: Notification::AUDIENCE_REPORTER,
            referenceType: 'news',
            referenceId: $news->id,
            pushData: [
                'type' => Notification::TYPE_COMMENT_REPORTED,
                'news_id' => (string) $news->id,
                'comment_id' => (string) $comment->id,
                'reporter_id' => (string) $news->user_id,
            ]
        );
    }

    /**
     * Active app users eligible for news publish/approval broadcasts.
     * Reporters and admins are excluded; the news author is also excluded.
     *
     * @return list<int>
     */
    public function getNewsBroadcastRecipientIds(News $news): array
    {
        return User::query()
            ->where('role', 'user')
            ->where('is_active', true)
            ->when($news->user_id, fn ($query) => $query->where('id', '!=', $news->user_id))
            ->pluck('id')
            ->all();
    }

    /**
     * @return list<string>
     */
    public function getNewsBroadcastFcmTokens(News $news): array
    {
        return User::query()
            ->where('role', 'user')
            ->where('is_active', true)
            ->when($news->user_id, fn ($query) => $query->where('id', '!=', $news->user_id))
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    protected function getActiveUserIds(): array
    {
        return User::query()
            ->where('role', 'user')
            ->where('is_active', true)
            ->pluck('id')
            ->all();
    }

    /**
     * @return list<string>
     */
    protected function getActiveUserFcmTokens(): array
    {
        return User::query()
            ->where('role', 'user')
            ->where('is_active', true)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Users eligible for push (any active user with a saved FCM token).
     *
     * @return list<int>
     */
    protected function getUsersWithFcmTokens(): array
    {
        return User::query()
            ->where('role', 'user')
            ->where('is_active', true)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('id')
            ->all();
    }

    protected function isAlertActive(Alert $alert): bool
    {
        return filter_var($alert->status, FILTER_VALIDATE_BOOLEAN);
    }

    public function dispatchToUsers(
        string $type,
        string $title,
        string $message,
        array $userIds,
        string $audience,
        ?string $referenceType = null,
        ?int $referenceId = null,
        array $pushData = []
    ): Notification {
        $userIds = array_values(array_unique(array_filter($userIds)));

        $notification = DB::transaction(function () use (
            $type,
            $title,
            $message,
            $userIds,
            $audience,
            $referenceType,
            $referenceId
        ) {
            $notification = Notification::create([
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'audience' => $audience,
            ]);

            if ($userIds === []) {
                Log::warning('Notification stored without recipients', [
                    'notification_id' => $notification->id,
                    'type' => $type,
                    'audience' => $audience,
                ]);

                return $notification;
            }

            $now = now();
            $rows = array_map(static fn (int $userId) => [
                'notification_id' => $notification->id,
                'user_id' => $userId,
                'is_read' => false,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ], $userIds);

            foreach (array_chunk($rows, 500) as $chunk) {
                UserNotification::insert($chunk);
            }

            Log::info('Notification stored', [
                'notification_id' => $notification->id,
                'type' => $type,
                'recipient_count' => count($userIds),
            ]);

            return $notification;
        });

        if ($userIds !== []) {
            if ($this->isUserBroadcast($audience, $type)) {
                $this->sendUserBroadcastPush($userIds, $title, $message, $pushData, $type);
            } elseif ($audience === Notification::AUDIENCE_REPORTER) {
                $this->sendReporterPush($userIds, $title, $message, $pushData, $type);
            } else {
                $this->sendPushToUsers($userIds, $title, $message, $pushData, $type);
            }
        }

        return $notification;
    }

    protected function isUserBroadcast(string $audience, string $type): bool
    {
        return $audience === Notification::AUDIENCE_USER
            && in_array($type, [Notification::TYPE_NEWS_APPROVED, Notification::TYPE_NEW_ALERT], true);
    }

    protected function sendUserBroadcastPush(
        array $userIds,
        string $title,
        string $message,
        array $data,
        string $type
    ): void {
        if ($userIds === []) {
            Log::warning('Push notification skipped: no eligible recipients', [
                'type' => $type,
            ]);

            return;
        }

        $this->sendPushToUsers($userIds, $title, $message, $data, $type, 'user');
    }

    /**
     * Push to specific reporter(s).
     * Prefer the saved device FCM token; if missing, fall back to that
     * reporter's private topic (reporter_{id}).
     *
     * @param  list<int>  $reporterIds
     */
    protected function sendReporterPush(
        array $reporterIds,
        string $title,
        string $message,
        array $data,
        string $type
    ): void {
        $reporterIds = array_values(array_unique(array_filter(array_map('intval', $reporterIds))));

        if ($reporterIds === []) {
            Log::warning('Push notification skipped: no reporter recipients', [
                'type' => $type,
            ]);

            return;
        }

        // Send by user id only (no role filter) so a valid token is never dropped.
        $reporters = User::query()
            ->whereIn('id', $reporterIds)
            ->get(['id', 'fcm_token', 'role']);

        $tokens = $reporters
            ->pluck('fcm_token')
            ->filter(fn ($token) => is_string($token) && trim($token) !== '')
            ->unique()
            ->values()
            ->all();

        if ($tokens !== []) {
            $this->sendPushToTokenList($tokens, $title, $message, $data, $type);

            return;
        }

        $prefix = (string) config('services.fcm.reporter_topic_prefix', 'reporter_');

        foreach ($reporterIds as $reporterId) {
            $topic = $prefix.$reporterId;

            try {
                $this->firebaseNotification->sendToTopic($topic, $title, $message, $data);

                Log::info('Reporter push sent via topic fallback', [
                    'type' => $type,
                    'reporter_id' => $reporterId,
                    'topic' => $topic,
                ]);
            } catch (Throwable $e) {
                Log::error('Reporter topic push failed', [
                    'type' => $type,
                    'reporter_id' => $reporterId,
                    'topic' => $topic,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @param  list<string>  $tokens
     */
    protected function sendPushToTokenList(
        array $tokens,
        string $title,
        string $message,
        array $data = [],
        ?string $type = null
    ): void {
        $tokens = array_values(array_unique(array_filter($tokens)));

        if ($tokens === []) {
            Log::warning('Push notification skipped: no FCM tokens provided', [
                'type' => $type,
            ]);

            return;
        }

        try {
            $result = $this->firebaseNotification->sendToTokens($tokens, $title, $message, $data);

            Log::info('Push notification multicast finished', [
                'type' => $type,
                'token_count' => count($tokens),
                'success_count' => $result['success_count'],
                'failure_count' => $result['failure_count'],
            ]);
        } catch (Throwable $e) {
            Log::error('Push notification multicast failed', [
                'type' => $type,
                'token_count' => count($tokens),
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendPushToUsers(
        array $userIds,
        string $title,
        string $message,
        array $data = [],
        ?string $type = null,
        ?string $role = null
    ): void {
        try {
            $query = User::query()
                ->whereIn('id', $userIds)
                ->whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '');

            if ($role !== null) {
                $query->where('role', $role);
            }

            $tokens = $query
                ->pluck('fcm_token')
                ->unique()
                ->values()
                ->all();

            if ($tokens === []) {
                Log::warning('Push notification skipped: no FCM tokens for recipients', [
                    'type' => $type,
                    'recipient_ids' => $userIds,
                ]);

                return;
            }

            $this->sendPushToTokenList($tokens, $title, $message, $data, $type);
        } catch (Throwable $e) {
            Log::error('Push notification dispatch failed', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
