<?php

namespace Codovision\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    protected $table = 'crm_notes';

    protected $fillable = [
        'lead_id',
        'user_id',
        'body',
        'mentions',
        'is_internal',
    ];

    protected function casts(): array
    {
        return [
            'mentions' => 'array',
            'is_internal' => 'boolean',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(CrmUser::class, 'user_id');
    }
}
