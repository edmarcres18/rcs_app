@php
    $storageAuth = 'storage/app_logo/auth_logo.png';
    $storageDefault = 'storage/app_logo/logo.png';
    $publicAuth = 'images/app_logo/auth_logo.png';
    $publicDefault = 'images/app_logo/logo.png';

    // Prefer storage if exists, otherwise fall back to public assets
    $resolvedAuth = file_exists(public_path($storageAuth)) ? $storageAuth : (file_exists(public_path($publicAuth)) ? $publicAuth : $storageAuth);
    $resolvedDefault = file_exists(public_path($storageDefault)) ? $storageDefault : (file_exists(public_path($publicDefault)) ? $publicDefault : $storageDefault);

    $logoToUse = file_exists(public_path($resolvedAuth)) ? $resolvedAuth : $resolvedDefault;
    $logoPath = versioned_asset($logoToUse);
@endphp
<img src="{{ $logoPath }}" alt="logo" style="width: 80px;">
