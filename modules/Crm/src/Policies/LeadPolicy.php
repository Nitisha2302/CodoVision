<?php

namespace Codovision\Crm\Policies;

use Codovision\Crm\Models\CrmUser;
use Codovision\Crm\Models\Lead;

class LeadPolicy
{
    public function viewAny(CrmUser $user): bool
    {
        return $user->can('crm.leads.view');
    }

    public function view(CrmUser $user, Lead $lead): bool
    {
        if (!$user->can('crm.leads.view')) {
            return false;
        }

        if ($user->isAdmin() || $user->can('crm.leads.view_all')) {
            return true;
        }

        if (($user->isManager() || $user->can('crm.leads.view_team')) && $lead->team_id === $user->team_id) {
            return true;
        }

        return (int) $lead->assigned_to === (int) $user->id;
    }

    public function create(CrmUser $user): bool
    {
        return $user->can('crm.leads.create');
    }

    public function update(CrmUser $user, Lead $lead): bool
    {
        return $this->view($user, $lead) && $user->can('crm.leads.update');
    }

    public function delete(CrmUser $user, Lead $lead): bool
    {
        return $user->canDeleteLeads() && $this->view($user, $lead);
    }

    public function reassign(CrmUser $user, Lead $lead): bool
    {
        return $user->canReassignLeads() && $this->view($user, $lead);
    }

    public function export(CrmUser $user): bool
    {
        return $user->canExportLeads();
    }
}
