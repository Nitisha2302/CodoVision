<?php

namespace Codovision\Crm\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadChangeAlert extends Model
{
    protected $table = 'crm_lead_change_alerts';

    protected $fillable = [
        'lead_id',
        'actor_id',
        'action',
        'summary',
        'meta',
        'is_read',
        'read_by',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(CrmUser::class, 'actor_id');
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(CrmUser::class, 'read_by');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function markRead(CrmUser $reader): void
    {
        $this->forceFill([
            'is_read' => true,
            'read_by' => $reader->id,
            'read_at' => now(),
        ])->save();
    }
}
