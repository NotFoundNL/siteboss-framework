<?php

namespace NotFound\Framework\Http\Controllers\Auth;

use Illuminate\Http\Request;
use NotFound\Framework\Http\Controllers\Controller;

class EmailVerificationNotificationController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return ['status' => 'ok', 'message' => 'siteboss::auth.verify_email_link_sent'];
    }
}
