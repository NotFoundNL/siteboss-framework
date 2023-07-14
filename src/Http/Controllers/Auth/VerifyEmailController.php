<?php

namespace NotFound\Framework\Http\Controllers\Auth;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use NotFound\Framework\Http\Controllers\Controller;
use NotFound\Framework\Models\CmsUser;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(EmailVerificationRequest $request)
    {
        $user = CmsUser::find($request->route('id'));

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect('/siteboss')->with('verified', true);
    }
}
