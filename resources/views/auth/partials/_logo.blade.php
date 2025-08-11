@php
    $authLogo = 'storage/app_logo/auth_logo.png';
    $defaultLogo = 'storage/app_logo/logo.png';
    $logoToUse = file_exists(public_path($authLogo)) ? $authLogo : $defaultLogo;
    $logoPath = versioned_asset($logoToUse);
@endphp
<img src="{{ $logoPath }}" alt="logo" style="width: 80px;">
