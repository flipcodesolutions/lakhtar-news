<?php

namespace App\Console\Commands;

use App\Jobs\SendScheduledNewsNotificationJob;
use App\Models\News;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledNewsNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:send-scheduled-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch queued jobs for approved news whose publish date has been reached';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dispatchedCount = 0;

        News::query()
            ->eligibleForScheduledNotification()
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($newsItems) use (&$dispatchedCount) {
                foreach ($newsItems as $news) {
                    SendScheduledNewsNotificationJob::dispatch($news->id);
                    $dispatchedCount++;
                }
            });

        if ($dispatchedCount > 0) {
            Log::info('Scheduled news notification jobs dispatched', [
                'count' => $dispatchedCount,
            ]);

            $this->info("Dispatched {$dispatchedCount} scheduled news notification job(s).");
        }

        return self::SUCCESS;
    }
}
