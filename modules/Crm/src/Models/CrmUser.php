<?php

namespace Codovision\Crm\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class CrmUser extends Authenticatable
{
    use HasRoles;
    use Notifiable;

    protected $table = 'crm_users';

    protected $guard_name = 'crm';

    protected $fillable = [
        'team_id',
        'name',
        'email',
        'password',
        'phone',
        'is_active',
        'assignment_weight',
        'last_assigned_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_assigned_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('Sales Manager');
    }

    public function isExecutive(): bool
    {
        return $this->hasRole('Sales Executive');
    }

    public function isViewer(): bool
    {
        return $this->hasRole('Viewer');
    }

    public function canManageUsers(): bool
    {
        return $this->can('crm.users.manage');
    }

    public function canExportLeads(): bool
    {
        return $this->can('crm.leads.export');
    }

    public function canReassignLeads(): bool
    {
        return $this->can('crm.leads.reassign');
    }

    public function canDeleteLeads(): bool
    {
        return $this->can('crm.leads.delete');
    }
}
