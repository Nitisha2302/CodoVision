<?php

namespace Codovision\Crm\Models;

use Codovision\Crm\Support\MailboxAddress;
use Codovision\Crm\Support\MailVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MailThread extends Model
{
    protected $table = 'crm_mail_threads';

    protected $fillable = [
        'subject',
        'participants_hash',
        'lead_id',
        'assigned_to',
        'primary_email',
        'last_message_at',
        'is_archived',
        'message_count',
        'unread_count',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'is_archived' => 'boolean',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(CrmUser::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MailMessage::class, 'thread_id')->orderBy('sent_at')->orderBy('id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(MailMessage::class, 'thread_id')->latestOfMany('sent_at');
    }

    public function scopeVisibleTo(Builder $query, CrmUser $user): Builder
    {
        if ($user->isAdmin() || $user->can('crm.mailbox.view_all')) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where('assigned_to', $user->id)
                ->orWhereHas('lead', fn ($lead) => $lead->visibleTo($user));
        });
    }

    /**
     * Shared info@ mailbox only (hide contact folders / personal Gmail).
     */
    public function scopeInfoMailbox(Builder $query): Builder
    {
        return MailVisibility::scopeThreads($query);
    }

    public function refreshCounters(): void
    {
        $latest = $this->messages()->max('sent_at');

        $this->forceFill([
            'message_count' => $this->messages()->count(),
            'unread_count' => $this->messages()->where('direction', 'inbound')->where('is_seen', false)->count(),
            'last_message_at' => $latest ?: $this->last_message_at,
        ])->save();
    }

    /**
     * External address to reply to — never our own mailbox.
     */
    public function replyRecipient(): ?string
    {
        if ($this->primary_email && !MailboxAddress::isOwn($this->primary_email)) {
            return strtolower($this->primary_email);
        }

        $externalInbound = $this->messages()
            ->where('direction', 'inbound')
            ->whereNotNull('from_email')
            ->latest('sent_at')
            ->latest('id')
            ->get()
            ->first(fn (MailMessage $m) => !MailboxAddress::isOwn($m->from_email));

        if ($externalInbound?->from_email) {
            return strtolower($externalInbound->from_email);
        }

        $outbound = $this->messages()
            ->where('direction', 'outbound')
            ->latest('sent_at')
            ->latest('id')
            ->get();

        foreach ($outbound as $message) {
            foreach ($message->to_emails ?: [] as $email) {
                if (!MailboxAddress::isOwn($email)) {
                    return strtolower($email);
                }
            }
        }

        if ($this->lead?->primaryContact?->email && !MailboxAddress::isOwn($this->lead->primaryContact->email)) {
            return strtolower($this->lead->primaryContact->email);
        }

        return null;
    }

    public function replyParentMessage(): ?MailMessage
    {
        return $this->messages()
            ->where('direction', 'inbound')
            ->whereNotNull('from_email')
            ->latest('sent_at')
            ->latest('id')
            ->get()
            ->first(fn (MailMessage $m) => !MailboxAddress::isOwn($m->from_email))
            ?? $this->messages()->latest('id')->first();
    }
}
