<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerification;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Carbon\Carbon;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/email/verify';

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
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'nickname' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'string', 'in:EMPLOYEE,SUPERVISOR'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'nickname' => $data['nickname'],
            'email' => $data['email'],
            'roles' => $data['roles'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        // Generate OTP
        $otp = mt_rand(100000, 999999);

        // Store OTP in email_verifications table
        EmailVerification::updateOrCreate(
            ['email' => $user->email],
            [
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(15),
            ]
        );

        // Send OTP to email
        $this->sendOtpEmail($user->email, $otp);

        return redirect()->route('verification.notice');
    }

    /**
     * Send OTP email for verification.
     *
     * @param  string  $email
     * @param  string  $otp
     * @return void
     */
    protected function sendOtpEmail($email, $otp)
    {
        Mail::to($email)->send(new OtpMail($otp));
    }
}
