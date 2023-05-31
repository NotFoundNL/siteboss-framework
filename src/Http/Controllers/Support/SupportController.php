<?php

namespace NotFound\Framework\Http\Controllers\Support;

use NotFound\Framework\Http\Controllers\Controller;
use App\Http\Requests\FormDataRequest;
use NotFound\Layout\Elements\LayoutBreadcrumb;
use NotFound\Layout\Elements\LayoutButton;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Elements\LayoutPage;
use NotFound\Layout\Elements\LayoutText;
use NotFound\Layout\Elements\LayoutWidget;
use NotFound\Layout\Inputs\LayoutInputEmail;
use NotFound\Layout\Inputs\LayoutInputText;
use NotFound\Layout\Inputs\LayoutInputTextArea;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Toast;

class SupportController extends Controller
{
    public function index(FormDataRequest $request)
    {
        $response = new LayoutResponse();
        $page = new LayoutPage(__('siteboss::support.title'));

        $breadcrumb = new LayoutBreadcrumb();
        $breadcrumb->addHome();
        $breadcrumb->addItem(__('siteboss::support.breadcrumb'));
        $page->addBreadCrumb($breadcrumb);

        $widget = new LayoutWidget(__('siteboss::support.widgetTitle'));

        $widget->addText(new LayoutText(__('siteboss::support.intro')));

        $form = new LayoutForm('/app/support');

        $email = new LayoutInputEmail('email', 'E-mail');
        $email->setRequired();

        $form->addInput($email);

        $subject = new LayoutInputText('subject', __('siteboss::support.subject'));
        $subject->setRequired();
        $subject->setDescription(__('siteboss::support.subjectDescription'));

        $form->addInput($subject);

        $description = new LayoutInputTextArea('description', __('siteboss::support.description'));
        $description->setRequired();
        $description->setDescription(__('siteboss::support.descriptionDescription'));

        $form->addInput($description);

        $form->addButton(new LayoutButton(__('siteboss::support.submit')));

        $widget->addForm($form);

        $page->addWidget($widget);

        $response->addUIElement($page);

        return response()->json($response->build());
    }

    public function update(FormDataRequest $request)
    {
        $request->validate([
            'email' => 'email|required',
            'subject' => 'string|required',
            'description' => 'string|required',
        ]);

        $message = '<p><strong>IP:</strong> '.$request->ip().'<br/><strong>Browser:</strong> '.$request->header('User-Agent').'</p>';

        $ticket_data = json_encode((object) [
            'message' => '<h3>Aanvraag '.env('APP_NAME', 'SiteBoss support form').'</h3><p>'.nl2br(htmlentities($request->input('description'))).'</p><hr/>'.$message,
            'email' => $request->input('email'),
            'priority' => 2,
            'subject' => $request->input('subject'),
        ]);
        $url = 'https://a-p-i.nl/cms/tickets/create.php?api_key='.urlencode(env('APP_NOTFOUND_SUPPORT_API'));
        $ch = curl_init($url);

        $header[] = 'Content-type: application/json';
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $ticket_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = json_decode(curl_exec($ch));

        $info = curl_getinfo($ch);

        $response = new LayoutResponse();
        if ($info['http_code'] == 200 && $server_output->result == 'ok') {
            $toast = new Toast(__('siteboss::support.done'));
            $response->addAction($toast);
        } else {
            $toastError = new Toast(__('siteboss::support.error'), 'error');
            $response->addAction($toastError);
        }

        $page = new LayoutPage(__('siteboss::support.title'));

        $breadcrumb = new LayoutBreadcrumb();
        $breadcrumb->addHome();
        $breadcrumb->addItem(__('siteboss::support.breadcrumb'));
        $page->addBreadCrumb($breadcrumb);

        $widget = new LayoutWidget(__('siteboss::support.widgetTitle'));

        $widget->addText(new LayoutText(__('siteboss::support.done')));

        $page->addWidget($widget);
        $response->addUIElement($page);

        return response()->json($response->build());
    }
}
