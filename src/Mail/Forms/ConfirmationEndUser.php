<?php

namespace NotFound\Framework\Mail\Forms;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use NotFound\Framework\Models\Forms\Form;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Services\Forms\ValidatorInfo;

class ConfirmationEndUser extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected Form $form,
        protected ValidatorInfo $validatorInfo,
        protected Lang $lang
    ) {}

    public function build()
    {
        return $this->markdown('siteboss::mail.formbuilder.confirmation-end-user')
            ->subject($this->form->name)
            ->with([
                'form' => $this->form,
                'html' => $this->convertSubmittedValuesToReadableValues(),
            ]);
    }

    private function convertSubmittedValuesToReadableValues()
    {
        $langurl = $this->lang->url;
        $pattern = '/<span.*?data-field=\"([0-9]+)\".*?>.*?<\/span>/i';
        $validatorList = $this->validatorInfo->validators();
        $confirmationEmail = $this->form->confirmation_mail->$langurl;

        $output = preg_replace_callback($pattern, function ($matches) use ($validatorList, $langurl) {
            $fieldId = $matches[1];
            $fieldValue = '';

            foreach ($validatorList as $validator) {
                if ($validator->getFieldId() == $fieldId) {
                    $fieldValue = $validator->getReadableValue($langurl);
                }
            }

            return $fieldValue;
        }, $confirmationEmail);

        return $output;
    }
}
