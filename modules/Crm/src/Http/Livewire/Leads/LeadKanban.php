<?php

namespace Codovision\Crm\Http\Livewire\Leads;

use Codovision\Crm\Http\Livewire\Concerns\InteractsWithCrmAuth;
use Codovision\Crm\Models\Lead;
use Codovision\Crm\Models\LeadStatus;
use Codovision\Crm\Models\LostReason;
use Codovision\Crm\Services\LeadService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Throwable;

class LeadKanban extends Component
{
    use InteractsWithCrmAuth;

    public ?int $movingLeadId = null;
    public string $targetStatusSlug = '';
    public string $lost_reason_id = '';
    public string $lost_notes = '';
    public string $won_amount = '';
    public string $won_currency = 'USD';
    public string $won_closing_date = '';
    public string $won_service = '';
    public string $won_project_type = '';
    public bool $showTransitionModal = false;

    public function moveLead(int $leadId, string $statusSlug): void
    {
        $this->clearCrmFlash();

        if ($statusSlug === '') {
            return;
        }

        try {
            $user = $this->crmUser();
            $lead = Lead::query()->visibleTo($user)->findOrFail($leadId);
            $this->crmAuthorize('update', $lead);

            $status = LeadStatus::where('slug', $statusSlug)->first();
            if (!$status) {
                $this->crmFlashError('Selected status is invalid.');

                return;
            }

            if ($status->is_lost || $status->is_won) {
                $this->movingLeadId = $leadId;
                $this->targetStatusSlug = $statusSlug;
                $this->showTransitionModal = true;
                $this->won_service = $lead->service_interested ?? '';
                $this->won_currency = $lead->currency ?? 'USD';
                $this->won_closing_date = now()->toDateString();
                $this->lost_reason_id = '';
                $this->lost_notes = '';
                $this->won_amount = '';
                $this->won_project_type = '';

                return;
            }

            app(LeadService::class)->updateStatus($lead, $status, $user);
            $this->crmFlashSuccess('Lead moved to ' . $status->name . '.');
        } catch (AuthorizationException $e) {
            $this->crmFlashError($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError('Could not update lead status.');
        }
    }

    public function confirmTransition(LeadService $leadService): void
    {
        $this->clearCrmFlash();

        try {
            $user = $this->crmUser();
            $lead = Lead::query()->visibleTo($user)->findOrFail($this->movingLeadId);
            $this->crmAuthorize('update', $lead);
            $status = LeadStatus::where('slug', $this->targetStatusSlug)->firstOrFail();

            $extra = [];
            if ($status->is_lost) {
                $this->validate(
                    [
                        'lost_reason_id' => ['required', 'exists:crm_lost_reasons,id'],
                        'lost_notes' => ['nullable', 'string', 'max:2000'],
                    ],
                    [
                        'lost_reason_id.required' => 'Please select a lost reason.',
                        'lost_reason_id.exists' => 'Selected lost reason is invalid.',
                    ]
                );
                $extra = [
                    'lost_reason_id' => (int) $this->lost_reason_id,
                    'lost_notes' => $this->lost_notes,
                ];
            }

            if ($status->is_won) {
                $this->validate(
                    [
                        'won_amount' => ['required', 'numeric', 'min:0'],
                        'won_closing_date' => ['required', 'date'],
                        'won_currency' => ['required', 'string', 'size:3'],
                    ],
                    [
                        'won_amount.required' => 'Won amount is required.',
                        'won_amount.numeric' => 'Won amount must be a number.',
                        'won_closing_date.required' => 'Closing date is required.',
                        'won_closing_date.date' => 'Enter a valid closing date.',
                        'won_currency.size' => 'Currency must be a 3-letter code.',
                    ]
                );
                $extra = [
                    'won_amount' => $this->won_amount,
                    'won_currency' => strtoupper($this->won_currency),
                    'won_closing_date' => $this->won_closing_date,
                    'won_service' => $this->won_service,
                    'won_project_type' => $this->won_project_type,
                ];
            }

            $leadService->updateStatus($lead, $status, $user, $extra);
            $this->resetTransition();
            $this->crmFlashSuccess('Lead moved to ' . $status->name . '.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            $this->crmFlashError($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError($e->getMessage() ?: 'Could not complete stage change.');
        }
    }

    public function resetTransition(): void
    {
        $this->showTransitionModal = false;
        $this->movingLeadId = null;
        $this->targetStatusSlug = '';
        $this->lost_reason_id = '';
        $this->lost_notes = '';
        $this->won_amount = '';
        $this->won_project_type = '';
    }

    public function render()
    {
        $user = $this->crmUser();
        $statuses = LeadStatus::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['leads' => function ($q) use ($user) {
                $q->visibleTo($user)->with(['primaryContact', 'company', 'assignee', 'source'])->latest();
            }])
            ->get();

        return view('crm::livewire.leads.lead-kanban', [
            'statuses' => $statuses,
            'lostReasons' => LostReason::where('is_active', true)->orderBy('name')->get(),
        ])->layout('crm::layouts.app');
    }
}
