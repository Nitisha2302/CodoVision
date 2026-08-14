<?php

namespace Codovision\Crm\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $table = 'crm_leads';

    protected $fillable = [
        'lead_code',
        'company_id',
        'primary_contact_id',
        'lead_source_id',
        'lead_status_id',
        'assigned_to',
        'team_id',
        'created_by',
        'title',
        'campaign',
        'service_interested',
        'priority',
        'lead_score',
        'probability',
        'budget',
        'expected_value',
        'currency',
        'expected_closing_date',
        'existing_site_app',
        'tech_requirements',
        'timeline',
        'decision_maker',
        'competitor',
        'notes',
        'lost_reason_id',
        'lost_notes',
        'won_amount',
        'won_currency',
        'won_closing_date',
        'won_service',
        'won_project_type',
        'first_contacted_at',
        'next_follow_up_at',
        'follow_up_note',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'expected_value' => 'decimal:2',
            'won_amount' => 'decimal:2',
            'expected_closing_date' => 'date',
            'won_closing_date' => 'date',
            'first_contacted_at' => 'datetime',
            'next_follow_up_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function primaryContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'primary_contact_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class, 'lead_status_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(CrmUser::class, 'assigned_to');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(CrmUser::class, 'created_by');
    }

    public function lostReason(): BelongsTo
    {
        return $this->belongsTo(LostReason::class, 'lost_reason_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(LeadTag::class, 'crm_lead_tag_relations', 'lead_id', 'lead_tag_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LeadAssignment::class, 'lead_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(LeadStatusHistory::class, 'lead_id')->latest();
    }

    public function notesRelation(): HasMany
    {
        return $this->hasMany(Note::class, 'lead_id')->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'lead_id')->latest();
    }

    public function changeAlerts(): HasMany
    {
        return $this->hasMany(LeadChangeAlert::class, 'lead_id')->latest();
    }

    public function scopeVisibleTo(Builder $query, CrmUser $user): Builder
    {
        if ($user->isAdmin() || $user->can('crm.leads.view_all')) {
            return $query;
        }

        if ($user->isManager() || $user->can('crm.leads.view_team')) {
            return $query->where(function (Builder $q) use ($user) {
                $q->where('team_id', $user->team_id)
                    ->orWhere('assigned_to', $user->id);
            });
        }

        return $query->where('assigned_to', $user->id);
    }

    public function weightedValue(): float
    {
        return (float) $this->expected_value * ((int) $this->probability / 100);
    }

    public function scopeFollowUpsDue(Builder $query): Builder
    {
        // Include leads already due/overdue, plus anything due within the next 1 minute.
        return $query->whereNotNull('next_follow_up_at')
            ->where('next_follow_up_at', '<=', now()->addMinute())
            ->whereNull('closed_at');
    }

    public function scopeFollowUpsUpcoming(Builder $query, int $hours = 48): Builder
    {
        return $query->whereNotNull('next_follow_up_at')
            ->whereBetween('next_follow_up_at', [now()->addMinute()->addSecond(), now()->addHours($hours)])
            ->whereNull('closed_at');
    }

    public function followUpState(): ?string
    {
        if (!$this->next_follow_up_at || $this->closed_at) {
            return null;
        }

        // Due now, overdue, or within the next 1 minute.
        if ($this->next_follow_up_at->lte(now()->addMinute())) {
            return 'overdue';
        }

        if ($this->next_follow_up_at->isToday()) {
            return 'today';
        }

        if ($this->next_follow_up_at->lte(now()->addDay())) {
            return 'soon';
        }

        return 'scheduled';
    }
}
