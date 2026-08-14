<?php

namespace Codovision\Crm\Models;

use Illuminate\Database\Eloquent\Model;

class LostReason extends Model
{
    protected $table = 'crm_lost_reasons';

    protected $fillable = ['name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
