<?php

/**
 * Get appropriate color class for activity type
 *
 * @param string $activityType
 * @return string
 */
function getActivityColor(string $activityType): string
{
    $colors = [
        'login' => 'success',
        'logout' => 'secondary',
        'user_created' => 'primary',
        'user_updated' => 'info',
        'user_deleted' => 'danger',
        'failed_login' => 'warning',
        'password_reset' => 'warning',
    ];

    return $colors[$activityType] ?? 'primary';
}
