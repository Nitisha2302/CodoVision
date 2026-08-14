<?php

namespace Codovision\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadAssignment extends Model
{
    protected $table = 'crm_lead_assignments';

    protected $fillable = [
        'lead_id',
        'assigned_from',
        'assigned_to',
        'assigned_by',
        'method',
        'note',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }
}
