<?php

namespace Codovision\Crm\Http\Livewire\Leads;

use Codovision\Crm\Http\Livewire\Concerns\InteractsWithCrmAuth;
use Codovision\Crm\Models\Lead;
use Codovision\Crm\Models\LeadSource;
use Codovision\Crm\Models\LeadStatus;
use Codovision\Crm\Services\ActivityLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class LeadIndex extends Component
{
    use InteractsWithCrmAuth;
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $source = '';
    public string $priority = '';

    protected $queryString = ['search', 'status', 'source', 'priority'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSource(): void
    {
        $this->resetPage();
    }

    public function updatingPriority(): void
    {
        $this->resetPage();
    }

    public function export(ActivityLogger $logger): ?StreamedResponse
    {
        $this->clearCrmFlash();

        try {
            $user = $this->crmUser();
            if (!$user->canExportLeads()) {
                $this->crmFlashError('You do not have permission to export leads.');

                return null;
            }

            $logger->audit($user, 'leads.exported', null, null, [
                'filters' => [
                    'search' => $this->search,
                    'status' => $this->status,
                    'source' => $this->source,
                    'priority' => $this->priority,
                ],
            ]);

            $filename = 'crm-leads-' . now()->format('Ymd-His') . '.csv';

            return response()->streamDownload(function () use ($user) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Code', 'Title', 'Contact', 'Email', 'Phone', 'Company', 'Status', 'Priority', 'Value', 'Assignee']);

                Lead::query()
                    ->visibleTo($user)
                    ->with(['primaryContact', 'company', 'status', 'assignee'])
                    ->when($this->search, function ($q) {
                        $term = '%' . $this->search . '%';
                        $q->where(function ($inner) use ($term) {
                            $inner->where('lead_code', 'like', $term)
                                ->orWhere('title', 'like', $term)
                                ->orWhere('notes', 'like', $term)
                                ->orWhereHas('primaryContact', fn ($c) => $c->where('email', 'like', $term)->orWhere('phone', 'like', $term)->orWhere('first_name', 'like', $term))
                                ->orWhereHas('company', fn ($c) => $c->where('name', 'like', $term));
                        });
                    })
                    ->when($this->status, fn ($q) => $q->whereHas('status', fn ($s) => $s->where('slug', $this->status)))
                    ->when($this->source, fn ($q) => $q->where('lead_source_id', $this->source))
                    ->when($this->priority, fn ($q) => $q->where('priority', $this->priority))
                    ->orderByDesc('id')
                    ->chunk(200, function ($chunk) use ($out) {
                        foreach ($chunk as $lead) {
                            fputcsv($out, [
                                $lead->lead_code,
                                $lead->title,
                                $lead->primaryContact?->fullName(),
                                $lead->primaryContact?->email,
                                $lead->primaryContact?->phone,
                                $lead->company?->name,
                                $lead->status?->name,
                                $lead->priority,
                                $lead->expected_value,
                                $lead->assignee?->name,
                            ]);
                        }
                    });

                fclose($out);
            }, $filename, ['Content-Type' => 'text/csv']);
        } catch (AuthorizationException $e) {
            $this->crmFlashError($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError('Could not export leads.');
        }

        return null;
    }

    public function render()
    {
        $user = $this->crmUser();

        $leads = Lead::query()
            ->visibleTo($user)
            ->with(['primaryContact', 'company', 'status', 'assignee'])
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('lead_code', 'like', $term)
                        ->orWhere('title', 'like', $term)
                        ->orWhere('notes', 'like', $term)
                        ->orWhereHas('primaryContact', fn ($c) => $c->where('email', 'like', $term)->orWhere('phone', 'like', $term)->orWhere('first_name', 'like', $term)->orWhere('last_name', 'like', $term))
                        ->orWhereHas('company', fn ($c) => $c->where('name', 'like', $term));
                });
            })
            ->when($this->status, fn ($q) => $q->whereHas('status', fn ($s) => $s->where('slug', $this->status)))
            ->when($this->source, fn ($q) => $q->where('lead_source_id', $this->source))
            ->when($this->priority, fn ($q) => $q->where('priority', $this->priority))
            ->latest()
            ->paginate(12);

        return view('crm::livewire.leads.lead-index', [
            'leads' => $leads,
            'statuses' => LeadStatus::orderBy('sort_order')->get(),
            'sources' => LeadSource::where('is_active', true)->orderBy('name')->get(),
            'canExport' => $user->canExportLeads(),
        ])->layout('crm::layouts.app');
    }
}
