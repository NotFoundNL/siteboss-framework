<?php

namespace NotFound\Framework\View\Components\Forms;

use Illuminate\View\Component;
use NotFound\Framework\Models\Forms\Form as FormsForm;
use NotFound\Framework\Models\Lang;

class Form extends Component
{
    public FormsForm $form;

    public function __construct(int $id)
    {
        $this->form = FormsForm::whereId($id)->first();
    }

    public function render()
    {
        if (view()->exists('siteboss.forms.form')) {
            return view('siteboss.forms.form', ['submitUrl' => '/siteboss/api/api/forms/'.$this->form->id.'/'.Lang::current()->url.'/']);
        }

        return view('siteboss::siteboss.forms.form', ['submitUrl' => '/siteboss/api/api/forms/'.$this->form->id.'/'.Lang::current()->url.'/']);
    }
}
