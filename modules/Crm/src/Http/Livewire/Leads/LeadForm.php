<?php

namespace Codovision\Crm\Http\Livewire\Leads;

use Codovision\Crm\Http\Livewire\Concerns\InteractsWithCrmAuth;
use Codovision\Crm\Models\Company;
use Codovision\Crm\Models\CrmUser;
use Codovision\Crm\Models\Lead;
use Codovision\Crm\Models\LeadSource;
use Codovision\Crm\Models\LeadStatus;
use Codovision\Crm\Models\Team;
use Codovision\Crm\Services\ActivityLogger;
use Codovision\Crm\Services\LeadChangeTracker;
use Codovision\Crm\Services\LeadService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Throwable;

class LeadForm extends Component
{
    use InteractsWithCrmAuth;

    public ?int $leadId = null;
    public bool $force_create = false;

    public string $contact_first_name = '';
    public string $contact_last_name = '';
    public string $contact_email = '';
    public string $contact_phone = '';
    public string $contact_job_title = '';
    public bool $is_decision_maker = false;
    public string $company_name = '';
    public string $company_website = '';
    public string $company_country = '';
    public string $company_city = '';
    public string $title = '';
    public string $campaign = '';
    public string $service_interested = '';
    public string $priority = 'medium';
    public string $lead_score = '0';
    public string $probability = '10';
    public string $budget = '';
    public string $expected_value = '';
    public string $currency = 'USD';
    public string $expected_closing_date = '';
    public string $existing_site_app = '';
    public string $tech_requirements = '';
    public string $timeline = '';
    public string $decision_maker = '';
    public string $competitor = '';
    public string $notes = '';
    public string $lead_source_id = '';
    public string $assigned_to = '';
    public string $team_id = '';
    public string $status_slug = 'new';
    public string $next_follow_up_at = '';
    public string $follow_up_note = '';
    public string $duplicateWarning = '';

