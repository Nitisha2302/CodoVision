<?php

namespace Codovision\Crm\Services;

use Codovision\Crm\Models\Contact;
use Codovision\Crm\Models\CrmUser;
use Codovision\Crm\Models\Lead;
use Codovision\Crm\Models\MailAlert;
use Codovision\Crm\Models\MailAttachment;
use Codovision\Crm\Models\MailMessage;
use Codovision\Crm\Models\MailThread;
use Codovision\Crm\Support\MailboxAddress;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Message;
use Throwable;

class MailSyncService
{
    public function configured(): bool
    {
        $imap = config('crm.mail.imap');

        return filled($imap['host'] ?? null)
            && filled($imap['username'] ?? null)
            && filled($imap['password'] ?? null);
    }

    /**
     * Lightweight sync for dashboard/mailbox auto-refresh.
     * Skips if another sync ran recently.
     *
     * @return array{imported:int,skipped:int,errors:list<string>,ran:bool}
     */
    public function syncIfDue(int $limit = 25, int $cooldownSeconds = 45): array
    {
        if (!$this->configured()) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => [], 'ran' => false];
        }

        $lock = Cache::lock('crm-mail-sync-lock', 120);
        if (!$lock->get()) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => [], 'ran' => false];
        }

        try {
            $last = Cache::get('crm-mail-sync-last-at');
            if ($last && Carbon::parse($last)->addSeconds($cooldownSeconds)->isFuture()) {
                return ['imported' => 0, 'skipped' => 0, 'errors' => [], 'ran' => false];
            }

            $result = $this->sync($limit);
            $result['ran'] = true;

            return $result;
        } finally {
            $lock->release();
        }
    }

    /**
     * Fast path: only UNSEEN messages from Inbox/Spam/contact folders.
     * Used by live UI polling so new mail appears without a full refresh.
     *
     * @return array{imported:int,skipped:int,errors:list<string>,ran:bool}
     */
    public function syncUnseenQuick(int $limit = 12, int $cooldownSeconds = 8): array
    {
        if (!$this->configured()) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => [], 'ran' => false];
        }

        $lock = Cache::lock('crm-mail-sync-unseen-lock', 60);
        if (!$lock->get()) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => [], 'ran' => false];
        }

        try {
            $last = Cache::get('crm-mail-sync-unseen-last-at');
            if ($last && Carbon::parse($last)->addSeconds($cooldownSeconds)->isFuture()) {
                return ['imported' => 0, 'skipped' => 0, 'errors' => [], 'ran' => false];
            }

            $imported = 0;
            $skipped = 0;
            $errors = [];

            $client = $this->client();
            $client->connect();

            foreach ($this->syncFolders($client) as $folder) {
                $folderName = (string) ($folder->name ?: $folder->path ?: 'INBOX');
                try {
                    $messages = $folder->query()->unseen()->setFetchOrderDesc()->limit($limit, 0)->get();
                } catch (Throwable $e) {
                    Log::warning("CRM unseen quick failed [{$folderName}]: ".$e->getMessage());
                    continue;
                }

                foreach ($messages as $message) {
                    try {
                        if ($this->ingestMessage($message, $folderName)) {
                            $imported++;
                        } else {
                            $skipped++;
                        }
                    } catch (Throwable $e) {
                        $errors[] = $e->getMessage();
                    }
                }
            }

            $client->disconnect();
            Cache::put('crm-mail-sync-unseen-last-at', now()->toIso8601String(), now()->addHour());

            return [
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
                'ran' => true,
            ];
        } catch (Throwable $e) {
            Log::warning('CRM unseen quick sync failed: '.$e->getMessage());

            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => [$e->getMessage()],
                'ran' => false,
            ];
        } finally {
            $lock->release();
        }
    }

    /**
     * @return array{imported:int,skipped:int,errors:list<string>}
     */
    public function sync(int $limit = 40): array
    {
        if (!$this->configured()) {
            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['IMAP is not configured. Set CRM_IMAP_HOST, CRM_IMAP_USERNAME, CRM_IMAP_PASSWORD in .env'],
            ];
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        try {
            $client = $this->client();
            $client->connect();
            $since = now()->subDays(7);

            foreach ($this->syncFolders($client) as $folder) {
                $folderName = (string) ($folder->name ?: $folder->path ?: 'INBOX');
                $perFolder = str_contains(strtolower($folderName), '@')
                    ? max(20, $limit)
                    : max(12, (int) ceil($limit / 2));

                $messages = $this->fetchRecent($folder, $folderName, $perFolder, $since, $errors);
                $seenUids = [];

                foreach ($messages as $message) {
                    $uid = (string) $message->getUid();
                    $dedupeKey = $folderName.'|'.$uid;
                    if ($uid !== '' && isset($seenUids[$dedupeKey])) {
                        continue;
                    }
                    $seenUids[$dedupeKey] = true;

                    try {
                        if ($this->ingestMessage($message, $folderName)) {
                            $imported++;
                        } else {
                            $skipped++;
                        }
                    } catch (Throwable $e) {
                        $errors[] = $e->getMessage();
                        Log::warning('CRM mail ingest failed: '.$e->getMessage());
                    }
                }
            }

            $client->disconnect();
            Cache::put('crm-mail-sync-last-at', now()->toIso8601String(), now()->addHour());
        } catch (Throwable $e) {
            Log::error('CRM IMAP sync failed: '.$e->getMessage());
            $errors[] = 'IMAP sync failed: '.$e->getMessage();
        }

        return compact('imported', 'skipped', 'errors');
    }

    /**
     * @param  list<string>  $errors
     * @return iterable<int, Message>
     */
    protected function fetchRecent(Folder $folder, string $folderName, int $limit, Carbon $since, array &$errors): iterable
    {
        $collected = [];

        try {
            $unseen = $folder->query()->unseen()->setFetchOrderDesc()->limit($limit, 0)->get();
            foreach ($unseen as $message) {
                $collected[(string) $message->getUid()] = $message;
            }
        } catch (Throwable $e) {
            Log::warning("CRM mail unseen query failed [{$folderName}]: ".$e->getMessage());
        }

        try {
            $recent = $folder->query()->since($since)->setFetchOrderDesc()->limit($limit, 0)->get();
            foreach ($recent as $message) {
                $collected[(string) $message->getUid()] = $message;
            }
        } catch (Throwable $e) {
            // Some hosts dislike SINCE; fall back to newest-all.
            try {
                $recent = $folder->query()->all()->setFetchOrderDesc()->limit($limit, 0)->get();
                foreach ($recent as $message) {
                    $collected[(string) $message->getUid()] = $message;
                }
            } catch (Throwable $e2) {
                $errors[] = "IMAP recent query failed [{$folderName}]: ".$e2->getMessage();
                Log::warning("CRM mail recent query failed [{$folderName}]: ".$e2->getMessage());
            }
        }

        return array_values($collected);
    }

    /**
     * Priority: INBOX, Spam, and GoDaddy contact folders (name contains @).
     * Skip Sent/Drafts/Trash and large non-contact folders like Archive.
     *
     * @return list<Folder>
     */
    protected function syncFolders($client): array
    {
        $skip = ['sent', 'drafts', 'trash', 'bin', 'scheduled', 'outbox', 'templates', 'archive'];
        $priority = [];
        $contact = [];

        try {
            foreach ($client->getFolders(false) as $folder) {
                $name = strtolower((string) ($folder->name ?: $folder->path));
                $base = basename(str_replace('\\', '/', $name));
                if (in_array($base, $skip, true) || in_array($name, $skip, true)) {
                    continue;
                }

                $key = strtolower((string) ($folder->path ?: $folder->name));
                if ($base === 'inbox' || $name === 'inbox') {
                    $priority[$key] = $folder;
                } elseif (in_array($base, ['spam', 'junk'], true)) {
                    $priority[$key] = $folder;
                } elseif (str_contains($name, '@')) {
                    $contact[$key] = $folder;
                }
            }
        } catch (Throwable $e) {
            Log::warning('CRM mail folder list failed: '.$e->getMessage());
        }

        if ($priority === [] && $contact === []) {
            try {
                if ($inbox = $client->getFolder('INBOX')) {
                    $priority['inbox'] = $inbox;
                }
            } catch (Throwable $e) {
                Log::warning('CRM mail INBOX open failed: '.$e->getMessage());
            }
        }

        // Contact folders first (GoDaddy conversation filing), then inbox/spam.
        return array_values(array_merge($contact, $priority));
    }

    protected function client()
    {
        $imap = config('crm.mail.imap');
        $encryption = strtolower((string) ($imap['encryption'] ?? 'ssl'));
        if ($encryption === 'null' || $encryption === 'none' || $encryption === '') {
            $encryption = false;
        }

        $cm = new ClientManager([]);

        return $cm->make([
            'host' => $imap['host'],
            'port' => (int) ($imap['port'] ?? 993),
            'encryption' => $encryption,
            'validate_cert' => (bool) ($imap['validate_cert'] ?? true),
            'username' => $imap['username'],
            'password' => $imap['password'],
            'protocol' => 'imap',
        ]);
    }

    protected function ingestMessage(Message $message, string $folder): bool
    {
        $messageId = $this->cleanMessageId((string) ($message->getMessageId() ?: ''));
        $uid = (string) $message->getUid();

        if ($messageId && MailMessage::where('message_id', $messageId)->exists()) {
            return false;
        }

        if (!$messageId && MailMessage::where('folder', $folder)->where('message_uid', $uid)->exists()) {
            return false;
        }

        $from = $message->getFrom()->first();
        $fromEmail = strtolower((string) ($from?->mail ?? ''));
        $fromName = (string) ($from?->personal ?? $fromEmail);
        $to = $this->addresses($message->getTo());
        $cc = $this->addresses($message->getCc());
        $subject = (string) ($message->getSubject() ?: '(no subject)');
        $inReplyTo = $this->cleanMessageId((string) ($message->getInReplyTo() ?: ''));
        $sentAt = optional($message->getDate()?->toDate())->setTimezone(config('app.timezone')) ?? now();
        $bodyText = (string) ($message->getTextBody() ?: '');
        $bodyHtml = (string) ($message->getHTMLBody() ?: '');

        // Skip copies of our own outbound mail that land back in folders.
        if (MailboxAddress::isOwn($fromEmail)) {
            if ($messageId && MailMessage::where('message_id', $messageId)->exists()) {
                return false;
            }

            $existingOutbound = MailMessage::query()
                ->where('direction', 'outbound')
                ->where('subject', $subject)
                ->where('sent_at', '>=', now()->subDays(14))
                ->latest('id')
                ->first();
            if ($existingOutbound) {
                return false;
            }

            return false;
        }

        $lead = $this->matchLead($fromEmail, $to);
        $thread = $this->resolveThread($subject, $fromEmail, $inReplyTo, $lead);

        $mail = MailMessage::create([
            'thread_id' => $thread->id,
            'lead_id' => $lead?->id ?? $thread->lead_id,
            'direction' => 'inbound',
            'folder' => $folder,
            'message_uid' => $uid,
            'message_id' => $messageId ?: ('local-'.$folder.'-'.$uid.'-'.Str::random(8)),
            'in_reply_to' => $inReplyTo ?: null,
            'from_email' => $fromEmail ?: null,
            'from_name' => $fromName ?: null,
            'to_emails' => $to,
            'cc_emails' => $cc,
            'subject' => $subject,
            'body_text' => $bodyText ?: strip_tags($bodyHtml),
            'body_html' => $bodyHtml ?: null,
            'is_seen' => false,
            'has_attachments' => $message->hasAttachments(),
            'sent_at' => $sentAt,
            'synced_at' => now(),
        ]);

        $this->storeAttachments($message, $mail);
        $thread->refreshCounters();
        $mail->setRelation('thread', $thread);
        $this->notifyRecipients($mail, $lead ?? $thread->lead);

        if ($lead) {
            app(ActivityLogger::class)->logLead(
                $lead,
                null,
                'email',
                'Inbound email received',
                Str::limit($subject, 120),
                ['mail_message_id' => $mail->id]
            );
        }

        return true;
    }

    protected function storeAttachments(Message $message, MailMessage $mail): void
    {
        if (!$message->hasAttachments()) {
            return;
        }

        foreach ($message->getAttachments() as $attachment) {
            $filename = $attachment->getName() ?: ('attachment-'.Str::random(6));
            $safe = Str::slug(pathinfo($filename, PATHINFO_FILENAME)) ?: 'file';
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $path = 'crm-mail/'.$mail->id.'/'.$safe.($ext ? '.'.$ext : '');

            Storage::disk('local')->put($path, $attachment->getContent());

            MailAttachment::create([
                'message_id' => $mail->id,
                'filename' => $filename,
                'mime' => $attachment->getContentType() ?: null,
                'size' => strlen($attachment->getContent()),
                'disk' => 'local',
                'path' => $path,
                'content_id' => $attachment->getId() ?: null,
            ]);
        }

        $mail->forceFill(['has_attachments' => true])->save();
    }

    protected function resolveThread(string $subject, string $fromEmail, ?string $inReplyTo, ?Lead $lead): MailThread
    {
        if ($inReplyTo) {
            $parent = MailMessage::where('message_id', $inReplyTo)->first();
            if ($parent) {
                return $parent->thread;
            }
        }

        $normalized = $this->normalizeSubject($subject);
        $existing = MailThread::query()
            ->where('is_archived', false)
            ->where(function ($q) use ($normalized, $fromEmail) {
                $q->where(function ($inner) use ($normalized, $fromEmail) {
                    $inner->where('primary_email', $fromEmail)
                        ->where('subject', 'like', '%'.$normalized.'%');
                })->orWhere(function ($inner) use ($normalized, $fromEmail) {
                    $inner->where('subject', 'like', '%'.$normalized.'%')
                        ->where(function ($p) use ($fromEmail) {
                            $p->where('primary_email', $fromEmail)
                                ->orWhereHas('messages', function ($m) use ($fromEmail) {
                                    $m->where('from_email', $fromEmail)
                                        ->orWhereJsonContains('to_emails', $fromEmail);
                                });
                        });
                });
            })
            ->latest('last_message_at')
            ->first();

        if ($existing) {
            if ($lead && !$existing->lead_id) {
                $existing->lead_id = $lead->id;
                $existing->assigned_to = $lead->assigned_to;
                $existing->save();
            }
            if ($fromEmail && !$existing->primary_email) {
                $existing->primary_email = $fromEmail;
                $existing->save();
            }

            return $existing;
        }

        return MailThread::create([
            'subject' => $subject,
            'participants_hash' => hash('sha256', strtolower($fromEmail.'|'.$normalized)),
            'lead_id' => $lead?->id,
            'assigned_to' => $lead?->assigned_to,
            'primary_email' => $fromEmail ?: null,
            'last_message_at' => now(),
            'message_count' => 0,
            'unread_count' => 0,
        ]);
    }

    protected function matchLead(string $fromEmail, array $toEmails): ?Lead
    {
        if ($fromEmail === '') {
            return null;
        }

        $contact = Contact::query()->whereRaw('LOWER(email) = ?', [$fromEmail])->first();
        if ($contact) {
            $lead = Lead::query()->where('primary_contact_id', $contact->id)->latest('id')->first();
            if ($lead) {
                return $lead;
            }
        }

        return Lead::query()
            ->whereHas('primaryContact', fn ($q) => $q->whereRaw('LOWER(email) = ?', [$fromEmail]))
            ->latest('id')
            ->first();
    }

    protected function notifyRecipients(MailMessage $mail, ?Lead $lead): void
    {
        $userIds = CrmUser::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Admin')->where('guard_name', 'crm'))
            ->pluck('id')
            ->all();

        if ($lead?->assigned_to) {
            $userIds[] = (int) $lead->assigned_to;
        }

        if ($mail->thread?->assigned_to) {
            $userIds[] = (int) $mail->thread->assigned_to;
        }

        $userIds = array_values(array_unique(array_filter($userIds)));
        $summary = 'New mail from '.($mail->from_name ?: $mail->from_email).': '.Str::limit((string) $mail->subject, 80);

        foreach ($userIds as $userId) {
            MailAlert::updateOrCreate(
                ['message_id' => $mail->id, 'user_id' => $userId],
                [
                    'lead_id' => $lead?->id,
                    'summary' => $summary,
                    'is_read' => false,
                    'read_at' => null,
                ]
            );
        }
    }

    protected function addresses($collection): array
    {
        if (!$collection) {
            return [];
        }

        $out = [];
        foreach ($collection as $address) {
            $email = strtolower((string) ($address->mail ?? ''));
            if ($email !== '') {
                $out[] = $email;
            }
        }

        return array_values(array_unique($out));
    }

    protected function cleanMessageId(string $id): string
    {
        return trim(str_replace(['<', '>'], '', $id));
    }

    protected function normalizeSubject(string $subject): string
    {
        return trim(preg_replace('/^(re|fw|fwd)\s*:\s*/i', '', $subject) ?? $subject);
    }
}
