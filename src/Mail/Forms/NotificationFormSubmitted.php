<?php

namespace NotFound\Framework\Mail\Forms;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use NotFound\Framework\Models\Forms\Form;
use NotFound\Framework\Services\Forms\ValidatorInfo;

class NotificationFormSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Form $form,
        public ValidatorInfo $validatorInfo
    ) {
    }

    public function build()
    {
        return $this->markdown('siteboss::mail.formbuilder.notification-form-submitted')
            ->subject($this->form->name)
            ->with('html', $this->getSummary());
    }

    private function getSummary()
    {
        $summaryHtml = '';

        foreach ($this->validatorInfo->validators() as $validator) {
            $summaryHtml .= $validator->getEmailHtml();
        }

        return $summaryHtml;
    }
}
