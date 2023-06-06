<?php

namespace NotFound\Framework\Policies;

use NotFound\Framework\Models\CmsConfig;
use NotFound\Framework\Models\CmsUser;

class CmsConfigPolicy extends BasePolicy
{
    public function viewAny(CmsUser $user)
    {
        return true;
    }

    public function view(CmsUser $user, CmsConfig $setting)
    {
        return $user->checkRights($setting->visible);
    }

    public function update(CmsUser $user, CmsConfig $setting)
    {
        return $user->checkRights($setting->editable);
    }

    public function delete(CmsUser $user, CmsConfig $setting)
    {
        return $user->checkRights($setting->editable);
    }
}
