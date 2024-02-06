<?php

namespace NotFound\Framework\Models\Forms\Policies;

use NotFound\Framework\Models\CmsUser;
use NotFound\Framework\Models\Forms\Category;
use NotFound\Framework\Policies\BasePolicy;

class CategoryPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  \NotFound\Framework\Models\CmsUser  $cmsUser
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(CmsUser $user, Category $category)
    {
        return $user->checkRights($category->rights);
    }
}
