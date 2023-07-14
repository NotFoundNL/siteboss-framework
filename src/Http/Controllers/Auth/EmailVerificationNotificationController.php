<?php

namespace NotFound\Framework\Http\Controllers\Auth;

use NotFound\Framework\Http\Controllers\Controller;
use Illuminate\Http\Request;
use NotFound\Framework\Models\CmsUser;

class EmailVerificationNotificationController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Verification link sent!');
    }
}
