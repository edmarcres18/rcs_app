@php
    $authLogo = 'images/app_logo/auth_logo.png';
    $defaultLogo = 'images/app_logo/logo.png';
    $logoPath = file_exists(public_path($authLogo)) ? asset($authLogo) : asset($defaultLogo);
@endphp
<img src="{{ $logoPath }}" alt="logo" style="width: 80px;">
