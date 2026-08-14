<?php

namespace Codovision\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailMessage extends Model
{
    protected $table = 'crm_mail_messages';

    protected $fillable = [
        'thread_id',
        'lead_id',
        'user_id',
        'direction',
        'folder',
        'message_uid',
        'message_id',
        'in_reply_to',
        'from_email',
        'from_name',
        'to_emails',
        'cc_emails',
        'subject',
        'body_text',
        'body_html',
        'is_seen',
        'is_starred',
        'has_attachments',
        'sent_at',
        'synced_at',
        'send_status',
        'send_error',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'to_emails' => 'array',
            'cc_emails' => 'array',
            'is_seen' => 'boolean',
            'is_starred' => 'boolean',
            'has_attachments' => 'boolean',
            'sent_at' => 'datetime',
            'synced_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(MailThread::class, 'thread_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(CrmUser::class, 'user_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MailAttachment::class, 'message_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(MailAlert::class, 'message_id');
    }

    public function preview(int $limit = 140): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($this->body_text ?: $this->body_html ?: '')) ?? '');

        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit) . '…' : $text;
    }
}
