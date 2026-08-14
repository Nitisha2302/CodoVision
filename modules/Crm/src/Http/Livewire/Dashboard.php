<?php

namespace Codovision\Crm\Http\Livewire;

use Codovision\Crm\Http\Livewire\Concerns\InteractsWithCrmAuth;
use Codovision\Crm\Models\Lead;
use Codovision\Crm\Models\LeadChangeAlert;
use Codovision\Crm\Models\LeadStatus;
use Codovision\Crm\Models\MailAlert;
use Codovision\Crm\Jobs\SyncInboxJob;
use Codovision\Crm\Services\ActivityLogger;
use Codovision\Crm\Services\MailSyncService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Dashboard extends Component
{
    use InteractsWithCrmAuth;

    public string $mailSyncNote = '';

    public function pollMailSync(MailSyncService $sync): void
    {
        $user = $this->crmUser();
        if (!$user->can('crm.mailbox.view')) {
            return;
        }

        // IMAP stays in the queue worker — polling only refreshes DB alerts (no timeout page).
        if ($sync->configured()) {
            $lastQueued = Cache::get('crm-mail-sync-queued-at');
            if (!$lastQueued || \Carbon\Carbon::parse($lastQueued)->addSeconds(15)->isPast()) {
                Cache::put('crm-mail-sync-queued-at', now()->toIso8601String(), now()->addMinutes(5));
                SyncInboxJob::dispatch(25);
            }
        }

        $imported = (int) Cache::pull('crm-mail-sync-last-imported', 0);
        $unread = MailAlert::query()->where('user_id', $user->id)->unread()->count();

        if ($imported > 0) {
            $latest = MailAlert::query()->where('user_id', $user->id)->unread()->latest('id')->first();
            $this->mailSyncNote = $imported.' new email(s) just arrived.';
            $this->dispatch('crm-new-mail', count: $unread, added: $imported, summary: (string) ($latest?->summary ?? 'New email'));
        }
    }

    public function markAlertRead(int $alertId): void
    {
        $this->clearCrmFlash();
        $user = $this->crmUser();

        if (!$user->isAdmin()) {
            $this->crmFlashError('Only Super Admin can mark lead changes as read.');

            return;
        }

        $alert = LeadChangeAlert::query()->findOrFail($alertId);
        $alert->markRead($user);
        $this->crmFlashSuccess('Change marked as read.');
    }

    public function markAllAlertsRead(): void
    {
        $this->clearCrmFlash();
        $user = $this->crmUser();

        if (!$user->isAdmin()) {
            $this->crmFlashError('Only Super Admin can mark lead changes as read.');

            return;
        }

        $count = LeadChangeAlert::query()->unread()->update([
            'is_read' => true,
            'read_by' => $user->id,
            'read_at' => now(),
        ]);

        $this->crmFlashSuccess($count > 0 ? "Marked {$count} change(s) as read." : 'No unread changes.');
    }

    public function openLeadAndMarkRead(int $leadId, ActivityLogger $logger): mixed
    {
        $user = $this->crmUser();
        $lead = Lead::query()->visibleTo($user)->findOrFail($leadId);

        if ($user->isAdmin()) {
            $logger->markLeadAlertsRead($lead, $user);
        }

        return redirect()->route('crm.leads.show', $lead->id);
    }

    public function markMailAlertRead(int $alertId): void
    {
        $this->clearCrmFlash();
        $user = $this->crmUser();
        $alert = MailAlert::query()->where('user_id', $user->id)->findOrFail($alertId);
        $alert->markRead();
        $this->crmFlashSuccess('Mail alert marked as read.');
    }

    public function markAllMailAlertsRead(): void
    {
        $this->clearCrmFlash();
        $user = $this->crmUser();
        $count = MailAlert::query()->where('user_id', $user->id)->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
        $this->crmFlashSuccess($count > 0 ? "Marked {$count} mail alert(s) as read." : 'No unread mail alerts.');
    }

    public function openMailAlert(int $alertId): mixed
    {
        $user = $this->crmUser();
        $alert = MailAlert::query()
            ->where('user_id', $user->id)
            ->with('message')
            ->findOrFail($alertId);
        $alert->markRead();

        return redirect()->route('crm.mailbox.thread', $alert->message->thread_id);
    }

    public function render()
    {
        $user = $this->crmUser();
        $leads = Lead::query()->visibleTo($user);

        $statusCounts = LeadStatus::query()
            ->orderBy('sort_order')
            ->withCount(['leads' => fn ($q) => $q->visibleTo($user)])
            ->get();

        $followUpsDue = (clone $leads)
            ->followUpsDue()
            ->with(['primaryContact', 'assignee', 'status'])
            ->orderBy('next_follow_up_at')
            ->limit(12)
            ->get();

        $followUpsUpcoming = (clone $leads)
            ->followUpsUpcoming(48)
            ->with(['primaryContact', 'assignee', 'status'])
            ->orderBy('next_follow_up_at')
            ->limit(12)
            ->get();

        $unreadAlerts = collect();
        $unreadCount = 0;
        if ($user->isAdmin()) {
            $unreadAlerts = LeadChangeAlert::query()
                ->unread()
                ->with(['lead.primaryContact', 'lead.status', 'actor'])
                ->latest()
                ->limit(20)
                ->get();
            $unreadCount = LeadChangeAlert::query()->unread()->count();
        }

        $mailAlerts = MailAlert::query()
            ->where('user_id', $user->id)
            ->unread()
            ->with(['message.thread', 'lead'])
            ->latest()
            ->limit(15)
            ->get();
        $mailUnreadCount = MailAlert::query()->where('user_id', $user->id)->unread()->count();

        $recentLeads = (clone $leads)
            ->with(['status', 'primaryContact', 'company', 'assignee'])
            ->withCount(['changeAlerts as unread_alerts_count' => fn ($q) => $q->where('is_read', false)])
            ->latest('updated_at')
            ->limit(12)
            ->get();

        return view('crm::livewire.dashboard', [
            'user' => $user,
            'isAdmin' => $user->isAdmin(),
            'totalLeads' => (clone $leads)->count(),
            'myLeads' => Lead::query()->where('assigned_to', $user->id)->count(),
            'pipelineValue' => (clone $leads)->sum('expected_value'),
            'dueFollowUpsCount' => (clone $leads)->followUpsDue()->count(),
            'statusCounts' => $statusCounts,
            'followUpsDue' => $followUpsDue,
            'followUpsUpcoming' => $followUpsUpcoming,
            'unreadAlerts' => $unreadAlerts,
            'unreadCount' => $unreadCount,
            'mailAlerts' => $mailAlerts,
            'mailUnreadCount' => $mailUnreadCount,
            'mailSyncNote' => $this->mailSyncNote,
            'recentLeads' => $recentLeads,
        ])->layout('crm::layouts.app');
    }
}
