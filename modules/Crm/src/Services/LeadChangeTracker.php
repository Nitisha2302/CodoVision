<?php

namespace Codovision\Crm\Services;

use Codovision\Crm\Models\CrmUser;
use Codovision\Crm\Models\Lead;
use Codovision\Crm\Models\LeadSource;
use Codovision\Crm\Models\LeadStatus;
use Codovision\Crm\Models\Team;
use Illuminate\Support\Carbon;

class LeadChangeTracker
{
    /** @return array<string, mixed> */
    public function snapshot(Lead $lead): array
    {
        $lead->loadMissing(['primaryContact', 'company', 'status', 'source', 'assignee', 'team']);

        return [
            'title' => $lead->title,
            'campaign' => $lead->campaign,
            'service_interested' => $lead->service_interested,
            'priority' => $lead->priority,
            'lead_score' => (string) ((int) $lead->lead_score),
            'probability' => (string) ((int) $lead->probability),
            'budget' => $lead->budget !== null ? (string) $lead->budget : null,
            'expected_value' => $lead->expected_value !== null ? (string) $lead->expected_value : null,
            'currency' => $lead->currency,
            'expected_closing_date' => optional($lead->expected_closing_date)?->toDateString(),
            'existing_site_app' => $lead->existing_site_app,
            'tech_requirements' => $lead->tech_requirements,
            'timeline' => $lead->timeline,
            'decision_maker' => $lead->decision_maker,
            'competitor' => $lead->competitor,
            'notes' => $lead->notes,
            'status' => $lead->status?->name,
            'source' => $lead->source?->name,
            'assignee' => $lead->assignee?->name,
            'team' => $lead->team?->name,
            'next_follow_up_at' => optional($lead->next_follow_up_at)?->format('Y-m-d H:i'),
            'follow_up_note' => $lead->follow_up_note,
            'contact_first_name' => $lead->primaryContact?->first_name,
            'contact_last_name' => $lead->primaryContact?->last_name,
            'contact_email' => $lead->primaryContact?->email,
            'contact_phone' => $lead->primaryContact?->phone,
            'contact_job_title' => $lead->primaryContact?->job_title,
            'is_decision_maker' => $lead->primaryContact?->is_decision_maker ? 'Yes' : 'No',
            'company_name' => $lead->company?->name,
            'company_website' => $lead->company?->website,
            'company_country' => $lead->company?->country,
            'company_city' => $lead->company?->city,
        ];
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return list<array{field:string,label:string,old:mixed,new:mixed}>
     */
    public function diff(array $before, array $after): array
    {
        $labels = $this->labels();
        $changes = [];

        foreach ($labels as $field => $label) {
            $old = $this->normalize($before[$field] ?? null);
            $new = $this->normalize($after[$field] ?? null);

            if ($old === $new) {
                continue;
            }

            $changes[] = [
                'field' => $field,
                'label' => $label,
                'old' => $old === '' || $old === null ? '—' : $old,
                'new' => $new === '' || $new === null ? '—' : $new,
            ];
        }

        return $changes;
    }

    /**
     * Build human "after" snapshot values from form payload when model relations may not be refreshed yet.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function afterFromPayload(Lead $lead, array $payload): array
    {
        $status = !empty($payload['status_slug'])
            ? LeadStatus::where('slug', $payload['status_slug'])->value('name')
            : $lead->status?->name;

        $source = !empty($payload['lead_source_id'])
            ? LeadSource::where('id', $payload['lead_source_id'])->value('name')
            : null;

        $assignee = !empty($payload['assigned_to'])
            ? CrmUser::where('id', $payload['assigned_to'])->value('name')
            : null;

        $team = !empty($payload['team_id'])
            ? Team::where('id', $payload['team_id'])->value('name')
            : null;

        $followUp = null;
        if (!empty($payload['next_follow_up_at'])) {
            try {
                $followUp = Carbon::parse($payload['next_follow_up_at'])->format('Y-m-d H:i');
            } catch (\Throwable) {
                $followUp = (string) $payload['next_follow_up_at'];
            }
        }

        return [
            'title' => $payload['title'] ?? $lead->title,
            'campaign' => $payload['campaign'] ?? null,
            'service_interested' => $payload['service_interested'] ?? null,
            'priority' => $payload['priority'] ?? null,
            'lead_score' => isset($payload['lead_score']) ? (string) $payload['lead_score'] : null,
            'probability' => isset($payload['probability']) ? (string) $payload['probability'] : null,
            'budget' => isset($payload['budget']) && $payload['budget'] !== null ? (string) $payload['budget'] : null,
            'expected_value' => isset($payload['expected_value']) && $payload['expected_value'] !== null ? (string) $payload['expected_value'] : null,
            'currency' => isset($payload['currency']) ? strtoupper((string) $payload['currency']) : null,
            'expected_closing_date' => $payload['expected_closing_date'] ?? null,
            'existing_site_app' => $payload['existing_site_app'] ?? null,
            'tech_requirements' => $payload['tech_requirements'] ?? null,
            'timeline' => $payload['timeline'] ?? null,
            'decision_maker' => $payload['decision_maker'] ?? null,
            'competitor' => $payload['competitor'] ?? null,
            'notes' => $payload['notes'] ?? null,
            'status' => $status,
            'source' => $source,
            'assignee' => $assignee,
            'team' => $team,
            'next_follow_up_at' => $followUp,
            'follow_up_note' => $payload['follow_up_note'] ?? null,
            'contact_first_name' => $payload['contact_first_name'] ?? null,
            'contact_last_name' => $payload['contact_last_name'] ?? null,
            'contact_email' => $payload['contact_email'] ?? null,
            'contact_phone' => $payload['contact_phone'] ?? null,
            'contact_job_title' => $payload['contact_job_title'] ?? null,
            'is_decision_maker' => !empty($payload['is_decision_maker']) ? 'Yes' : 'No',
            'company_name' => $payload['company_name'] ?? null,
            'company_website' => $payload['company_website'] ?? null,
            'company_country' => $payload['company_country'] ?? null,
            'company_city' => $payload['company_city'] ?? null,
        ];
    }

    /** @return array<string, string> */
    protected function labels(): array
    {
        return [
            'title' => 'Title',
            'campaign' => 'Campaign',
            'service_interested' => 'Service interested',
            'priority' => 'Priority',
            'lead_score' => 'Lead score',
            'probability' => 'Probability',
            'budget' => 'Budget',
            'expected_value' => 'Expected value',
            'currency' => 'Currency',
            'expected_closing_date' => 'Expected closing date',
            'existing_site_app' => 'Existing site/app',
            'tech_requirements' => 'Tech requirements',
            'timeline' => 'Timeline',
            'decision_maker' => 'Decision maker',
            'competitor' => 'Competitor',
            'notes' => 'Notes',
            'status' => 'Status',
            'source' => 'Source',
            'assignee' => 'Assignee',
            'team' => 'Team',
            'next_follow_up_at' => 'Follow-up date/time',
            'follow_up_note' => 'Follow-up note',
            'contact_first_name' => 'Contact first name',
            'contact_last_name' => 'Contact last name',
            'contact_email' => 'Contact email',
            'contact_phone' => 'Contact phone',
            'contact_job_title' => 'Contact job title',
            'is_decision_maker' => 'Decision maker (contact)',
            'company_name' => 'Company',
            'company_website' => 'Company website',
            'company_country' => 'Company country',
            'company_city' => 'Company city',
        ];
    }

    protected function normalize(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
