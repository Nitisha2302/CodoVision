<?php

namespace Codovision\Crm\Services;

use Codovision\Crm\Models\CrmUser;
use Codovision\Crm\Models\Lead;
use Codovision\Crm\Models\LeadAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignmentService
{
    public function __construct(protected ActivityLogger $logger)
    {
    }

    public function assign(Lead $lead, int $userId, CrmUser $actor, string $method = 'manual', ?string $note = null): Lead
    {
        if (!$actor->canReassignLeads() && $actor->id !== $userId && $lead->assigned_to) {
            throw ValidationException::withMessages([
                'assigned_to' => 'You are not allowed to reassign leads.',
            ]);
        }

        $user = CrmUser::query()->where('is_active', true)->findOrFail($userId);

        return DB::transaction(function () use ($lead, $user, $actor, $method, $note) {
            $from = $lead->assigned_to;

            LeadAssignment::create([
                'lead_id' => $lead->id,
                'assigned_from' => $from,
                'assigned_to' => $user->id,
                'assigned_by' => $actor->id,
                'method' => $method,
                'note' => $note,
            ]);

            $lead->assigned_to = $user->id;
            $lead->team_id = $user->team_id;
            $lead->save();

            $user->forceFill(['last_assigned_at' => now()])->save();

            $this->logger->logLead($lead, $actor, 'assignment', 'Lead assigned to ' . $user->name, $method);
            $this->logger->audit($actor, 'lead.assigned', Lead::class, $lead->id, [
                'to' => $user->id,
                'method' => $method,
            ]);

            return $lead->fresh(['assignee']);
        });
    }

    public function roundRobinAssign(Lead $lead, CrmUser $actor): Lead
    {
        $query = CrmUser::query()
            ->where('is_active', true)
            ->role('Sales Executive');

        if ($lead->team_id) {
            $query->where('team_id', $lead->team_id);
        }

        $user = $query->orderByRaw('last_assigned_at IS NOT NULL')
            ->orderBy('last_assigned_at')
            ->orderBy('id')
            ->first();

        if (!$user) {
            return $this->assign($lead, $actor->id, $actor, 'round_robin', 'Fallback to creator');
        }

        return $this->assign($lead, $user->id, $actor, 'round_robin');
    }
}
