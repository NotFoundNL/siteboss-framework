<?php

namespace NotFound\Framework\Models\Forms\Policies;

use NotFound\Framework\Models\CmsUser;
use NotFound\Framework\Models\Forms\Data;
use NotFound\Framework\Policies\BasePolicy;

class DataPolicy extends BasePolicy
{
    public function viewAny(CmsUser $user)
    {
        return $user->checkRights('forms-data');
    }

    public function update(CmsUser $user, Data $data)
    {
        return $user->checkRights('forms-data');
    }

    public function delete(CmsUser $user, Data $data)
    {
        return $user->checkRights('forms-data');
    }
}
