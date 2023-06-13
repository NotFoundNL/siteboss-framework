<?php

namespace NotFound\Framework\Services\Forms;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use NotFound\Framework\Mail\Forms\ConfirmationEndUser;
use NotFound\Framework\Mail\Forms\IncorrectSettings;
use NotFound\Framework\Mail\Forms\NotificationFormSubmitted;
use NotFound\Framework\Models\Forms\Form;
use NotFound\Framework\Models\Lang;

class MailHandler
{
    public function __construct(
        private Lang $lang,
        private Form $form,
        private ValidatorInfo $validatorInfo
    ) {
    }

    public function sendMail(): void
    {
        $this->sendNotificationMail();

        if (trim($this->validatorInfo->getPrimaryEmail()) == '') {
            return;
        }

        if ($this->incorrectFormbuilderSettings()) {
            if (isset($this->form->notification_address)) {
                Mail::to($this->form->notification_address)->send(new IncorrectSettings($this->form, $this->validatorInfo));
            } else {
                Log::error('forms things are wrong');
            }

            return;
        }

        Mail::to($this->validatorInfo->getPrimaryEmail())->send(new ConfirmationEndUser($this->form, $this->validatorInfo, $this->lang));
    }

    private function incorrectFormbuilderSettings(): bool
    {
        $langurl = $this->lang->url ?? 'nl';
        if (
            ! isset($this->form->confirmation_mail?->$langurl)
            || strip_tags($this->form->confirmation_mail?->$langurl) == ''
        ) {
            // Confirmation email is empty
            return true;
        }

        return false;
    }

    private function sendNotificationMail(): void
    {
        if (trim($this->form->notification_address) === '') {
            return;
        }

        $mail = new NotificationFormSubmitted($this->form, $this->validatorInfo);
        if ($emailArray = explode(',', $this->form->notification_address)) {
            foreach ($emailArray as $email) {
                Mail::to($email)->send($mail);
            }
        } else {
            Mail::to($this->form->notification_address)->send($mail);
        }
    }
}
