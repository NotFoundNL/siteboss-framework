<?php

namespace NotFound\Framework\Http\Controllers;

use Illuminate\Http\Request;
use NotFound\Framework\Helpers\Layout\Elements\LayoutBar;
use NotFound\Framework\Helpers\Layout\Elements\LayoutBarButton;
use NotFound\Framework\Helpers\Layout\Elements\LayoutBreadcrumb;
use NotFound\Framework\Helpers\Layout\Elements\LayoutButton;
use NotFound\Framework\Helpers\Layout\Elements\LayoutForm;
use NotFound\Framework\Helpers\Layout\Elements\LayoutPage;
use NotFound\Framework\Helpers\Layout\Elements\LayoutWidget;
use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTable;
use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTableHeader;
use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTableRow;
use NotFound\Framework\Helpers\Layout\Enums\LayoutRequestMethod;
use NotFound\Framework\Helpers\Layout\Helpers\LayoutWidgetHelper;
use NotFound\Framework\Helpers\Layout\Inputs\LayoutInputCheckbox;
use NotFound\Framework\Helpers\Layout\Inputs\LayoutInputText;
use NotFound\Framework\Helpers\Layout\LayoutResponse;
use NotFound\Framework\Helpers\Layout\Responses\Redirect;
use NotFound\Framework\Helpers\Layout\Responses\Toast;
use NotFound\Framework\Http\Requests\FormDataRequest;
use NotFound\Framework\Models\CmsRedirect;

class RedirectController extends Controller
{
    public function index(Request $request)
    {
        $helper = new LayoutWidgetHelper(__('siteboss::redirects.title'), __('siteboss::redirects.widget_title'));
        $helper->widget->noPadding();

        $table = new LayoutTable(sort: false, delete: true, edit: true);

        $table->addHeader(new LayoutTableHeader(__('siteboss::redirects.from'), 'url'));
        $table->addHeader(new LayoutTableHeader(__('siteboss::redirects.to'), 'redirect'));
        $table->addHeader(new LayoutTableHeader(__('siteboss::ui.enabled'), 'enabled'));
        $bar = new LayoutBar;

        $button = new LayoutBarButton('Nieuw');

        $button->setIcon('plus');

        $button->setLink('/app/redirects/create');

        $bar->addBarButton($button);

        $helper->widget->addBar($bar);

        foreach (CmsRedirect::all()->sortBy('url') as $redirect) {
            $row = new LayoutTableRow($redirect->id, '/app/redirects/'.$redirect->id);
            $row->addColumn(new LayoutTableColumn($redirect->url));
            $row->addColumn(new LayoutTableColumn($redirect->redirect));
            $row->addColumn(new LayoutTableColumn($redirect->enabled, 'checkbox'));
            $table->addRow($row);
        }

        $helper->widget->addTable($table);

        return $helper->response();
    }

    public function readOne(?CmsRedirect $redirect)
    {
        $page = new LayoutPage('Redirect');

        $breadcrumb = new LayoutBreadcrumb;
        $breadcrumb->addHome();
        $breadcrumb->addItem('Redirects', '/app/redirects');
        $breadcrumb->addItem($redirect->url ?? __('siteboss::redirects.title'));
        $page->addBreadCrumb($breadcrumb);

        $widget = new LayoutWidget($redirect->url ?? __('siteboss::redirects.title'));

        $form = new LayoutForm(sprintf('/app/redirects/%s/', $redirect->id ?? 'create'));

        $url = new LayoutInputText('url', __('siteboss::redirects.from'));
        $url->setValue($redirect->url ?? '');
        $url->setDescription(__('siteboss::redirects.from_description'));
        $url->setRequired();
        $form->addInput($url);

        $to = new LayoutInputText('redirect', __('siteboss::redirects.to'));
        $to->setValue($redirect->redirect ?? '');
        $to->setDescription(__('siteboss::redirects.to_description'));
        $to->setRequired();
        $form->addInput($to);

        $enabled = new LayoutInputCheckbox('enabled', __('siteboss::ui.enabled'));
        $enabled->setValue($redirect->enabled ?? false);
        $form->addInput($enabled);

        $recursive = new LayoutInputCheckbox('recursive', __('siteboss::redirects.recursive'));
        $recursive->setValue($redirect->recursive ?? false);
        $form->addInput($recursive);

        $rewrite = new LayoutInputCheckbox('rewrite', __('siteboss::redirects.rewrite'));
        $rewrite->setDescription(__('siteboss::redirects.rewrite_description'));
        $rewrite->setValue($redirect->rewrite ?? false);
        $form->addInput($rewrite);

        $form->setMethod(LayoutRequestMethod::PUT);
        $form->addButton(new LayoutButton(__('siteboss::ui.save')));

        $widget->addForm($form);
        $page->addWidget($widget);

        $response = new LayoutResponse($page);

        return $response->build();
    }

    public function create()
    {
        return $this->readOne(null);
    }

    public function update(CmsRedirect $redirect, FormDataRequest $request)
    {
        return $this->saveRedirect($request, $redirect);
    }

    public function delete(CmsRedirect $redirect)
    {
        $response = new LayoutResponse;

        if ($redirect->delete()) {
            $response->addAction(new Toast(__('siteboss::redirects.deleted')));
        } else {
            $response->addAction(new Toast('Error'));
        }

        return $response->build();
    }

    public function createRedirect(FormDataRequest $request)
    {
        $redirect = new CmsRedirect;

        return $this->saveRedirect($request, $redirect);
    }

    private function saveRedirect(FormDataRequest $request, CmsRedirect $redirect): object
    {
        $request->validate([
            'url' => 'required|string',
            'redirect' => 'required|string',
            'enabled' => 'bool',
            'recursive' => 'bool',
            'rewrite' => 'bool',
        ]);

        $redirect->url = $request->url;
        $redirect->redirect = $request->redirect;
        $redirect->enabled = $request->enabled;
        $redirect->recursive = $request->recursive;
        $redirect->rewrite = $request->rewrite;
        $response = new LayoutResponse;

        if ($redirect->save()) {
            $response->addAction(new Toast(__('siteboss::redirects.updated')));
        } else {
            $response->addAction(new Toast('Error'));
        }

        $response->addAction(new Redirect('/app/redirects'));

        return $response->build();
    }
}
