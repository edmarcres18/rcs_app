@extends('emails.instructions.default')

@section('title', 'Email Verification')

@section('message_body')
    <h1 style="margin-top: 0; text-align: center;">Email Verification</h1>
    <p>Thank you for registering! Please use the following One-Time Password (OTP) to verify your email address:</p>
    <div style="font-size: 24px; font-weight: bold; text-align: center; color: #3490dc; padding: 10px; margin: 20px 0; letter-spacing: 5px; background-color: #eee; border-radius: 5px;">{{ $otp }}</div>
    <p>This OTP will expire in 15 minutes.</p>
    <p>If you did not request this verification, please ignore this email.</p>
@endsection
