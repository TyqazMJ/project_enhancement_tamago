<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth; // ✅ IMPORTANT!

class OTPController extends Controller
{
    public function verify(Request $request)
{
    $request->validate([
        'otp_code' => 'required|digits:6',
    ]);

    \Log::info('Logged in user:', ['user' => Auth::user()]);


    $user = Auth::user();

    if (
        $user->otp_code != $request->otp_code ||
        now()->greaterThan($user->otp_expires_at)
    ) {
        return back()->withErrors(['otp_code' => 'Invalid or expired OTP.']);
    }

    // ✅ OTP is valid — clear it
    $user->update([
        'otp_code' => null,
        'otp_expires_at' => null,
    ]);

    // 🔁 Redirect based on role
    return match ($user->role) {
        'super-admin' => redirect('/superadmin/pending-users'),
        'admin' => redirect('/admin/dashboard'),
        default => redirect('/staff/dashboard'),
    };
}

}
