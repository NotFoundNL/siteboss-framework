<?php

namespace NotFound\Framework\Http\Controllers\Auth;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
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
            $link = URL::temporarySignedRoute(
                'siteboss.verification.block',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $request->route('id'),
                    'hash' => $request->route('hash'),
                ]);

            return view('siteboss::pages.block-account',[
                'result' => 'error',
                'title' => __('siteboss::auth.verify_block_account_title'),
                'message' => __('siteboss::auth.verify_block_account_message'),
                'buttonText' => __('siteboss::auth.verify_block_account_button'),
                'link' => $link,
            ]);
        }

        if (! $user) {
            throw new AuthorizationException;
        }

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification())) || ! $user->enabled) {
            throw new AuthorizationException;
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return ['status' => 'ok', 'message' => __('siteboss::auth.verify_email_success')];
    }

    public function block(Request $request)
    {
        $user = CmsUser::find($request->route('id'));

        if (! $user) {
            throw new AuthorizationException;
        }

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        $user->enabled = 0;
        $user->email_verified_at = null;
        $user->save();

        Mail::to(config('siteboss.admin_email'))->send(new AccountBlocked($user));

        return ['status' => 'ok', 'message' => __('siteboss::auth.block_account_message')];
    }
}
