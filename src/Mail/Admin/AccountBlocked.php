<?php

namespace NotFound\Framework\Mail\Admin;

use Illuminate\Mail\Mailable;
use NotFound\Framework\Models\CmsUser;

class AccountBlocked extends Mailable
{
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        private CmsUser $user,
    ) {}

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('siteboss::mail.admin.account-blocked')
            ->subject('CMS: Account geblokkeerd')
            ->with([
                'user' => $this->user,
            ]);
    }
}
