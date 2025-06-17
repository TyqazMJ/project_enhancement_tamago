<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
{
    // First, authenticate user credentials
    $request->authenticate();

    $user = $request->user();

    if ($user->is_approved !== 1) {
        Auth::logout();

        $message = match ($user->is_approved) {
            0 => 'Your account is still pending approval.',
            -1 => 'Your account has been rejected.',
        };

        return back()->withErrors(['email' => $message]);
    }

    // If approved, regenerate session
    $request->session()->regenerate();

    // 🟡 Generate OTP
    $otp = rand(100000, 999999);
    $user->update([
        'otp_code' => $otp,
        'otp_expires_at' => now()->addMinutes(5),
    ]);

    // 🟡 Send OTP via email
    Mail::raw("Your OTP code is: $otp", function ($message) use ($user) {
        $message->to($user->email)
                ->subject('Your OTP Code for EBRMS Login');
    });

    // 🟡 Redirect to OTP input page
    return redirect('/2fa');
}


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function authenticated(Request $request, $user)
    {
        $otp = rand(100000, 999999);

        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(5),
        ]);

        Mail::raw("Your OTP code is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your OTP Code for EBRMS Login');
        });

        return redirect('/2fa');
    }


}
