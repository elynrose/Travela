<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Enable two factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (! $user->two_factor_secret) {
            app(EnableTwoFactorAuthentication::class)($user);
        }

        return back()->with('status', 'two-factor-authentication-enabled');
    }

    /**
     * Disable two factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        if ($user->two_factor_secret) {
            app(DisableTwoFactorAuthentication::class)($user);
        }

        return back()->with('status', 'two-factor-authentication-disabled');
    }

    /**
     * Generate new recovery codes for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function regenerate(Request $request)
    {
        $user = $request->user();

        if ($user->two_factor_secret) {
            app(GenerateNewRecoveryCodes::class)($user);
        }

        return back()->with('status', 'recovery-codes-generated');
    }
} 