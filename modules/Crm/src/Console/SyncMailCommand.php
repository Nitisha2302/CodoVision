<?php

namespace Codovision\Crm\Console;

use Codovision\Crm\Jobs\SyncInboxJob;
use Codovision\Crm\Services\MailSyncService;
use Illuminate\Console\Command;

class SyncMailCommand extends Command
{
    protected $signature = 'crm:mail-sync {--sync : Run synchronously instead of queue} {--limit=40}';

    protected $description = 'Sync CRM mailbox inbox via IMAP (credentials from .env only)';

    public function handle(MailSyncService $syncService): int
    {
        $limit = (int) $this->option('limit');

        if ($this->option('sync')) {
            $result = $syncService->sync($limit);
            $this->info("Imported {$result['imported']}, skipped {$result['skipped']}.");
            foreach ($result['errors'] as $error) {
                $this->warn($error);
            }

            return empty($result['errors']) ? self::SUCCESS : self::FAILURE;
        }

        SyncInboxJob::dispatch($limit);
        $this->info('Queued CRM inbox sync job.');

        return self::SUCCESS;
    }
}
