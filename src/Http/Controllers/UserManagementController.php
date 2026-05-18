<?php

namespace NotFound\Framework\Http\Controllers;

use Illuminate\Support\Arr;
use NotFound\Framework\Helpers\Layout\Elements\LayoutBar;
use NotFound\Framework\Helpers\Layout\Elements\LayoutBarButton;
use NotFound\Framework\Helpers\Layout\Elements\LayoutBreadcrumb;
use NotFound\Framework\Helpers\Layout\Elements\LayoutButton;
use NotFound\Framework\Helpers\Layout\Elements\LayoutForm;
use NotFound\Framework\Helpers\Layout\Elements\LayoutPage;
use NotFound\Framework\Helpers\Layout\Elements\LayoutText;
use NotFound\Framework\Helpers\Layout\Elements\LayoutWidget;
use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTable;
use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTableHeader;
use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTableRow;
use NotFound\Framework\Helpers\Layout\Enums\LayoutRequestMethod;
use NotFound\Framework\Helpers\Layout\Inputs\LayoutInputCheckbox;
use NotFound\Framework\Helpers\Layout\Inputs\LayoutInputTags;
use NotFound\Framework\Helpers\Layout\Inputs\LayoutInputText;
use NotFound\Framework\Helpers\Layout\LayoutResponse;
use NotFound\Framework\Helpers\Layout\Responses\Redirect;
use NotFound\Framework\Helpers\Layout\Responses\Toast;
use NotFound\Framework\Http\Requests\FormDataRequest;
use NotFound\Framework\Models\CmsGroup;
use NotFound\Framework\Models\CmsUser;

class UserManagementController extends Controller
{
    public function create(): mixed
    {
        if (! config('openid.use_existing_email', false) || request()->user()->cannot('viewAny', CmsUser::class)) {
            return LayoutPage::unauthorized();
        }

        $page = new LayoutPage(__('siteboss::ui.users.list'));

        $breadcrumb = new LayoutBreadcrumb;
        $breadcrumb->addHome();
        $breadcrumb->addItem(__('siteboss::ui.users.list'), '/app/users');
        $breadcrumb->addItem(__('siteboss::ui.users.new'));
        $page->addBreadCrumb($breadcrumb);

        $widget = new LayoutWidget(__('siteboss::ui.users.new'));

        $form = new LayoutForm(sprintf('/app/users/create/'));

        $help = new LayoutText('<p>'.__('siteboss::ui.users.new_explain').'</p>');

        $form->addText($help);

        $email = new LayoutInputText('email', __('siteboss::ui.email'));

        $email->setRequired();
        $form->addInput($email);

        $form->addButton(new LayoutButton(__('siteboss::ui.save')));

        $widget->addForm($form);
        $page->addWidget($widget);

        $response = new LayoutResponse($page);

        return $response->build();
    }

    public function createUser(FormDataRequest $request): mixed
    {
        if (! config('openid.use_existing_email', false) || request()->user()->cannot('viewAny', CmsUser::class)) {
            return LayoutPage::unauthorized();
        }
        $request->validate([
            'email' => 'required|email|unique:cms_user,email',
        ]);

        $user = new CmsUser;
        $user->email = $request->email;
        $user->enabled = true;
        $user->properties = new \stdClass;
        $response = new LayoutResponse;
        if ($user->save()) {
            $response->addAction(new Toast(__('siteboss::response.user.updated')));
            $response->addAction(new Redirect('/app/users/'.$user->id));
        } else {
            $response->addAction(new Toast(__('siteboss::response.user.error')));
        }

        return $response->build();
    }

    public function readAll(CmsUser $user): mixed
    {
        if (request()->user()->cannot('update', $user)) {
            return LayoutPage::unauthorized();
        }

        $page = new LayoutPage(__('siteboss::ui.users.list'));

        $breadcrumb = new LayoutBreadcrumb;
        $breadcrumb->addHome();
        $breadcrumb->addItem(__('siteboss::ui.users.list'));
        $page->addBreadCrumb($breadcrumb);

        $widget = new LayoutWidget(__('siteboss::ui.users.list'));
        $widget->noPadding();

        if (config('openid.use_existing_email', false)) {
            $bar = new LayoutBar;

            $button = new LayoutBarButton(__('siteboss::ui.users.new'));

            $button->setLink('/app/users/create');

            $bar->addBarButton($button);

            $widget->addBar($bar);
        }
        $widget->addTable($this->createUserTable());

        $page->addWidget($widget);

        $response = new LayoutResponse($page);

        return $response->build();
    }

