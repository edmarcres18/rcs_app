<?php

namespace App\Enums;

enum UserRole: string
{
    case EMPLOYEE = 'EMPLOYEE';
    case SUPERVISOR = 'SUPERVISOR';
    case ADMIN = 'ADMIN';
    case SYSTEM_ADMIN = 'SYSTEM_ADMIN';
} 