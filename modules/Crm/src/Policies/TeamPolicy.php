<?php

namespace Codovision\Crm\Policies;

use Codovision\Crm\Models\CrmUser;
use Codovision\Crm\Models\Team;

class TeamPolicy
{
    public function viewAny(CrmUser $user): bool
    {
        return $user->can('crm.teams.manage') || $user->canManageUsers();
    }

    public function create(CrmUser $user): bool
    {
        return $user->can('crm.teams.manage') || $user->canManageUsers();
    }

    public function update(CrmUser $user, Team $team): bool
    {
        return $user->can('crm.teams.manage') || $user->canManageUsers();
    }
}