    public function readOne(CmsUser $user): mixed
    {
        if (request()->user()->cannot('update', $user)) {
            return LayoutPage::unauthorized();
        }

        $page = new LayoutPage(__('siteboss::ui.users.list'));

        $breadcrumb = new LayoutBreadcrumb;
        $breadcrumb->addHome();
        $breadcrumb->addItem(__('siteboss::ui.users.list'), '/app/users');
        $breadcrumb->addItem($user->name ?? 'User');
        $page->addBreadCrumb($breadcrumb);

        $widget = new LayoutWidget($user->email ?? 'User');

        $widget->addForm($this->createUserForm($user));
        $page->addWidget($widget);

        $response = new LayoutResponse($page);

        return $response->build();
    }

    public function update(FormDataRequest $request, CmsUser $user): mixed
    {
        $adminRoleId = CmsGroup::whereInternal('admin')->firstOrFail()->id;

        $this->authorize('update', $user);
        $request->validate([
            'roles' => 'array',
            'enabled' => 'bool',
        ]);

        if (in_array($adminRoleId, $request->roles)) {
            $errorResponse = new LayoutResponse;
            $errorResponse->addAction(new Toast(__('siteboss::response.user.no_admin_assign'), 'error'));

            return $errorResponse->build();
        }

        $rolesAllowed = CmsGroup::getCachedGroups()->pluck('id')->toArray();
        $checkedRoles = Arr::where($request->roles, function ($value) use ($rolesAllowed) {
            return in_array($value, $rolesAllowed);
        });

        if ($user->explicitlyHasRole('admin')) {
            $checkedRoles[] = $adminRoleId;
        }

        $user->groups()->sync($checkedRoles);
        $user->enabled = $request->enabled;

        $response = new LayoutResponse;
        if ($user->save()) {
            $response->addAction(new Toast(__('siteboss::response.user.updated')));
            $response->addAction(new Redirect('/app/users/'));
        } else {
            $response->addAction(new Toast(__('siteboss::response.user.error')));
        }

        return $response->build();
    }

    private function createUserTable(): LayoutTable
    {
        $table = new LayoutTable(sort: false, delete: false, edit: true);

        $table->addHeader(new LayoutTableHeader(__('siteboss::ui.email'), 'email'));
        $table->addHeader(new LayoutTableHeader(__('siteboss::ui.enabled'), 'enabled'));
        $users = CmsUser::orderBy('email')->get();

        foreach ($users as $user) {
            $row = new LayoutTableRow($user->id, '/app/users/'.$user->id);
            $row->addColumn(new LayoutTableColumn($user->email ?? 'e-mail onbekend'));
            $row->addColumn(new LayoutTableColumn($user->enabled == 1, 'checkbox'));
            $table->addRow($row);
        }

        return $table;
    }

    private function getGroups(int $groupId): string
    {
        $groups = CmsGroup::where('parent', $groupId)->get();
        if ($groups->count() == 0) {
            return '';
        }
        $html = '<ul>';
        foreach ($groups as $group) {
            $html .= sprintf('<li>%s %s</li>', $group->name, $this->getGroups($group->id));
        }
        $html .= '</ul>';

        return $html;
    }

    private function createUserForm(CmsUser $user): LayoutForm
    {
        $form = new LayoutForm(sprintf('/app/users/%s/', $user->id));

        $help = new LayoutText('<h2>Groups</h2><div class="rights-tree">'.$this->getGroups(0).'</div>');

        $form->addText($help);

        $email = new LayoutInputText('email', __('siteboss::ui.email'));
        $email->setValue($user->email ?? '');
        $email->setDisabled();
        $form->addInput($email);

        $rolesTags = new LayoutInputTags('roles', __('siteboss::ui.users.roles'));
        $userIsAdmin = false;

        $userRoles = [];
        foreach (CmsGroup::getCachedGroups() as $group) {
            if ($group->internal !== 'admin') {
                $rolesTags->addItem($group->id, $group->name);
                if ($user->explicitlyHasRole($group->internal)) {
                    $userRoles[] = (object) ['id' => $group->id, 'label' => $group->name];
                }
            } elseif ($user->explicitlyHasRole($group->internal)) {
                $userIsAdmin = true;
            }
        }
        $rolesTags->setValue($userRoles);
        $form->addInput($rolesTags);

        if ($userIsAdmin) {
            $form->addText(new LayoutText(__('siteboss::ui.users.is_admin')));
        } else {
            $form->addText(new LayoutText(__('siteboss::ui.users.cannot_add_admin')));
        }

        $checkbox = new LayoutInputCheckbox('enabled', __('siteboss::ui.enabled'));
        $checkbox->setValue($user->enabled == 1);
        $form->addInput($checkbox);

        $form->setMethod(LayoutRequestMethod::PUT);
        $form->addButton(new LayoutButton(__('siteboss::ui.save')));

        return $form;
    }
}
