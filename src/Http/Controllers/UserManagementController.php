<?php

namespace NotFound\Framework\Http\Controllers;

use App\Http\Requests\FormDataRequest;
use Illuminate\Support\Arr;
use NotFound\Framework\Models\CmsGroup;
use NotFound\Framework\Models\CmsUser;
use NotFound\Layout\Elements\LayoutBar;
use NotFound\Layout\Elements\LayoutBarButton;
use NotFound\Layout\Elements\LayoutBreadcrumb;
use NotFound\Layout\Elements\LayoutButton;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Elements\LayoutPage;
use NotFound\Layout\Elements\LayoutText;
use NotFound\Layout\Elements\LayoutWidget;
use NotFound\Layout\Elements\Table\LayoutTable;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Elements\Table\LayoutTableHeader;
use NotFound\Layout\Elements\Table\LayoutTableRow;
use NotFound\Layout\Enums\LayoutRequestMethod;
use NotFound\Layout\Inputs\LayoutInputCheckbox;
use NotFound\Layout\Inputs\LayoutInputTags;
use NotFound\Layout\Inputs\LayoutInputText;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Redirect;
use NotFound\Layout\Responses\Toast;

class UserManagementController extends Controller
{
    public function create()
    {
        if (! env('OIDC_USE_EXISTING_EMAIL', false) || request()->user()->cannot('viewAny', CmsUser::class)) {
            return LayoutPage::unauthorized();
        }

        $page = new LayoutPage(__('ui.users.list'));

        $breadcrumb = new LayoutBreadcrumb();
        $breadcrumb->addHome();
        $breadcrumb->addItem(__('ui.users.list'), '/app/users');
        $breadcrumb->addItem(__('ui.users.new'));
        $page->addBreadCrumb($breadcrumb);

        $widget = new LayoutWidget(__('ui.users.new'));

        $form = new LayoutForm(sprintf('/app/users/create/'));

        $help = new LayoutText('Je maakt nu een lokale gebruiker aan. De gebruiker wordt niet automatisch aangemaakt in de login provider zoals KeyCloak, Azure of een andere OpenID Connect provider.');

        $form->addText($help);

        $email = new LayoutInputText('email', __('Email'));

        $email->setRequired();
        $form->addInput($email);

        $form->addButton(new LayoutButton(__('ui.save')));

        $widget->addForm($form);
        $page->addWidget($widget);

        $response = new LayoutResponse($page);

        return $response->build();
    }

    public function createUser(FormDataRequest $request)
    {
        if (! env('OIDC_USE_EXISTING_EMAIL', false) || request()->user()->cannot('viewAny', CmsUser::class)) {
            return LayoutPage::unauthorized();
        }
        $request->validate([
            'email' => 'required|email|unique:cms_user,email',
        ]);

        $user = new CmsUser();
        $user->email = $request->email;
        $user->enabled = true;
        $user->properties = new \stdClass();
        $response = new LayoutResponse();
        if ($user->save()) {
            $response->addAction(new Toast(__('response.user.updated')));
            $response->addAction(new Redirect('/app/users/'.$user->id));
        } else {
            $response->addAction(new Toast(__('response.user.error')));
        }

        return $response->build();
    }

    public function readAll(CmsUser $user)
    {
        if (request()->user()->cannot('update', $user)) {
            return LayoutPage::unauthorized();
        }

        $page = new LayoutPage(__('ui.users.list'));

        $breadcrumb = new LayoutBreadcrumb();
        $breadcrumb->addHome();
        $breadcrumb->addItem(__('ui.users.list'));
        $page->addBreadCrumb($breadcrumb);

        $widget = new LayoutWidget(__('ui.users.list'));
        $widget->noPadding();

        if (env('OIDC_USE_EXISTING_EMAIL', false)) {
            $bar = new LayoutBar();

            $button = new LayoutBarButton(__('ui.users.new'));

            $button->setLink('/app/users/create');

            $bar->addBarButton($button);

            $widget->addBar($bar);
        }
        $widget->addTable($this->createUserTable());

        $page->addWidget($widget);

        $response = new LayoutResponse($page);

        return $response->build();
    }

