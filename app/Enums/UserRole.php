<?php

namespace App\Enums;

enum UserRole: string
{
    case EMPLOYEE = 'EMPLOYEE';
    case SUPERVISOR = 'SUPERVISOR';
    case ADMIN = 'ADMIN';
    case SYSTEM_ADMIN = 'SYSTEM_ADMIN';

    /**
     * Get all roles that can be selected as recipients.
     * Excludes SYSTEM_ADMIN.
     *
     * @return array
     */
    public static function getSelectableRoles(): array
    {
        return [
            self::EMPLOYEE,
            self::SUPERVISOR,
            self::ADMIN,
        ];
    }
}
