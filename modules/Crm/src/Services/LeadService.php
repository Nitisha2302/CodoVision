<?php

namespace Codovision\Crm\Services;

use Codovision\Crm\Models\Company;
use Codovision\Crm\Models\Contact;
use Codovision\Crm\Models\CrmUser;
use Codovision\Crm\Models\Lead;
use Codovision\Crm\Models\LeadStatus;
use Codovision\Crm\Models\LeadStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LeadService
{
    public function __construct(
        protected ActivityLogger $logger,
        protected AssignmentService $assignmentService,
    ) {
    }

    public function findDuplicates(?string $email, ?string $phone, ?string $companyName, ?int $ignoreLeadId = null): array
    {
        $matches = Lead::query()
            ->with(['primaryContact', 'company'])
            ->when($ignoreLeadId, fn ($q) => $q->where('id', '!=', $ignoreLeadId))
            ->where(function ($q) use ($email, $phone, $companyName) {
                if ($email) {
                    $q->orWhereHas('primaryContact', fn ($c) => $c->where('email', $email));
                }
                if ($phone) {
                    $q->orWhereHas('primaryContact', fn ($c) => $c->where('phone', $phone));
                }
                if ($companyName) {
                    $q->orWhereHas('company', fn ($c) => $c->whereRaw('LOWER(name) = ?', [Str::lower($companyName)]));
                }
            })
            ->limit(10)
            ->get();

        return $matches->all();
    }

    public function create(array $data, CrmUser $actor): Lead
    {
        return DB::transaction(function () use ($data, $actor) {
            $duplicates = $this->findDuplicates(
                $data['contact_email'] ?? null,
                $data['contact_phone'] ?? null,
                $data['company_name'] ?? null
            );

            if (!empty($duplicates) && empty($data['force_create'])) {
                throw ValidationException::withMessages([
                    'contact_email' => 'Possible duplicate lead found. Confirm to create anyway.',
                    'duplicates' => collect($duplicates)->pluck('lead_code')->implode(', '),
                ]);
            }

            $company = null;
            if (!empty($data['company_name'])) {
                $company = Company::firstOrCreate(
                    ['name' => $data['company_name']],
                    [
                        'website' => $data['company_website'] ?? null,
                        'country' => $data['company_country'] ?? null,
                        'city' => $data['company_city'] ?? null,
                    ]
                );
            }

            $contact = Contact::create([
                'company_id' => $company?->id,
                'first_name' => $data['contact_first_name'],
                'last_name' => $data['contact_last_name'] ?? null,
                'email' => $data['contact_email'] ?? null,
                'phone' => $data['contact_phone'] ?? null,
                'job_title' => $data['contact_job_title'] ?? null,
                'is_decision_maker' => (bool) ($data['is_decision_maker'] ?? false),
            ]);

            $status = LeadStatus::query()->where('slug', $data['status_slug'] ?? 'new')->first()
                ?? LeadStatus::query()->orderBy('sort_order')->firstOrFail();

            $lead = Lead::create([
                'lead_code' => $this->nextLeadCode(),
                'company_id' => $company?->id,
                'primary_contact_id' => $contact->id,
                'lead_source_id' => $data['lead_source_id'] ?? null,
                'lead_status_id' => $status->id,
                'assigned_to' => $data['assigned_to'] ?? $actor->id,
                'team_id' => $data['team_id'] ?? $actor->team_id,
                'created_by' => $actor->id,
                'title' => $data['title'] ?? ($data['service_interested'] ?? 'New lead'),
                'campaign' => $data['campaign'] ?? null,
                'service_interested' => $data['service_interested'] ?? null,
                'priority' => $data['priority'] ?? 'medium',
                'lead_score' => (int) ($data['lead_score'] ?? 0),
                'probability' => (int) ($data['probability'] ?? 10),
                'budget' => $data['budget'] ?? null,
                'expected_value' => $data['expected_value'] ?? null,
                'currency' => $data['currency'] ?? 'USD',
                'expected_closing_date' => $data['expected_closing_date'] ?? null,
                'existing_site_app' => $data['existing_site_app'] ?? null,
                'tech_requirements' => $data['tech_requirements'] ?? null,
                'timeline' => $data['timeline'] ?? null,
                'decision_maker' => $data['decision_maker'] ?? null,
                'competitor' => $data['competitor'] ?? null,
                'notes' => $data['notes'] ?? null,
                'next_follow_up_at' => $data['next_follow_up_at'] ?? null,
                'follow_up_note' => $data['follow_up_note'] ?? null,
            ]);

            if (empty($data['assigned_to']) && config('crm.assignment.mode') === 'round_robin') {
                $this->assignmentService->roundRobinAssign($lead, $actor);
            } elseif (!empty($data['assigned_to'])) {
                $this->assignmentService->assign($lead, (int) $data['assigned_to'], $actor, 'manual');
            }

            LeadStatusHistory::create([
                'lead_id' => $lead->id,
                'from_status_id' => null,
                'to_status_id' => $status->id,
                'changed_by' => $actor->id,
                'note' => 'Lead created',
            ]);

            $this->logger->logLead($lead, $actor, 'created', 'Lead created', $lead->lead_code);
            $this->logger->audit($actor, 'lead.created', Lead::class, $lead->id, ['lead_code' => $lead->lead_code]);

            return $lead->fresh(['status', 'assignee', 'primaryContact', 'company']);
        });
    }

    public function scheduleFollowUp(Lead $lead, CrmUser $actor, string $followUpAt, ?string $note = null): Lead
    {
        $lead->next_follow_up_at = $followUpAt;
        $lead->follow_up_note = $note;
        $lead->save();

        $this->logger->logLead(
            $lead,
            $actor,
            'follow_up',
            'Follow-up scheduled',
            optional($lead->next_follow_up_at)->format('Y-m-d H:i') . ($note ? ' — ' . $note : '')
        );
        $this->logger->audit($actor, 'lead.follow_up_set', Lead::class, $lead->id, [
            'next_follow_up_at' => optional($lead->next_follow_up_at)->toDateTimeString(),
        ]);

        return $lead->fresh();
    }

    public function updateStatus(Lead $lead, LeadStatus $toStatus, CrmUser $actor, array $extra = []): Lead
    {
        return DB::transaction(function () use ($lead, $toStatus, $actor, $extra) {
            $from = $lead->status;

            if ($toStatus->is_lost) {
                if (empty($extra['lost_reason_id'])) {
                    throw ValidationException::withMessages([
                        'lost_reason_id' => 'Lost reason is required when marking a lead as Lost.',
                    ]);
                }
                $lead->lost_reason_id = $extra['lost_reason_id'];
                $lead->lost_notes = $extra['lost_notes'] ?? null;
                $lead->closed_at = now();
            }

            if ($toStatus->is_won) {
                if (empty($extra['won_amount']) || empty($extra['won_closing_date'])) {
                    throw ValidationException::withMessages([
                        'won_amount' => 'Won amount and closing date are required.',
                    ]);
                }
                $lead->won_amount = $extra['won_amount'];
                $lead->won_currency = $extra['won_currency'] ?? $lead->currency;
                $lead->won_closing_date = $extra['won_closing_date'];
                $lead->won_service = $extra['won_service'] ?? $lead->service_interested;
                $lead->won_project_type = $extra['won_project_type'] ?? null;
                $lead->closed_at = now();
                $lead->probability = 100;
            }

            if ($toStatus->slug === 'contacted' && !$lead->first_contacted_at) {
                $lead->first_contacted_at = now();
            }

            $lead->lead_status_id = $toStatus->id;
            $lead->save();

            LeadStatusHistory::create([
                'lead_id' => $lead->id,
                'from_status_id' => $from?->id,
                'to_status_id' => $toStatus->id,
                'changed_by' => $actor->id,
                'note' => $extra['note'] ?? null,
            ]);

            $this->logger->logLead(
                $lead,
                $actor,
                'status_change',
                'Status changed to ' . $toStatus->name,
                ($from?->name ?? 'None') . ' → ' . $toStatus->name
            );

            $this->logger->audit($actor, 'lead.status_changed', Lead::class, $lead->id, [
                'from' => $from?->slug,
                'to' => $toStatus->slug,
            ]);

            return $lead->fresh(['status', 'assignee', 'primaryContact', 'company']);
        });
    }

    public function addNote(Lead $lead, CrmUser $actor, string $body): void
    {
        $mentions = [];
        if (preg_match_all('/@([\w.\-]+)/', $body, $matches)) {
            $mentions = $matches[1];
        }

        $lead->notesRelation()->create([
            'user_id' => $actor->id,
            'body' => $body,
            'mentions' => $mentions ?: null,
            'is_internal' => true,
        ]);

        $this->logger->logLead($lead, $actor, 'note', 'Internal note added', Str::limit($body, 120));
    }

    protected function nextLeadCode(): string
    {
        $next = (int) Lead::withTrashed()->max('id') + 1;

        return 'LD-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
