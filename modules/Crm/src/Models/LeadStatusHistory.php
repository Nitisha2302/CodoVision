<?php

namespace Codovision\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadStatusHistory extends Model
{
    protected $table = 'crm_lead_status_history';

    protected $fillable = [
        'lead_id',
        'from_status_id',
        'to_status_id',
        'changed_by',
        'note',
    ];

    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class, 'from_status_id');
    }

    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class, 'to_status_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(CrmUser::class, 'changed_by');
    }
}
