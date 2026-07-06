<?php

namespace App\Jobs;

use App\Models\News;
use App\Services\AppNotificationService;
use App\Services\FirebaseNotificationService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SendScheduledNewsNotificationJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var list<int>
     */
    public array $backoff = [60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $newsId
    ) {}

    /**
     * Prevent duplicate jobs for the same news article.
     */
    public function uniqueId(): string
    {
        return 'scheduled-news-notification-' . $this->newsId;
    }

    /**
     * Execute the job.
     */
    public function handle(
        FirebaseNotificationService $firebaseNotification,
        AppNotificationService $appNotification
    ): void {
        $news = DB::transaction(function () {
            $news = News::query()
                ->whereKey($this->newsId)
                ->lockForUpdate()
                ->first();

            if (! $news || $news->notification_sent || ! $news->isEligibleForScheduledNotification()) {
                return null;
            }

            return $news;
        });

        if (! $news) {
            Log::info('Scheduled news notification skipped', [
                'news_id' => $this->newsId,
                'reason' => 'already_sent_or_ineligible',
            ]);

            return;
        }

        $tokens = $appNotification->getNewsBroadcastFcmTokens($news);

        if ($tokens === []) {
            Log::warning('Scheduled news notification skipped: no FCM tokens available', [
                'news_id' => $news->id,
            ]);

            $this->markNotificationSent($news);

            return;
        }

        $title = (string) $news->title;
        $body = Str::limit(trim(strip_tags((string) $news->description)), 240);
        $data = [
            'type' => 'news_published',
            'news_id' => (string) $news->id,
            'slug' => (string) $news->slug,
            'publish_date' => $news->publish_date?->toIso8601String() ?? '',
        ];

        try {
            $result = $firebaseNotification->sendToTokens($tokens, $title, $body, $data);

            Log::info('Scheduled news push notification sent', [
                'news_id' => $news->id,
                'token_count' => count($tokens),
                'success_count' => $result['success_count'],
                'failure_count' => $result['failure_count'],
            ]);

            if ($result['success_count'] === 0) {
                throw new \RuntimeException('Firebase multicast returned zero successful deliveries.');
            }

            $this->markNotificationSent($news);
        } catch (Throwable $e) {
            Log::error('Scheduled news push notification failed', [
                'news_id' => $news->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function markNotificationSent(News $news): void
    {
        $news->forceFill(['notification_sent' => true])->save();

        Log::info('Scheduled news notification marked as sent', [
            'news_id' => $news->id,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Scheduled news notification job failed permanently', [
            'news_id' => $this->newsId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
