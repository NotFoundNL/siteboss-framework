<?php

namespace NotFound\Framework\Policies;

use NotFound\Framework\Models\CmsUser;

class CmsUserPolicy extends BasePolicy
{
    public function viewAny(CmsUser $userLoggedIn)
    {
        return $userLoggedIn->checkRights(config('auth.user_management_role'));
    }

    public function view(CmsUser $userLoggedIn, CmsUser $userToUpdate)
    {
        return $userLoggedIn->checkRights(config('auth.user_management_role'));
    }

    public function update(CmsUser $userLoggedIn, CmsUser $userToUpdate)
    {
        return $userLoggedIn->checkRights(config('auth.user_management_role'));
    }

    public function delete(CmsUser $userLoggedIn, CmsUser $userToUpdate)
    {
        return $userLoggedIn->checkRights(config('auth.user_management_role'));
    }
}
