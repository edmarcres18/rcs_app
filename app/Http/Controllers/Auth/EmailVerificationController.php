<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class EmailVerificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the email verification form.
     *
     * @return \Illuminate\View\View
     */
    public function showVerificationForm()
    {
        return view('auth.verify-otp');
    }

    /**
     * Verify the email using OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $verification = EmailVerification::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$verification) {
            return back()->withErrors(['otp' => 'Invalid OTP.'])->withInput();
        }

        if ($verification->isExpired()) {
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.'])->withInput();
        }

        // Mark user's email as verified
        $user = User::where('email', $request->email)->first();
        $user->email_verified_at = Carbon::now();
        $user->save();

        // Delete the verification record
        $verification->delete();

        // Login the user
        Auth::login($user);

        return redirect()->route('home')->with('status', 'Email verified successfully!');
    }

    /**
     * Resend OTP to email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $otp = mt_rand(100000, 999999);
        
        EmailVerification::updateOrCreate(
            ['email' => $request->email],
            [
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(15),
            ]
        );

        // Send OTP email
        Mail::to($request->email)->send(new OtpMail($otp));

        return back()->with('status', 'OTP has been sent to your email.');
    }
}
