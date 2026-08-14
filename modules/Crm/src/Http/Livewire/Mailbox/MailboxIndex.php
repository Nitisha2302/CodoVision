<?php

namespace Codovision\Crm\Http\Livewire\Mailbox;

use Codovision\Crm\Http\Livewire\Concerns\InteractsWithCrmAuth;
use Codovision\Crm\Jobs\SyncInboxJob;
use Codovision\Crm\Models\Lead;
use Codovision\Crm\Models\MailAlert;
use Codovision\Crm\Models\MailMessage;
use Codovision\Crm\Models\MailThread;
use Codovision\Crm\Services\MailSendService;
use Codovision\Crm\Services\MailSyncService;
use Codovision\Crm\Support\MailHtml;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;

class MailboxIndex extends Component
{
    use InteractsWithCrmAuth;
    use WithFileUploads;
    use WithPagination;

    public string $folder = 'inbox';
    public string $search = '';
    public bool $showCompose = false;
    public bool $showCc = false;
    public bool $showToolbar = true;

    public string $compose_to = '';
    public string $compose_cc = '';
    public string $compose_subject = '';
    public string $compose_body = '';
    public string $compose_lead_id = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $compose_files = [];

    public function mount(MailSyncService $sync): void
    {
        abort_unless($this->crmUser()->can('crm.mailbox.view'), 403);

        if (request()->boolean('compose')) {
            $this->openCompose(request('to'), request('subject'));
            if (filled(request('lead_id'))) {
                $this->compose_lead_id = (string) request('lead_id');
            }
        }

        if ($sync->configured()) {
            SyncInboxJob::dispatch(25);
        }
    }