    public function readOne(CmsUser $user)
    {
        if (request()->user()->cannot('update', $user)) {
            return LayoutPage::unauthorized();
        }

        $page = new LayoutPage(__('ui.users.list'));

        $breadcrumb = new LayoutBreadcrumb();
        $breadcrumb->addHome();
        $breadcrumb->addItem(__('ui.users.list'), '/app/users');
        $breadcrumb->addItem($user->name ?? 'User');
        $page->addBreadCrumb($breadcrumb);

        $widget = new LayoutWidget($user->email ?? 'User');

        $widget->addForm($this->createUserForm($user));
        $page->addWidget($widget);

        $response = new LayoutResponse($page);

        return $response->build();
    }

    public function update(FormDataRequest $request, CmsUser $user)
    {
        $adminRoleId = (new CmsGroup)->whereInternal('admin')->firstOrFail()->id;

        $this->authorize('update', $user);
        $request->validate([
            'roles' => 'array',
            'enabled' => 'bool',
        ]);

        if (in_array($adminRoleId, $request->roles)) {
            $errorResponse = new LayoutResponse();

            $errorResponse->addAction(new Toast('Do not assign admin role', 'error'));

            return $errorResponse->build();
        }

        // Make sure any roles selected are in fact local roles
        $rolesAllowed = (new CmsGroup)->getCachedGroups()->pluck('id')->toArray();
        $checkedRoles = Arr::where($request->roles, function ($value) use ($rolesAllowed) {
            return in_array($value, $rolesAllowed);
        });

        if ($user->explicityHasRole('admin')) {
            $checkedRoles[] = $adminRoleId;
        }

        $user->groups()->sync($checkedRoles);
        $user->enabled = $request->enabled;
        $user->save();

        $response = new LayoutResponse();
        if ($user->save()) {
            $response->addAction(new Toast(__('response.user.updated')));
        } else {
            $response->addAction(new Toast(__('response.user.error')));
        }

        return $response->build();
    }

    private function createUserTable(): LayoutTable
    {
        $table = new LayoutTable(sort: false, delete: false, edit: true);

        $table->addHeader(new LayoutTableHeader('E-mail', 'email'));
        $table->addHeader(new LayoutTableHeader(__('enabled'), 'enabled'));
        $users = CmsUser::get();

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

        $email = new LayoutInputText('email', __('Email'));
        $email->setValue($user->email ?? '');
        $email->setDisabled();
        $form->addInput($email);

        $rolesTags = new LayoutInputTags('roles', __('Roles'));
        $userIsAdmin = false;

        $userRoles = [];
        foreach ((new CmsGroup)->getCachedGroups() as $group) {
            // Do not show the admin group unless a user is an admin
            if ($group->internal !== 'admin') {
                $rolesTags->addItem($group->id, $group->name);
                if ($user->explicityHasRole($group->internal)) {
                    $userRoles[] = (object) ['id' => $group->id, 'label' => $group->name];
                }
            } elseif ($user->explicityHasRole($group->internal)) {
                $userIsAdmin = true;
            }
        }
        $rolesTags->setValue($userRoles);
        $form->addInput($rolesTags);

        if ($userIsAdmin) {
            $form->addText(new LayoutText('This user is a system administrator, you cannot remove this role.'));
        } else {
            $form->addText(new LayoutText('You cannot add administrator rights to a user.'));
        }

        $checkbox = new LayoutInputCheckbox('enabled', __('Enabled'));
        $checkbox->setValue($user->enabled == 1);
        $form->addInput($checkbox);

        $form->setMethod(LayoutRequestMethod::PUT);
        $form->addButton(new LayoutButton(__('ui.save')));

        return $form;
    }
}
