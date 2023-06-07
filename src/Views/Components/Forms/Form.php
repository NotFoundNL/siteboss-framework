<?php

namespace NotFound\Framework\Views\Components\Forms;

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
        return view('siteboss::forms.form', ['submitUrl' => '/siteboss/api/api/forms/'.$this->form->id.'/'.Lang::current()->url.'/']);
    }
}
