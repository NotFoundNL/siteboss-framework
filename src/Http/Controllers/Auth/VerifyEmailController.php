<?php

namespace NotFound\Framework\Http\Controllers\Auth;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use NotFound\Framework\Http\Controllers\Controller;
use NotFound\Framework\Mail\Admin\AccountBlocked;
use NotFound\Framework\Models\CmsUser;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $user = CmsUser::find($request->route('id'));

        if ($request->query('block')) {
            $user->enabled = 0;
            $user->email_verified_at = null;
            $user->save();

            Mail::to(env('SB_ADMIN_EMAIL'))->send(new AccountBlocked($user));

            return ['status' => 'ok', 'message' => __('siteboss::auth.block_account_message')];
        }

        if (!$user) {
            throw new AuthorizationException;
        }

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification())) || ! $user->enabled) {
            throw new AuthorizationException;
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect('/siteboss')->with('verified', true);
    }
}
