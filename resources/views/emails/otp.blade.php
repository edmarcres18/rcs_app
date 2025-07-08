<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .otp {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            color: #3490dc;
            padding: 10px;
            margin: 20px 0;
            letter-spacing: 5px;
            background-color: #eee;
            border-radius: 5px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Email Verification</h1>
        </div>
        <p>Thank you for registering! Please use the following One-Time Password (OTP) to verify your email address:</p>
        <div class="otp">{{ $otp }}</div>
        <p>This OTP will expire in 15 minutes.</p>
        <p>If you did not request this verification, please ignore this email.</p>
        <div class="footer">
            <p>&copy; {{ date('Y') }} RCS App. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 