<?php

namespace Codovision\Crm\Services;

use Codovision\Crm\Models\CrmUser;
use Codovision\Crm\Models\Lead;
use Codovision\Crm\Models\MailAttachment;
use Codovision\Crm\Models\MailMessage;
use Codovision\Crm\Models\MailThread;
use Codovision\Crm\Support\MailboxAddress;
use Codovision\Crm\Support\MailHtml;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;

class MailSendService
{
    public function configured(): bool
    {
        $smtp = config('crm.mail.smtp');

        return filled($smtp['host'] ?? null)
            && filled($smtp['username'] ?? null)
            && filled($smtp['from_address'] ?? null);
    }

    public function sendMessage(MailMessage $message): MailMessage
    {
        if (!$this->configured()) {
            $message->forceFill([
                'send_status' => 'failed',
                'send_error' => 'SMTP is not configured. Set CRM_MAIL_* or MAIL_* in .env',
            ])->save();

            return $message;
        }

        try {
            $smtp = config('crm.mail.smtp');
            $to = $message->to_emails ?: [];
            if ($to === []) {
                throw new \RuntimeException('No recipients.');
            }

            $html = $message->body_html ?: nl2br(e($message->body_text ?: ''));
            $attachments = $message->attachments()->get();

            // Use CRM SMTP settings directly (avoid global MAIL_SCHEME conflicts).
            $mailer = new Mailer($this->makeTransport($smtp));
            $email = (new Email())
                ->from(new Address($smtp['from_address'], $smtp['from_name'] ?? 'CodoVision CRM'))
                ->subject($message->subject ?: '(no subject)')
                ->html($html)
                ->text($message->body_text ?: MailHtml::toText($html));

            $email->to(...array_map(fn ($addr) => new Address($addr), $to));

            if (!empty($message->cc_emails)) {
                $email->cc(...array_map(fn ($addr) => new Address($addr), $message->cc_emails));
            }

            // Stable Message-ID so Gmail/Outlook replies can thread back into CRM.
            $messageId = $message->message_id;
            if (!$messageId || str_starts_with($messageId, 'pending-') || str_starts_with($messageId, 'outbound-')) {
                $messageId = Str::uuid().'@codovision.tech';
            }
            $email->getHeaders()->addIdHeader('Message-ID', $messageId);

            if ($message->in_reply_to) {
                $ref = trim($message->in_reply_to, '<>');
                $email->getHeaders()->addTextHeader('In-Reply-To', '<'.$ref.'>');
                $email->getHeaders()->addTextHeader('References', '<'.$ref.'>');
            }

            foreach ($attachments as $attachment) {
                if (!Storage::disk($attachment->disk)->exists($attachment->path)) {
                    continue;
                }
                $email->attachFromPath(
                    Storage::disk($attachment->disk)->path($attachment->path),
                    $attachment->filename,
                    $attachment->mime ?: null
                );
            }

            $mailer->send($email);

            $message->forceFill([
                'send_status' => 'sent',
                'send_error' => null,
                'sent_at' => now(),
                'message_id' => $messageId,
                'synced_at' => now(),
            ])->save();

            $message->thread?->refreshCounters();

            return $message->fresh();
        } catch (Throwable $e) {
            Log::error('CRM mail send failed: '.$e->getMessage());
            $message->forceFill([
                'send_status' => 'failed',
                'send_error' => $e->getMessage(),
            ])->save();

            throw $e;
        }
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function compose(
        CrmUser $actor,
        string $to,
        string $subject,
        string $body,
        ?int $leadId = null,
        ?int $threadId = null,
        ?string $cc = null,
        bool $queue = true,
        array $files = []
    ): MailMessage {
        $toEmails = $this->splitEmails($to);
        $ccEmails = $this->splitEmails((string) $cc);
        $toEmails = array_values(array_filter($toEmails, fn ($email) => !MailboxAddress::isOwn($email)));
        if ($toEmails === []) {
            throw new \InvalidArgumentException('Recipient cannot be your own CRM mailbox. Choose the contact’s email.');
        }

        $lead = $leadId ? Lead::find($leadId) : null;

        $bodyHtml = MailHtml::sanitize($body);
        if (MailHtml::isEmpty($bodyHtml) && !MailHtml::isEmpty($body)) {
            $bodyHtml = nl2br(e(MailHtml::toText($body)));
        }
        $bodyText = MailHtml::toText($bodyHtml);

        if ($bodyText === '') {
            throw new \InvalidArgumentException('Message body is required.');
        }

        if ($threadId) {
            $thread = MailThread::findOrFail($threadId);
            $dirty = false;
            if (!$thread->assigned_to) {
                $thread->assigned_to = $lead?->assigned_to ?: $actor->id;
                $dirty = true;
            }
            if ($lead && !$thread->lead_id) {
                $thread->lead_id = $lead->id;
                $dirty = true;
            }
            if (!$thread->primary_email || MailboxAddress::isOwn($thread->primary_email)) {
                $thread->primary_email = $toEmails[0] ?? $thread->primary_email;
                $dirty = true;
            }
            if ($dirty) {
                $thread->save();
            }
        } else {
            $thread = MailThread::create([
                'subject' => $subject,
                'participants_hash' => hash('sha256', strtolower(($toEmails[0] ?? '').'|'.$subject)),
                'lead_id' => $lead?->id,
                'assigned_to' => $lead?->assigned_to ?: $actor->id,
                'primary_email' => $toEmails[0] ?? null,
                'last_message_at' => now(),
            ]);
        }

        $parent = $threadId
            ? ($thread->replyParentMessage())
            : $thread->messages()->latest('id')->first();

        $message = MailMessage::create([
            'thread_id' => $thread->id,
            'lead_id' => $lead?->id ?? $thread->lead_id,
            'user_id' => $actor->id,
            'direction' => 'outbound',
            'folder' => 'Sent',
            'message_id' => 'pending-'.Str::uuid(),
            'in_reply_to' => $parent?->message_id,
            'from_email' => config('crm.mail.smtp.from_address'),
            'from_name' => config('crm.mail.smtp.from_name'),
            'to_emails' => $toEmails,
            'cc_emails' => $ccEmails,
            'subject' => $subject,
            'body_text' => $bodyText,
            'body_html' => $bodyHtml,
            'is_seen' => true,
            'has_attachments' => $files !== [],
            'sent_at' => now(),
            'send_status' => $queue ? 'queued' : 'pending',
        ]);

        $this->storeUploads($message, $files);
        $thread->refreshCounters();

        if ($lead || $thread->lead) {
            $linked = $lead ?: $thread->lead;
            app(ActivityLogger::class)->logLead(
                $linked,
                $actor,
                'email',
                'Email sent',
                Str::limit($subject, 120),
                ['mail_message_id' => $message->id]
            );
        }

        if ($queue) {
            \Codovision\Crm\Jobs\SendEmailJob::dispatch($message->id);
        } else {
            $this->sendMessage($message);
        }

        return $message->fresh(['thread', 'attachments']);
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    protected function storeUploads(MailMessage $message, array $files): void
    {
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $filename = $file->getClientOriginalName() ?: ('file-'.Str::random(6));
            $safe = Str::slug(pathinfo($filename, PATHINFO_FILENAME)) ?: 'file';
            $ext = $file->getClientOriginalExtension();
            $path = 'crm-mail/'.$message->id.'/'.$safe.($ext ? '.'.$ext : '');
            Storage::disk('local')->put($path, file_get_contents($file->getRealPath()) ?: '');

            MailAttachment::create([
                'message_id' => $message->id,
                'filename' => $filename,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize() ?: 0,
                'disk' => 'local',
                'path' => $path,
            ]);
        }

        if ($files !== []) {
            $message->forceFill(['has_attachments' => true])->save();
        }
    }

    /**
     * Build SMTP transport from CRM config only.
     * GoDaddy SecureServer: smtpout.secureserver.net:465 (SSL) or :587 (STARTTLS).
     */
    protected function makeTransport(array $smtp): EsmtpTransport
    {
        $host = (string) ($smtp['host'] ?? '');
        $port = (int) ($smtp['port'] ?? 587);
        $encryption = strtolower((string) ($smtp['encryption'] ?? 'tls'));
        $useSsl = $encryption === 'ssl' || $port === 465;

        if ($host === '') {
            throw new \RuntimeException('CRM SMTP host is not configured.');
        }

        $transport = new EsmtpTransport($host, $port, $useSsl);
        $transport->setUsername((string) ($smtp['username'] ?? ''));
        $transport->setPassword((string) ($smtp['password'] ?? ''));

        return $transport;
    }

    protected function splitEmails(string $value): array
    {
        return collect(preg_split('/[,;]+/', $value) ?: [])
            ->map(fn ($e) => strtolower(trim($e)))
            ->filter(fn ($e) => $e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }
}
