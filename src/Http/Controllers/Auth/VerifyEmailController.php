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

            return [
                'result' => 'error',
                'message' => 'weet je zeker dat je wilt blokekN?',
                'buttonText' => 'blokkeer email',
                'link' => $link,
            ];
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

        return redirect('/siteboss')->with('verified', true);
    }

    public function block(Request $request, CmsUser $user)
    {
        dd($request->route('hash'), $user->getEmailForVerification());

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        $user->enabled = 0;
        $user->email_verified_at = null;
        $user->save();

        Mail::to(env('SB_ADMIN_EMAIL'))->send(new AccountBlocked($user));

        return ['status' => 'ok', 'message' => __('siteboss::auth.block_account_message')];
    }
}
