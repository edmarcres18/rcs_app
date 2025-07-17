@extends('emails.instructions.default')

@section('title', 'Welcome to MHR Reporting Compliance System')

@section('message_body')
    <h1 style="margin-top: 0; text-align: center;">Welcome, {{ $user->first_name }}!</h1>
    <p>We are thrilled to have you on board with the <strong>MHR Reporting Compliance System</strong>.</p>
    <p>Our platform is designed to streamline your reporting and compliance tasks, making your workflow more efficient and effective. We are committed to providing you with a seamless experience and robust support.</p>
    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
    <p><strong>Here are a few things you can do to get started:</strong></p>
    <ul style="padding-left: 20px;">
        <li><strong>Complete your profile:</strong> Ensure your information is up-to-date for a personalized experience.</li>
        <li><strong>Explore the dashboard:</strong> Familiarize yourself with the layout and features available to you.</li>
        <li><strong>Review the user guide:</strong> Check out our comprehensive guide to make the most of our platform.</li>
    </ul>
    <p>If you have any questions or need assistance, please do not hesitate to contact our support team. We are here to help you every step of the way.</p>
    <p>Thank you for joining us. We look forward to helping you achieve your compliance goals.</p>
@endsection
