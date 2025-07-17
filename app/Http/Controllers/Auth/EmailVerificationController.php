<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Mail\WelcomeMail;

class EmailVerificationController extends Controller
{
    /**
     * Show the email verification notice.
     *
     * @return \Illuminate\View\View
     */
    public function showVerificationForm()
    {
        return view('auth.verify-otp');
    }

    /**
     * Mark the user's email address as verified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|min:6|max:6',
        ]);

        $verification = EmailVerification::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$verification) {
            return back()->withErrors(['otp' => 'Invalid OTP. Please try again.']);
        }

        if (Carbon::now()->isAfter($verification->expires_at)) {
            return back()->withErrors(['otp' => 'The OTP has expired. Please request a new one.']);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        // Send welcome email
        Mail::to($user->email)->send(new WelcomeMail($user));

        // Delete the OTP record after successful verification
        $verification->delete();

        return view('auth.verification-successful');
    }

    /**
     * Resend the email verification OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return back()->with('status', 'Your email is already verified.');
        }

        // Generate a new OTP
        $otp = mt_rand(100000, 999999);

        // Update or create the OTP record
        EmailVerification::updateOrCreate(
            ['email' => $user->email],
            [
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(15),
            ]
        );

        // Send the new OTP
        Mail::to($user->email)->send(new OtpMail($otp));

        return back()->with('status', 'A fresh OTP has been sent to your email address.');
    }
}
