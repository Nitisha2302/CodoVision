<?php

namespace Codovision\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadStatus extends Model
{
    protected $table = 'crm_lead_statuses';

    protected $fillable = [
        'name',
        'slug',
        'color',
        'sort_order',
        'is_won',
        'is_lost',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_won' => 'boolean',
            'is_lost' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'lead_status_id');
    }
}
