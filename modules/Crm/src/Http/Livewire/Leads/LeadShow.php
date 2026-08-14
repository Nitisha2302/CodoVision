<?php

namespace Codovision\Crm\Http\Livewire\Leads;

use Codovision\Crm\Http\Livewire\Concerns\InteractsWithCrmAuth;
use Codovision\Crm\Models\CrmUser;
use Codovision\Crm\Models\Lead;
use Codovision\Crm\Models\LeadChangeAlert;
use Codovision\Crm\Models\LeadStatus;
use Codovision\Crm\Models\MailThread;
use Codovision\Crm\Services\ActivityLogger;
use Codovision\Crm\Services\AssignmentService;
use Codovision\Crm\Services\LeadService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Throwable;

class LeadShow extends Component
{
    use InteractsWithCrmAuth;

    public Lead $lead;
    public string $noteBody = '';
    public string $assignTo = '';
    public string $followUpAt = '';
    public string $followUpNote = '';

    public function mount(Lead|int $lead, ActivityLogger $logger): void
    {
        $user = $this->crmUser();
        $leadId = $lead instanceof Lead ? $lead->id : $lead;

        $this->lead = Lead::query()
            ->visibleTo($user)
            ->with([
                'status', 'source', 'assignee', 'company', 'primaryContact',
                'notesRelation.author', 'activities.user', 'statusHistory.toStatus', 'statusHistory.actor', 'lostReason',
            ])
            ->findOrFail($leadId);

        $this->crmAuthorize('view', $this->lead);
        $this->assignTo = (string) ($this->lead->assigned_to ?? '');
        $this->followUpAt = optional($this->lead->next_follow_up_at)->format('Y-m-d\TH:i') ?? '';
        $this->followUpNote = $this->lead->follow_up_note ?? '';

        // Super admin viewing a changed lead marks alerts as read (normal list again).
        if ($user->isAdmin()) {
            $marked = $logger->markLeadAlertsRead($this->lead, $user);
            if ($marked > 0) {
                $this->crmFlashSuccess('Marked ' . $marked . ' team change' . ($marked > 1 ? 's' : '') . ' as read.');
            }
        }
    }

    public function saveFollowUp(LeadService $leadService): void
    {
        $this->clearCrmFlash();

        try {
            $this->crmAuthorize('update', $this->lead);
            $this->validate(
                [
                    'followUpAt' => ['required', 'date'],
                    'followUpNote' => ['nullable', 'string', 'max:255'],
                ],
                [
                    'followUpAt.required' => 'Follow-up date and time is required.',
                    'followUpAt.date' => 'Enter a valid follow-up date and time.',
                ]
            );

            $leadService->scheduleFollowUp(
                $this->lead,
                $this->crmUser(),
                $this->followUpAt,
                filled($this->followUpNote) ? $this->followUpNote : null
            );

            $this->lead->refresh()->load(['activities.user']);
            $this->crmFlashSuccess('Follow-up scheduled.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            $this->crmFlashError($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError('Could not save follow-up.');
        }
    }

    public function clearFollowUp(ActivityLogger $logger): void
    {
        $this->clearCrmFlash();

        try {
            $this->crmAuthorize('update', $this->lead);
            $this->lead->next_follow_up_at = null;
            $this->lead->follow_up_note = null;
            $this->lead->save();
            $logger->logLead($this->lead, $this->crmUser(), 'follow_up', 'Follow-up cleared');
            $this->followUpAt = '';
            $this->followUpNote = '';
            $this->lead->refresh()->load(['activities.user']);
            $this->crmFlashSuccess('Follow-up cleared.');
        } catch (AuthorizationException $e) {
            $this->crmFlashError($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError('Could not clear follow-up.');
        }
    }

    public function addNote(LeadService $leadService): void
    {
        $this->clearCrmFlash();

        try {
            $this->crmAuthorize('update', $this->lead);
            $this->validate(
                ['noteBody' => ['required', 'string', 'min:2', 'max:5000']],
                [
                    'noteBody.required' => 'Note cannot be empty.',
                    'noteBody.min' => 'Note must be at least 2 characters.',
                    'noteBody.max' => 'Note is too long (max 5000 characters).',
                ]
            );

            $leadService->addNote($this->lead, $this->crmUser(), $this->noteBody);
            $this->noteBody = '';
            $this->lead->refresh()->load(['notesRelation.author', 'activities.user']);
            $this->crmFlashSuccess('Note added.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            $this->crmFlashError($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError('Could not add note. Please try again.');
        }
    }

    public function reassign(AssignmentService $assignmentService): void
    {
        $this->clearCrmFlash();

        try {
            $this->crmAuthorize('reassign', $this->lead);
            $this->validate(
                ['assignTo' => ['required', 'exists:crm_users,id']],
                [
                    'assignTo.required' => 'Please select a user to assign.',
                    'assignTo.exists' => 'Selected user is invalid.',
                ]
            );

            $assignmentService->assign($this->lead, (int) $this->assignTo, $this->crmUser());
            $this->lead->refresh()->load(['assignee', 'activities.user']);
            $this->crmFlashSuccess('Lead reassigned.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            $this->crmFlashError($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError($e->getMessage() ?: 'Could not reassign lead.');
        }
    }

    public function deleteLead(): mixed
    {
        $this->clearCrmFlash();

        try {
            $this->crmAuthorize('delete', $this->lead);
            $code = $this->lead->lead_code;
            $this->lead->delete();

            return redirect()->route('crm.leads.index')->with('crm_success', "Lead {$code} deleted.");
        } catch (AuthorizationException $e) {
            $this->crmFlashError($e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->crmFlashError('Could not delete lead.');
        }

        return null;
    }

    public function render()
    {
        $user = $this->crmUser();
        $unreadAlerts = $user->isAdmin()
            ? LeadChangeAlert::query()->where('lead_id', $this->lead->id)->unread()->count()
            : 0;

        $mailThreads = collect();
        if ($user->can('crm.mailbox.view')) {
            $mailThreads = MailThread::query()
                ->visibleTo($user)
                ->where('lead_id', $this->lead->id)
                ->with(['latestMessage.attachments'])
                ->orderByDesc('last_message_at')
                ->limit(10)
                ->get();
        }

        return view('crm::livewire.leads.lead-show', [
            'statuses' => LeadStatus::orderBy('sort_order')->get(),
            'users' => CrmUser::where('is_active', true)->orderBy('name')->get(),
            'canReassign' => $user->canReassignLeads(),
            'canDelete' => $user->canDeleteLeads(),
            'canMailbox' => $user->can('crm.mailbox.view'),
            'canSendMail' => $user->can('crm.mailbox.send'),
            'followUpState' => $this->lead->followUpState(),
            'unreadAlerts' => $unreadAlerts,
            'mailThreads' => $mailThreads,
        ])->layout('crm::layouts.app');
    }
}
