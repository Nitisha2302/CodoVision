<?php

namespace Codovision\Crm\Http\Livewire\Mailbox;

use Codovision\Crm\Http\Livewire\Concerns\InteractsWithCrmAuth;
use Codovision\Crm\Models\Lead;
use Codovision\Crm\Models\MailAlert;
use Codovision\Crm\Models\MailMessage;
use Codovision\Crm\Models\MailThread;
use Codovision\Crm\Services\MailSendService;
use Codovision\Crm\Support\MailHtml;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class MailThreadShow extends Component
{
    use InteractsWithCrmAuth;
    use WithFileUploads;

    public MailThread $thread;
    public string $reply_body = '';
    public string $reply_cc = '';
    public string $link_lead_id = '';
    public bool $showCc = false;
    public bool $showToolbar = true;
    public bool $showReply = true;

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $reply_files = [];

    public function mount(MailThread|int $thread): void
    {
        $user = $this->crmUser();
        abort_unless($user->can('crm.mailbox.view'), 403);

        $threadId = $thread instanceof MailThread ? $thread->id : $thread;
        $this->thread = MailThread::query()
            ->visibleTo($user)
            ->with(['messages.attachments', 'lead.primaryContact', 'assignee'])
            ->findOrFail($threadId);

        $this->link_lead_id = (string) ($this->thread->lead_id ?? '');

        MailMessage::query()
            ->where('thread_id', $this->thread->id)
            ->where('direction', 'inbound')
            ->where('is_seen', false)
            ->update(['is_seen' => true]);

        MailAlert::query()
            ->where('user_id', $user->id)
            ->whereIn('message_id', $this->thread->messages()->pluck('id'))
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        $this->thread->refreshCounters();
        $this->thread->load(['messages.attachments', 'lead.primaryContact', 'assignee']);
    }

    public function removeReplyFile(int $index): void
    {
        if (!isset($this->reply_files[$index])) {
            return;
        }
        unset($this->reply_files[$index]);
        $this->reply_files = array_values($this->reply_files);
    }

    public function discardReply(): void
    {
        $this->reply_body = '';
        $this->reply_cc = '';
        $this->reply_files = [];
        $this->showCc = false;
        $this->dispatch('crm-mail-editor-clear', id: 'rte-reply-body');
        $this->crmFlashSuccess('Reply discarded.');
    }

    public function reply(MailSendService $send): void
    {
        $this->clearCrmFlash();

        try {
            abort_unless($this->crmUser()->can('crm.mailbox.send'), 403);

            if (MailHtml::isEmpty($this->reply_body)) {
                $this->addError('reply_body', 'Reply message is required.');

                return;
            }

            $this->validate([
                'reply_body' => ['required', 'string', 'min:1', 'max:200000'],
                'reply_cc' => ['nullable', 'string', 'max:500'],
                'reply_files.*' => ['nullable', 'file', 'max:10240'],
            ], [
                'reply_files.*.max' => 'Each attachment must be under 10 MB.',
            ]);

            $to = $this->thread->replyRecipient();
            if (!$to) {
                $this->crmFlashError('No external recipient found. Reply was blocked so it would not send to your own mailbox.');

                return;
            }

            $subject = $this->thread->subject ?: '(no subject)';
            if (!str_starts_with(strtolower($subject), 're:')) {
                $subject = 'Re: '.$subject;
            }

            // Send immediately so status does not stick on "Queued" without a worker.
            $send->compose(
                $this->crmUser(),
                $to,
                $subject,
                $this->reply_body,
                $this->thread->lead_id,
                $this->thread->id,
                $this->reply_cc ?: null,
                false,
                $this->reply_files
            );

            $this->reply_body = '';
            $this->reply_files = [];
            $this->dispatch('crm-mail-editor-clear', id: 'rte-reply-body');
            $this->crmFlashSuccess('Reply sent to '.$to.'.');
            $this->thread->refresh()->load(['messages.attachments', 'lead.primaryContact', 'assignee']);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError('Could not send reply: '.$e->getMessage());
        }
    }

    public function toggleStar(int $messageId): void
    {
        $message = MailMessage::query()
            ->where('thread_id', $this->thread->id)
            ->findOrFail($messageId);
        $message->is_starred = !$message->is_starred;
        $message->save();
        $this->thread->load(['messages.attachments']);
    }

    public function toggleArchive(): void
    {
        $this->thread->is_archived = !$this->thread->is_archived;
        $this->thread->save();
        $this->crmFlashSuccess($this->thread->is_archived ? 'Thread archived.' : 'Thread moved back to inbox.');
    }

    public function linkLead(): void
    {
        $this->validate([
            'link_lead_id' => ['nullable', 'exists:crm_leads,id'],
        ]);

        $lead = filled($this->link_lead_id) ? Lead::query()->visibleTo($this->crmUser())->find($this->link_lead_id) : null;
        $this->thread->lead_id = $lead?->id;
        $this->thread->assigned_to = $lead?->assigned_to ?: $this->thread->assigned_to;
        $this->thread->save();

        MailMessage::query()->where('thread_id', $this->thread->id)->update(['lead_id' => $lead?->id]);
        $this->thread->load(['lead.primaryContact']);
        $this->crmFlashSuccess($lead ? 'Thread linked to lead '.$lead->lead_code.'.' : 'Lead link cleared.');
    }

    public function render()
    {
        return view('crm::livewire.mailbox.mail-thread-show', [
            'leads' => Lead::query()->visibleTo($this->crmUser())->with('primaryContact')->latest()->limit(100)->get(),
            'canSend' => $this->crmUser()->can('crm.mailbox.send'),
        ])->layout('crm::layouts.app');
    }
}
