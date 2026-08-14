<?php

namespace Codovision\Crm\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailAlert extends Model
{
    protected $table = 'crm_mail_alerts';

    protected $fillable = [
        'message_id',
        'user_id',
        'lead_id',
        'summary',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(MailMessage::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(CrmUser::class, 'user_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function markRead(): void
    {
        $this->forceFill([
            'is_read' => true,
            'read_at' => now(),
        ])->save();
    }
}
