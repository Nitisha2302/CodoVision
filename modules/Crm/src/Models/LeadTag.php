<?php

namespace Codovision\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LeadTag extends Model
{
    protected $table = 'crm_lead_tags';

    protected $fillable = ['name', 'color'];

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 'crm_lead_tag_relations', 'lead_tag_id', 'lead_id');
    }
}
