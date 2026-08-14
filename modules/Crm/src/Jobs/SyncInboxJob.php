<?php

namespace Codovision\Crm\Jobs;

use Codovision\Crm\Services\MailSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class SyncInboxJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public int $timeout = 240;

    public function __construct(public int $limit = 40)
    {
    }

    public function handle(MailSyncService $sync): void
    {
        // Prefer fast unseen pull; fall back to fuller sync periodically.
        $result = $sync->syncUnseenQuick($this->limit, 10);

        $lastFull = Cache::get('crm-mail-sync-last-at');
        $needsFull = !$lastFull || \Carbon\Carbon::parse($lastFull)->addMinutes(2)->isPast();

        if ($needsFull) {
            $full = $sync->syncIfDue($this->limit, 90);
            if (($full['ran'] ?? false)) {
                $result = $full;
            }
        }

        if (($result['ran'] ?? false) && ($result['imported'] ?? 0) > 0) {
            Cache::put('crm-mail-sync-last-imported', (int) $result['imported'], now()->addMinutes(5));
        }
    }
}
