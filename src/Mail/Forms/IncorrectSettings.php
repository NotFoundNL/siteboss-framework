<?php

namespace NotFound\Framework\Mail\Forms;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use NotFound\Framework\Models\Forms\Form;
use NotFound\Framework\Services\Forms\ValidatorInfo;

class IncorrectSettings extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Form $form,
        public ValidatorInfo $validatorInfo
    ) {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mail.formbuilder.incorrect-settings')
            ->subject($this->form->name.' niet goed geconfigureerd');
    }
}
