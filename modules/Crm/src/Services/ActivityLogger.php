<?php

namespace Codovision\Crm\Services;

use Codovision\Crm\Models\Activity;
use Codovision\Crm\Models\AuditLog;
use Codovision\Crm\Models\CrmUser;
use Codovision\Crm\Models\Lead;
use Codovision\Crm\Models\LeadChangeAlert;

class ActivityLogger
{
    public function logLead(Lead $lead, ?CrmUser $user, string $type, string $title, ?string $description = null, array $meta = []): Activity
    {
        $activity = Activity::create([
            'lead_id' => $lead->id,
            'user_id' => $user?->id,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'meta' => $meta ?: null,
        ]);

        $this->notifyAdminsOfChange($lead, $user, $type, $title, $description, $meta);

        return $activity;
    }

    public function audit(?CrmUser $user, string $action, ?string $subjectType = null, ?int $subjectId = null, array $payload = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'payload' => $payload ?: null,
            'ip_address' => request()?->ip(),
        ]);
    }

    public function notifyAdminsOfChange(
        Lead $lead,
        ?CrmUser $actor,
        string $action,
        string $summary,
        ?string $description = null,
        array $meta = []
    ): void {
        // Super admin actions stay silent; team member / manager changes get highlighted.
        if (!$actor || $actor->isAdmin()) {
            return;
        }

        $changeCount = is_array($meta['changes'] ?? null) ? count($meta['changes']) : 0;
        $alertSummary = $summary;
        if ($changeCount > 0) {
            $alertSummary = $actor->name . ' edited ' . $lead->lead_code . ' (' . $changeCount . ' field' . ($changeCount === 1 ? '' : 's') . ')';
        } elseif ($description) {
            $alertSummary = $actor->name . ': ' . $summary;
        }

        LeadChangeAlert::create([
            'lead_id' => $lead->id,
            'actor_id' => $actor->id,
            'action' => $action,
            'summary' => $alertSummary,
            'meta' => array_filter([
                'description' => $description,
                'lead_code' => $lead->lead_code,
                ...$meta,
            ], fn ($v) => $v !== null && $v !== []) ?: null,
            'is_read' => false,
        ]);
    }

    public function markLeadAlertsRead(Lead $lead, CrmUser $reader): int
    {
        if (!$reader->isAdmin()) {
            return 0;
        }

        return LeadChangeAlert::query()
            ->where('lead_id', $lead->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_by' => $reader->id,
                'read_at' => now(),
            ]);
    }
}
