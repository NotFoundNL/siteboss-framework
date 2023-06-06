<?php

namespace NotFound\Framework\Policies;

use NotFound\Framework\Models\CmsUser;
use NotFound\Framework\Models\Table;

class TablePolicy extends BasePolicy
{
    public function viewAny(CmsUser $user)
    {
        return true;
    }

    public function view(CmsUser $user, Table $table)
    {
        return $user->checkRights($table->rights);
    }

    public function create(CmsUser $user, Table $table)
    {
        return $user->checkRights($table->rights) && $table->allow_create;
    }

    public function update(CmsUser $user, Table $table)
    {
        return $user->checkRights($table->rights);
    }

    public function delete(CmsUser $user, Table $table)
    {
        return $user->checkRights($table->rights) && $table->allow_delete;
    }
}