    protected function rules(): array
    {
        return [
            'contact_first_name' => ['required', 'string', 'min:2', 'max:120'],
            'contact_last_name' => ['nullable', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email', 'max:180'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'contact_job_title' => ['nullable', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'company_website' => ['nullable', 'string', 'max:180'],
            'company_country' => ['nullable', 'string', 'max:100'],
            'company_city' => ['nullable', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:180'],
            'campaign' => ['nullable', 'string', 'max:180'],
            'service_interested' => ['nullable', 'string', 'max:180'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'lead_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'expected_value' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'expected_closing_date' => ['nullable', 'date'],
            'existing_site_app' => ['nullable', 'string', 'max:255'],
            'tech_requirements' => ['nullable', 'string', 'max:5000'],
            'timeline' => ['nullable', 'string', 'max:180'],
            'decision_maker' => ['nullable', 'string', 'max:180'],
            'competitor' => ['nullable', 'string', 'max:180'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'lead_source_id' => ['nullable', 'exists:crm_lead_sources,id'],
            'assigned_to' => ['nullable', 'exists:crm_users,id'],
            'team_id' => ['nullable', 'exists:crm_teams,id'],
            'status_slug' => ['required', 'exists:crm_lead_statuses,slug'],
            'next_follow_up_at' => ['nullable', 'date'],
            'follow_up_note' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function messages(): array
    {
        return [
            'contact_first_name.required' => 'First name is required.',
            'contact_first_name.min' => 'First name must be at least 2 characters.',
            'contact_email.email' => 'Enter a valid email address.',
            'priority.required' => 'Priority is required.',
            'priority.in' => 'Select a valid priority.',
            'lead_score.integer' => 'Lead score must be a whole number.',
            'lead_score.min' => 'Lead score cannot be less than 0.',
            'lead_score.max' => 'Lead score cannot be greater than 100.',
            'probability.integer' => 'Probability must be a whole number.',
            'probability.max' => 'Probability cannot be greater than 100.',
            'budget.numeric' => 'Budget must be a number.',
            'expected_value.numeric' => 'Expected value must be a number.',
            'currency.size' => 'Currency must be a 3-letter code (e.g. USD).',
            'expected_closing_date.date' => 'Enter a valid closing date.',
            'lead_source_id.exists' => 'Selected source is invalid.',
            'assigned_to.exists' => 'Selected assignee is invalid.',
            'team_id.exists' => 'Selected team is invalid.',
            'status_slug.required' => 'Status is required.',
            'status_slug.exists' => 'Selected status is invalid.',
            'next_follow_up_at.date' => 'Enter a valid follow-up date and time.',
            'follow_up_note.max' => 'Follow-up note may not exceed 255 characters.',
        ];
    }

    public function mount(Lead|int|null $lead = null): void
    {
        $user = $this->crmUser();

        if ($lead) {
            $leadId = $lead instanceof Lead ? $lead->id : $lead;
            $model = Lead::query()->visibleTo($user)->with(['primaryContact', 'company', 'status'])->findOrFail($leadId);
            $this->crmAuthorize('update', $model);
            $this->leadId = $model->id;
            $this->fillFromLead($model);
        } else {
            $this->crmAuthorize('create', Lead::class);
            $this->assigned_to = (string) $user->id;
            $this->team_id = (string) ($user->team_id ?? '');
        }
    }

    protected function fillFromLead(Lead $lead): void
    {
        $this->contact_first_name = $lead->primaryContact?->first_name ?? '';
        $this->contact_last_name = $lead->primaryContact?->last_name ?? '';
        $this->contact_email = $lead->primaryContact?->email ?? '';
        $this->contact_phone = $lead->primaryContact?->phone ?? '';
        $this->contact_job_title = $lead->primaryContact?->job_title ?? '';
        $this->is_decision_maker = (bool) ($lead->primaryContact?->is_decision_maker);
        $this->company_name = $lead->company?->name ?? '';
        $this->company_website = $lead->company?->website ?? '';
        $this->company_country = $lead->company?->country ?? '';
        $this->company_city = $lead->company?->city ?? '';
        $this->title = $lead->title ?? '';
        $this->campaign = $lead->campaign ?? '';
        $this->service_interested = $lead->service_interested ?? '';
        $this->priority = $lead->priority ?: 'medium';
        $this->lead_score = (string) ((int) $lead->lead_score);
        $this->probability = (string) ((int) $lead->probability);
        $this->budget = $lead->budget !== null ? (string) $lead->budget : '';
        $this->expected_value = $lead->expected_value !== null ? (string) $lead->expected_value : '';
        $this->currency = $lead->currency ?: 'USD';
        $this->expected_closing_date = optional($lead->expected_closing_date)->toDateString() ?? '';
        $this->existing_site_app = $lead->existing_site_app ?? '';
        $this->tech_requirements = $lead->tech_requirements ?? '';
        $this->timeline = $lead->timeline ?? '';
        $this->decision_maker = $lead->decision_maker ?? '';
        $this->competitor = $lead->competitor ?? '';
        $this->notes = $lead->notes ?? '';
        $this->lead_source_id = (string) ($lead->lead_source_id ?? '');
        $this->assigned_to = (string) ($lead->assigned_to ?? '');
        $this->team_id = (string) ($lead->team_id ?? '');
        $this->status_slug = $lead->status?->slug ?? 'new';
        $this->next_follow_up_at = optional($lead->next_follow_up_at)->format('Y-m-d\TH:i') ?? '';
        $this->follow_up_note = $lead->follow_up_note ?? '';
    }

    public function save(LeadService $leadService, ActivityLogger $logger, LeadChangeTracker $tracker)
    {
        $this->clearCrmFlash();
        $this->duplicateWarning = '';

        try {
            $user = $this->crmUser();
            $data = $this->validate();

            if (blank($this->contact_email) && blank($this->contact_phone)) {
                throw ValidationException::withMessages([
                    'contact_email' => 'Provide at least an email or phone number.',
                    'contact_phone' => 'Provide at least an email or phone number.',
                ]);
            }

            $payload = array_merge($data, [
                'is_decision_maker' => $this->is_decision_maker,
                'force_create' => $this->force_create,
                'lead_source_id' => filled($this->lead_source_id) ? (int) $this->lead_source_id : null,
                'assigned_to' => filled($this->assigned_to) ? (int) $this->assigned_to : null,
                'team_id' => filled($this->team_id) ? (int) $this->team_id : null,
                'budget' => filled($this->budget) ? $this->budget : null,
                'expected_value' => filled($this->expected_value) ? $this->expected_value : null,
                'expected_closing_date' => filled($this->expected_closing_date) ? $this->expected_closing_date : null,
                'lead_score' => (int) ($this->lead_score !== '' ? $this->lead_score : 0),
                'probability' => (int) ($this->probability !== '' ? $this->probability : 10),
                'next_follow_up_at' => filled($this->next_follow_up_at) ? $this->next_follow_up_at : null,
                'follow_up_note' => filled($this->follow_up_note) ? $this->follow_up_note : null,
                'status_slug' => $this->status_slug,
            ]);

            if ($this->leadId) {
                $lead = Lead::query()->visibleTo($user)->with(['primaryContact', 'company', 'status', 'source', 'assignee', 'team'])->findOrFail($this->leadId);
                $this->crmAuthorize('update', $lead);

                $before = $tracker->snapshot($lead);
                $after = $tracker->afterFromPayload($lead, array_merge($payload, [
                    'contact_first_name' => $this->contact_first_name,
                    'contact_last_name' => filled($this->contact_last_name) ? $this->contact_last_name : null,
                    'contact_email' => filled($this->contact_email) ? $this->contact_email : null,
                    'contact_phone' => filled($this->contact_phone) ? $this->contact_phone : null,
                    'contact_job_title' => filled($this->contact_job_title) ? $this->contact_job_title : null,
                    'company_name' => filled($this->company_name) ? $this->company_name : null,
                    'company_website' => filled($this->company_website) ? $this->company_website : null,
                    'company_country' => filled($this->company_country) ? $this->company_country : null,
                    'company_city' => filled($this->company_city) ? $this->company_city : null,
                    'title' => filled($this->title) ? $this->title : $lead->title,
                    'campaign' => filled($this->campaign) ? $this->campaign : null,
                    'service_interested' => filled($this->service_interested) ? $this->service_interested : null,
                    'existing_site_app' => filled($this->existing_site_app) ? $this->existing_site_app : null,
                    'tech_requirements' => filled($this->tech_requirements) ? $this->tech_requirements : null,
                    'timeline' => filled($this->timeline) ? $this->timeline : null,
                    'decision_maker' => filled($this->decision_maker) ? $this->decision_maker : null,
                    'competitor' => filled($this->competitor) ? $this->competitor : null,
                    'notes' => filled($this->notes) ? $this->notes : null,
                ]));
                $changes = $tracker->diff($before, $after);

                $lead->primaryContact?->update([
                    'first_name' => $this->contact_first_name,
                    'last_name' => filled($this->contact_last_name) ? $this->contact_last_name : null,
                    'email' => filled($this->contact_email) ? $this->contact_email : null,
                    'phone' => filled($this->contact_phone) ? $this->contact_phone : null,
                    'job_title' => filled($this->contact_job_title) ? $this->contact_job_title : null,
                    'is_decision_maker' => $this->is_decision_maker,
                ]);

                if (filled($this->company_name)) {
                    if ($lead->company) {
                        $lead->company->update([
                            'name' => $this->company_name,
                            'website' => filled($this->company_website) ? $this->company_website : null,
                            'country' => filled($this->company_country) ? $this->company_country : null,
                            'city' => filled($this->company_city) ? $this->company_city : null,
                        ]);
                    } else {
                        $company = Company::create([
                            'name' => $this->company_name,
                            'website' => filled($this->company_website) ? $this->company_website : null,
                            'country' => filled($this->company_country) ? $this->company_country : null,
                            'city' => filled($this->company_city) ? $this->company_city : null,
                        ]);
                        $lead->company_id = $company->id;
                    }
                }

                $status = LeadStatus::where('slug', $this->status_slug)->first();
                $lead->fill([
                    'title' => filled($this->title) ? $this->title : $lead->title,
                    'campaign' => filled($this->campaign) ? $this->campaign : null,
                    'service_interested' => filled($this->service_interested) ? $this->service_interested : null,
                    'priority' => $this->priority,
                    'lead_score' => $payload['lead_score'],
                    'probability' => $payload['probability'],
                    'budget' => $payload['budget'],
                    'expected_value' => $payload['expected_value'],
                    'currency' => strtoupper($this->currency),
                    'expected_closing_date' => $payload['expected_closing_date'],
                    'existing_site_app' => filled($this->existing_site_app) ? $this->existing_site_app : null,
                    'tech_requirements' => filled($this->tech_requirements) ? $this->tech_requirements : null,
                    'timeline' => filled($this->timeline) ? $this->timeline : null,
                    'decision_maker' => filled($this->decision_maker) ? $this->decision_maker : null,
                    'competitor' => filled($this->competitor) ? $this->competitor : null,
                    'notes' => filled($this->notes) ? $this->notes : null,
                    'lead_source_id' => $payload['lead_source_id'],
                    'team_id' => $payload['team_id'],
                    'assigned_to' => $payload['assigned_to'],
                    'lead_status_id' => $status?->id ?? $lead->lead_status_id,
                    'next_follow_up_at' => $payload['next_follow_up_at'],
                    'follow_up_note' => $payload['follow_up_note'],
                ])->save();

                if (!empty($changes)) {
                    $summary = count($changes) . ' field' . (count($changes) === 1 ? '' : 's') . ' updated';
                    $logger->logLead($lead, $user, 'updated', 'Lead edited', $summary, [
                        'changes' => $changes,
                        'changed_by' => $user->name,
                    ]);
                    $logger->audit($user, 'lead.updated', Lead::class, $lead->id, [
                        'lead_code' => $lead->lead_code,
                        'changes' => $changes,
                    ]);
                }

                return redirect()->route('crm.leads.show', $lead->id)->with(
                    'crm_success',
                    empty($changes) ? 'No changes detected.' : 'Lead updated. ' . count($changes) . ' change(s) logged.'
                );
            }

            $this->crmAuthorize('create', Lead::class);
            $lead = $leadService->create($payload, $user);

            return redirect()->route('crm.leads.show', $lead->id)->with('crm_success', 'Lead created successfully.');
        } catch (ValidationException $e) {
            if (isset($e->errors()['duplicates'])) {
                $this->duplicateWarning = 'Possible duplicates found: ' . implode(', ', $e->errors()['duplicates']) . '. Click create again to force create.';
                $this->force_create = true;
            }
            throw $e;
        } catch (AuthorizationException $e) {
            $this->crmFlashError($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError('Could not save lead. Please review the form and try again.');
        }

        return null;
    }

    public function render()
    {
        return view('crm::livewire.leads.lead-form', [
            'sources' => LeadSource::where('is_active', true)->orderBy('name')->get(),
            'statuses' => LeadStatus::orderBy('sort_order')->get(),
            'users' => CrmUser::where('is_active', true)->orderBy('name')->get(),
            'teams' => Team::where('is_active', true)->orderBy('name')->get(),
            'canReassign' => $this->crmUser()->canReassignLeads(),
        ])->layout('crm::layouts.app');
    }
}
