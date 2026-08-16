<?php

namespace Codovision\Crm\Jobs;

use Codovision\Crm\Services\MailSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class SyncInboxJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public int $timeout = 240;

    /** Prevent hundreds of duplicate sync jobs from mailbox polling. */
    public int $uniqueFor = 55;

    public function __construct(public int $limit = 40)
    {
    }

    public function uniqueId(): string
    {
        return 'crm-mail-sync-inbox';
    }

    public function handle(MailSyncService $sync): void
    {
        // Always pull recent + unseen (GoDaddy marks opened mail as SEEN).
        $result = $sync->syncUnseenQuick($this->limit, 10);

        $lastFull = Cache::get('crm-mail-sync-last-at');
        $needsFull = !$lastFull || \Carbon\Carbon::parse($lastFull)->addMinutes(3)->isPast();

        if ($needsFull) {
            $full = $sync->syncIfDue($this->limit, 60);
            if (($full['ran'] ?? false)) {
                $result = $full;
            }
        }

        if (($result['ran'] ?? false) && ($result['imported'] ?? 0) > 0) {
            Cache::put('crm-mail-sync-last-imported', (int) $result['imported'], now()->addMinutes(5));
        }
    }
}