    public function pollMailSync(MailSyncService $sync): void
    {
        if (!$sync->configured()) {
            return;
        }

        // Refresh list from DB only; IMAP sync runs in queue:work (avoids timeout screen).
        $lastQueued = \Illuminate\Support\Facades\Cache::get('crm-mail-sync-queued-at');
        if (!$lastQueued || \Carbon\Carbon::parse($lastQueued)->addSeconds(15)->isPast()) {
            \Illuminate\Support\Facades\Cache::put('crm-mail-sync-queued-at', now()->toIso8601String(), now()->addMinutes(5));
            SyncInboxJob::dispatch(25);
        }

        $imported = (int) \Illuminate\Support\Facades\Cache::pull('crm-mail-sync-last-imported', 0);
        if ($imported > 0) {
            $user = $this->crmUser();
            $unread = MailAlert::query()->where('user_id', $user->id)->unread()->count();
            $latest = MailAlert::query()->where('user_id', $user->id)->unread()->latest('id')->first();
            $this->crmFlashSuccess($imported.' new email(s) synced.');
            $this->dispatch('crm-new-mail', count: $unread, added: $imported, summary: (string) ($latest?->summary ?? 'New email'));
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFolder(): void
    {
        $this->resetPage();
    }

    public function openCompose(?string $to = null, ?string $subject = null): void
    {
        $this->showCompose = true;
        $this->showCc = false;
        $this->showToolbar = true;
        $this->compose_to = $to ?? '';
        $this->compose_subject = $subject ?? '';
        $this->compose_body = '';
        $this->compose_cc = '';
        $this->compose_lead_id = $this->compose_lead_id ?: '';
        $this->compose_files = [];
        $this->clearCrmFlash();
        $this->resetValidation();
        $this->dispatch('crm-mail-editor-clear', id: 'rte-compose-body');
    }

    public function closeCompose(): void
    {
        $this->showCompose = false;
        $this->compose_files = [];
        $this->compose_body = '';
    }

    public function discardCompose(): void
    {
        $this->closeCompose();
        $this->crmFlashSuccess('Draft discarded.');
    }

    public function removeComposeFile(int $index): void
    {
        if (!isset($this->compose_files[$index])) {
            return;
        }
        unset($this->compose_files[$index]);
        $this->compose_files = array_values($this->compose_files);
    }

    public function toggleStarThread(int $threadId): void
    {
        $thread = MailThread::query()->visibleTo($this->crmUser())->findOrFail($threadId);
        $latest = $thread->latestMessage;
        if (!$latest) {
            return;
        }
        $latest->is_starred = !$latest->is_starred;
        $latest->save();
    }

    public function archiveThread(int $threadId): void
    {
        $thread = MailThread::query()->visibleTo($this->crmUser())->findOrFail($threadId);
        $thread->is_archived = true;
        $thread->save();
        $this->crmFlashSuccess('Conversation archived.');
    }

    public function markThreadRead(int $threadId, bool $read = true): void
    {
        $user = $this->crmUser();
        $thread = MailThread::query()->visibleTo($user)->findOrFail($threadId);

        MailMessage::query()
            ->where('thread_id', $thread->id)
            ->where('direction', 'inbound')
            ->update(['is_seen' => $read]);

        if ($read) {
            MailAlert::query()
                ->where('user_id', $user->id)
                ->whereIn('message_id', $thread->messages()->pluck('id'))
                ->unread()
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        $thread->refreshCounters();
    }

    public function syncNow(MailSyncService $sync): void
    {
        $this->clearCrmFlash();
        abort_unless($this->crmUser()->can('crm.mailbox.sync'), 403);

        try {
            if (!$sync->configured()) {
                $this->crmFlashError('IMAP is not configured. Add CRM_IMAP_* values in .env, then try again.');

                return;
            }

            SyncInboxJob::dispatch(50);
            $result = $sync->sync(20);
            $msg = "Sync complete: {$result['imported']} new, {$result['skipped']} skipped.";
            if (!empty($result['errors'])) {
                $this->crmFlashError($msg.' '.implode(' ', $result['errors']));
            } else {
                $this->crmFlashSuccess($msg);
            }
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError('Sync failed: '.$e->getMessage());
        }
    }

    public function sendCompose(MailSendService $send): void
    {
        $this->clearCrmFlash();

        try {
            abort_unless($this->crmUser()->can('crm.mailbox.send'), 403);

            if (MailHtml::isEmpty($this->compose_body)) {
                $this->addError('compose_body', 'Message body is required.');

                return;
            }

            $data = $this->validate([
                'compose_to' => ['required', 'string', 'max:500'],
                'compose_cc' => ['nullable', 'string', 'max:500'],
                'compose_subject' => ['required', 'string', 'max:255'],
                'compose_body' => ['required', 'string', 'min:1', 'max:200000'],
                'compose_lead_id' => ['nullable', 'exists:crm_leads,id'],
                'compose_files.*' => ['nullable', 'file', 'max:10240'],
            ], [
                'compose_to.required' => 'Recipient email is required.',
                'compose_subject.required' => 'Subject is required.',
                'compose_files.*.max' => 'Each attachment must be under 10 MB.',
            ]);

            $message = $send->compose(
                $this->crmUser(),
                $data['compose_to'],
                $data['compose_subject'],
                $data['compose_body'],
                filled($data['compose_lead_id'] ?? null) ? (int) $data['compose_lead_id'] : null,
                null,
                $data['compose_cc'] ?? null,
                false,
                $this->compose_files
            );

            $this->showCompose = false;
            $this->compose_files = [];
            $status = $message->send_status === 'sent' ? 'Email sent.' : 'Email saved (status: '.($message->send_status ?: 'unknown').').';
            $this->crmFlashSuccess($status);
            $this->redirect(route('crm.mailbox.thread', $message->thread_id), navigate: true);
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            $this->crmFlashError($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError('Could not send email: '.$e->getMessage());
        }
    }

    public function render(MailSyncService $sync)
    {
        $user = $this->crmUser();
        $base = MailThread::query()->visibleTo($user);

        $threads = (clone $base)
            ->with(['lead.primaryContact', 'assignee', 'latestMessage'])
            ->withCount(['messages as inbound_unread' => fn ($q) => $q->where('direction', 'inbound')->where('is_seen', false)])
            ->when($this->folder === 'inbox', fn ($q) => $q->where('is_archived', false))
            ->when($this->folder === 'archived', fn ($q) => $q->where('is_archived', true))
            ->when($this->folder === 'starred', fn ($q) => $q->whereHas('messages', fn ($m) => $m->where('is_starred', true)))
            ->when($this->folder === 'sent', fn ($q) => $q->where('is_archived', false)->whereHas('messages', fn ($m) => $m->where('direction', 'outbound')))
            ->when($this->search, function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('subject', 'like', $term)
                        ->orWhere('primary_email', 'like', $term)
                        ->orWhereHas('messages', fn ($m) => $m->where('body_text', 'like', $term)->orWhere('from_email', 'like', $term)->orWhere('from_name', 'like', $term));
                });
            })
            ->orderByDesc('last_message_at')
            ->paginate(20);

        $folderCounts = [
            'inbox' => (clone $base)->where('is_archived', false)->where('unread_count', '>', 0)->count(),
            'starred' => (clone $base)->whereHas('messages', fn ($m) => $m->where('is_starred', true))->count(),
            'sent' => (clone $base)->whereHas('messages', fn ($m) => $m->where('direction', 'outbound'))->count(),
            'archived' => (clone $base)->where('is_archived', true)->count(),
        ];

        $unreadMail = MailAlert::query()->where('user_id', $user->id)->unread()->count();

        return view('crm::livewire.mailbox.mailbox-index', [
            'threads' => $threads,
            'leads' => Lead::query()->visibleTo($user)->with('primaryContact')->latest()->limit(100)->get(),
            'imapReady' => $sync->configured(),
            'canSync' => $user->can('crm.mailbox.sync'),
            'canSend' => $user->can('crm.mailbox.send'),
            'unreadMail' => $unreadMail,
            'folderCounts' => $folderCounts,
        ])->layout('crm::layouts.app');
    }
}
