<?php

namespace NotFound\Framework\Policies\Forms;
use NotFound\Framework\Models\CmsUser;
use NotFound\Framework\Models\Forms\Form;
use NotFound\Framework\Policies\BasePolicy;

class FormPolicy extends BasePolicy
{
    public function viewAny(CmsUser $user)
    {
        return true;
    }

    public function create(CmsUser $user)
    {
        return true;
    }

    public function update(CmsUser $user, Form $form)
    {
        return true;
    }

    public function delete(CmsUser $user, Form $form)
    {
        return true;
    }

    public function viewText(CmsUser $user)
    {
        return $user->checkRights('forms-formsettings');
    }
}
