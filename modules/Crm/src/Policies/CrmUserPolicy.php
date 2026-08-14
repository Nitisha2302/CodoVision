<?php

namespace Codovision\Crm\Policies;

use Codovision\Crm\Models\CrmUser;

class CrmUserPolicy
{
    public function viewAny(CrmUser $user): bool
    {
        return $user->canManageUsers();
    }

    public function create(CrmUser $user): bool
    {
        return $user->canManageUsers();
    }

    public function update(CrmUser $user, CrmUser $model): bool
    {
        return $user->canManageUsers();
    }
}
