<?php

namespace NotFound\Framework\Policies\Forms;

use NotFound\Framework\Policies\BasePolicy;
use NotFound\Framework\Models\CmsUser;
use NotFound\Framework\Models\Forms\Data;

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
