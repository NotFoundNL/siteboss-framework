<?php

namespace NotFound\Framework\Http\Controllers;

use Illuminate\Http\Request;
use NotFound\Framework\Http\Requests\FormDataRequest;
use NotFound\Framework\Models\CmsRedirect;
use NotFound\Layout\Elements\LayoutBar;
use NotFound\Layout\Elements\LayoutBarButton;
use NotFound\Layout\Elements\LayoutBreadcrumb;
use NotFound\Layout\Elements\LayoutButton;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Elements\LayoutPage;
use NotFound\Layout\Elements\LayoutWidget;
use NotFound\Layout\Elements\Table\LayoutTable;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Elements\Table\LayoutTableHeader;
use NotFound\Layout\Elements\Table\LayoutTableRow;
use NotFound\Layout\Enums\LayoutRequestMethod;
use NotFound\Layout\Helpers\LayoutWidgetHelper;
use NotFound\Layout\Inputs\LayoutInputText;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Redirect;
use NotFound\Layout\Responses\Toast;

class RedirectController extends Controller
{
    public function index(Request $request)
    {
        $helper = new LayoutWidgetHelper('Redirects', 'Redirects');
        $helper->widget->noPadding();

        $table = new LayoutTable(sort: false, delete: true, edit: true);

        $table->addHeader(new LayoutTableHeader('Url', 'url'));
        $table->addHeader(new LayoutTableHeader('Redirect', 'redirect'));

        $bar = new LayoutBar();

        $button = new LayoutBarButton('Nieuw');

        $button->setIcon('plus');

        $button->setLink('/app/redirects/create');

        $bar->addBarButton($button);

        $helper->widget->addBar($bar);

        foreach (CmsRedirect::all() as $redirect) {
            $row = new LayoutTableRow($redirect->id, '/app/redirects/'.$redirect->id);
            $row->addColumn(new LayoutTableColumn($redirect->url));
            $row->addColumn(new LayoutTableColumn($redirect->redirect));
            $table->addRow($row);
        }

        $helper->widget->addTable($table);

        return $helper->response();
    }

    public function readOne(CmsRedirect $redirect)
    {
        $page = new LayoutPage('Redirect');

        $breadcrumb = new LayoutBreadcrumb();
        $breadcrumb->addHome();
        $breadcrumb->addItem('Redirects', '/app/redirects');
        $breadcrumb->addItem($redirect->url ?? 'url');
        $page->addBreadCrumb($breadcrumb);

        $widget = new LayoutWidget($redirect->url ?? 'Redirect');

        $form = new LayoutForm(sprintf('/app/redirects/%s/', $redirect->id));

        $url = new LayoutInputText('url', 'Url');
        $url->setValue($redirect->url ?? '');
        $form->addInput($url);

        $to = new LayoutInputText('redirect', 'Redirect');
        $to->setValue($redirect->redirect ?? '');
        $form->addInput($to);

        $form->setMethod(LayoutRequestMethod::PUT);
        $form->addButton(new LayoutButton(__('siteboss::ui.save')));

        $widget->addForm($form);
        $page->addWidget($widget);

        $response = new LayoutResponse($page);

        return $response->build();
    }

    public function update(CmsRedirect $redirect, FormDataRequest $request)
    {
        $redirect->url = $request->url;
        $redirect->redirect = $request->redirect;

        $response = new LayoutResponse();

        if ($redirect->save()) {
            $response->addAction(new Toast('Redirect updated'));
        } else {
            $response->addAction(new Toast('Error'));
        }

        $response->addAction(new Redirect('/app/redirects'));

        return $response->build();
    }

    public function delete(CmsRedirect $redirect)
    {
        $response = new LayoutResponse();

        if ($redirect->delete()) {
            $response->addAction(new Toast('Redirect deleted'));
        } else {
            $response->addAction(new Toast('Error'));
        }

        return $response->build();
    }

    public function create()
    {
        $page = new LayoutPage('Redirect');

        $breadcrumb = new LayoutBreadcrumb();
        $breadcrumb->addHome();
        $breadcrumb->addItem('Redirects', '/app/redirects');
        $breadcrumb->addItem('url');
        $page->addBreadCrumb($breadcrumb);

        $widget = new LayoutWidget('Redirect');

        $form = new LayoutForm('/app/redirects/create');

        $url = new LayoutInputText('url', 'Url');
        $form->addInput($url);

        $to = new LayoutInputText('redirect', 'Redirect');
        $form->addInput($to);

        $form->setMethod(LayoutRequestMethod::PUT);
        $form->addButton(new LayoutButton(__('siteboss::ui.save')));

        $widget->addForm($form);
        $page->addWidget($widget);

        $response = new LayoutResponse($page);

        return $response->build();
    }

    public function createRedirect(FormDataRequest $request)
    {
        $redirect = new CmsRedirect();

        $redirect->url = $request->url;
        $redirect->redirect = $request->redirect;

        $response = new LayoutResponse();

        if ($redirect->save()) {
            $response->addAction(new Toast('Redirect updated'));
        } else {
            $response->addAction(new Toast('Error'));
        }

        $response->addAction(new Redirect('/app/redirects'));

        return $response->build();
    }
}
