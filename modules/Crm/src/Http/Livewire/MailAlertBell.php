<?php

namespace Codovision\Crm\Http\Livewire;

use Codovision\Crm\Http\Livewire\Concerns\InteractsWithCrmAuth;
use Codovision\Crm\Jobs\SyncInboxJob;
use Codovision\Crm\Models\MailAlert;
use Codovision\Crm\Services\MailSyncService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class MailAlertBell extends Component
{
    use InteractsWithCrmAuth;

    public int $unread = 0;
    public int $lastNotified = 0;
    public string $latestSummary = '';

    public function mount(MailSyncService $sync): void
    {
        $user = $this->crmUser();
        if (!$user->can('crm.mailbox.view')) {
            return;
        }

        $this->unread = MailAlert::query()->where('user_id', $user->id)->unread()->count();
        $this->lastNotified = $this->unread;

        if ($sync->configured()) {
            $this->queueSync();
        }
    }

    public function poll(MailSyncService $sync): void
    {
        $user = $this->crmUser();
        if (!$user->can('crm.mailbox.view')) {
            return;
        }

        // Never run IMAP inside the browser request (causes 30s timeout page).
        if ($sync->configured()) {
            $this->queueSync();
        }

        $this->unread = MailAlert::query()->where('user_id', $user->id)->unread()->count();

        $latest = MailAlert::query()
            ->where('user_id', $user->id)
            ->unread()
            ->latest('id')
            ->first();
        $this->latestSummary = (string) ($latest?->summary ?? '');

        if ($this->unread > $this->lastNotified) {
            $added = $this->unread - $this->lastNotified;
            $this->dispatch(
                'crm-new-mail',
                count: $this->unread,
                added: $added,
                summary: $this->latestSummary ?: ($added.' new email(s)')
            );
            $this->lastNotified = $this->unread;
        } elseif ($this->unread < $this->lastNotified) {
            $this->lastNotified = $this->unread;
            $this->dispatch('crm-mail-unread', count: $this->unread);
        } else {
            $this->dispatch('crm-mail-unread', count: $this->unread);
        }
    }

    protected function queueSync(): void
    {
        $lastQueued = Cache::get('crm-mail-sync-queued-at');
        if ($lastQueued && \Carbon\Carbon::parse($lastQueued)->addSeconds(15)->isFuture()) {
            return;
        }

        Cache::put('crm-mail-sync-queued-at', now()->toIso8601String(), now()->addMinutes(5));
        SyncInboxJob::dispatch(25);
    }

    public function render()
    {
        return view('crm::livewire.mail-alert-bell');
    }
}
