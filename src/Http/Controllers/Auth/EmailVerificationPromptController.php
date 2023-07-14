<?php

namespace NotFound\Framework\Http\Controllers\Auth;

use Illuminate\Http\Request;
use NotFound\Framework\Http\Controllers\Controller;
use NotFound\Framework\Providers\RouteServiceProvider;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(RouteServiceProvider::HOME)
            : view('siteboss::auth.verify-email');
    }
}
