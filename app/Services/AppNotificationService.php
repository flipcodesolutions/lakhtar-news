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
use Kreait\Firebase\Exception\MessagingException;
use Throwable;

class AppNotificationService
{
    public function __construct(
        protected FirebaseNotificationService $firebaseNotification
    ) {}

    public function notifyNewsApproved(News $news): Notification
    {
        $news->loadMissing('user');

        // Send immediately on admin approval. publish_date is not checked for push.
        return $this->dispatchToUsers(
            type: Notification::TYPE_NEWS_APPROVED,
            title: 'New Story Published',
            message: $news->title,
            userIds: $this->getActiveUserIds(),
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

        if (! $news->user_id || $news->user_id === $commenter->id) {
            Log::info('Comment notification skipped: missing owner or commenter is owner', [
                'news_id' => $news->id,
                'owner_id' => $news->user_id,
                'commenter_id' => $commenter->id,
            ]);

            return null;
        }

        if ($news->user?->role !== 'reporter') {
            Log::info('Comment notification skipped: news owner is not a reporter', [
                'news_id' => $news->id,
                'owner_id' => $news->user_id,
                'owner_role' => $news->user?->role,
            ]);

            return null;
        }

        $commenterName = $commenter->name ?: 'Someone';
        $commentPreview = strlen($comment->comment) > 80
            ? substr($comment->comment, 0, 80).'...'
            : $comment->comment;

        return $this->dispatchToUsers(
            type: Notification::TYPE_NEW_COMMENT,
            title: 'New Comment on Your News',
            message: "{$commenterName} commented: {$commentPreview}",
            userIds: [$news->user_id],
            audience: Notification::AUDIENCE_REPORTER,
            referenceType: 'news',
            referenceId: $news->id,
            pushData: [
                'type' => Notification::TYPE_NEW_COMMENT,
                'news_id' => (string) $news->id,
                'comment_id' => (string) $comment->id,
                'reporter_id' => (string) $news->user_id,
            ]
        );
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
        $tokens = $this->getActiveUserFcmTokens();

        if ($tokens === []) {
            Log::warning('Push notification skipped: no user accounts with FCM tokens', [
                'type' => $type,
            ]);

            return;
        }

        $this->sendPushToTokenList($tokens, $title, $message, $data, $type);
    }

    /**
     * Push to specific reporter(s) via per-reporter FCM topic and device token.
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
        $prefix = config('services.fcm.reporter_topic_prefix', 'reporter_');

        foreach ($reporterIds as $reporterId) {
            $topic = $prefix.$reporterId;

            try {
                $this->firebaseNotification->sendToTopic($topic, $title, $message, $data);

                Log::info('Reporter push sent to FCM topic', [
                    'type' => $type,
                    'topic' => $topic,
                    'reporter_id' => $reporterId,
                ]);
            } catch (MessagingException $e) {
                Log::warning('Reporter topic push failed', [
                    'type' => $type,
                    'topic' => $topic,
                    'reporter_id' => $reporterId,
                    'error' => $e->getMessage(),
                ]);
            } catch (Throwable $e) {
                Log::error('Reporter topic push failed', [
                    'type' => $type,
                    'topic' => $topic,
                    'reporter_id' => $reporterId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->sendPushToUsers($reporterIds, $title, $message, $data, $type);
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
        ?string $type = null
    ): void {
        try {
            $tokens = User::query()
                ->whereIn('id', $userIds)
                ->whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '')
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
