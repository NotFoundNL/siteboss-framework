<?php

namespace NotFound\Framework\Models\Forms\Policies;

use Illuminate\Auth\Access\Response;
use NotFound\Framework\Models\CmsUser;
use NotFound\Framework\Models\Forms\Category;
use NotFound\Framework\Policies\BasePolicy;

class CategoryPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  CmsUser  $cmsUser
     * @return Response|bool
     */
    public function view(CmsUser $user, Category $category)
    {
        return $user->checkRights($category->rights);
    }
}
