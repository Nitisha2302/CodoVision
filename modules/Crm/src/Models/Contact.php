<?php

namespace Codovision\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $table = 'crm_contacts';

    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'job_title',
        'is_decision_maker',
    ];

    protected function casts(): array
    {
        return [
            'is_decision_maker' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function fullName(): string
    {
        return trim($this->first_name . ' ' . ($this->last_name ?? ''));
    }
}
