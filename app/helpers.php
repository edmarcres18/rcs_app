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

if (!function_exists('parse_instruction_body')) {
    /**
     * Parses the instruction body text into structured HTML.
     * This function looks for numbered headings (e.g., "1. Heading")
     * and converts the text into a styled HTML format.
     *
     * @param string $text The raw instruction body text.
     * @return string The formatted HTML.
     */
    function parse_instruction_body(string $text): string
    {
        $lines = preg_split('/\\r\\n|\\n|\\r/', $text);
        $html = '<div class="instruction-content">';
        $isInList = false;

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                if ($isInList) {
                    $html .= '</ul>';
                    $isInList = false;
                }
                continue;
            }

            // Main headings (e.g., "1. Purpose of the Memo")
            if (preg_match('/^(\d+)\.\s+(.*)/', $line, $matches)) {
                if ($isInList) {
                    $html .= '</ul>';
                    $isInList = false;
                }
                $html .= '<h4 class="instruction-heading">' . e($matches[1]) . '. ' . e($matches[2]) . '</h4>';
            }
            // Sub-items (e.g., "a. To recommend...")
            elseif (preg_match('/^([a-z])\.\s+(.*)/i', $line, $matches)) {
                 if ($isInList) {
                    $html .= '</ul>';
                    $isInList = false;
                }
                $html .= '<p class="instruction-sub-item">' . e($matches[1]) . '. ' . e($matches[2]) . '</p>';
            }
            // Bullet points (e.g., "- Positioned at the top right.")
            elseif (str_starts_with($line, '- ')) {
                if (!$isInList) {
                    $html .= '<ul class="instruction-bullets">';
                    $isInList = true;
                }
                $html .= '<li>' . e(substr($line, 2)) . '</li>';
            }
            // Default paragraph
            else {
                 if ($isInList) {
                    $html .= '</ul>';
                    $isInList = false;
                }
                $html .= '<p>' . e($line) . '</p>';
            }
        }

        if ($isInList) {
            $html .= '</ul>';
        }

        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('generate_initials')) {
    /**
     * Generates initials from a full name.
     *
     * @param string $fullName
     * @return string
     */
    function generate_initials(string $fullName): string
    {
        return collect(explode(' ', $fullName))
            ->map(fn($name) => mb_strtoupper(mb_substr(trim($name), 0, 1)))
            ->implode('');
    }
}

if (!function_exists('format_recipients')) {
    /**
     * Formats a collection of recipients for display based on their roles.
     *
     * @param \Illuminate\Support\Collection $recipients
     * @return string
     */
    function format_recipients(\Illuminate\Support\Collection $recipients): string
    {
        if ($recipients->isEmpty()) {
            return 'N/A';
        }

        $recipientStrings = [];
        $groupedByRole = $recipients->groupBy('roles.value');

        // Process Admins first to maintain order
        if (isset($groupedByRole[\App\Enums\UserRole::ADMIN->value])) {
            foreach ($groupedByRole[\App\Enums\UserRole::ADMIN->value] as $user) {
                $recipientStrings[] = generate_initials($user->full_name) . " (" . $user->full_name . ")";
            }
        }

        // Process Supervisors
        if (isset($groupedByRole[\App\Enums\UserRole::SUPERVISOR->value])) {
            $supervisors = $groupedByRole[\App\Enums\UserRole::SUPERVISOR->value];
            $totalSupervisorsInSystem = \App\Models\User::where('roles', \App\Enums\UserRole::SUPERVISOR)->count();

            // If not all supervisors are selected, display their initials.
            if ($supervisors->count() < $totalSupervisorsInSystem) {
                foreach ($supervisors as $user) {
                    $recipientStrings[] = generate_initials($user->full_name);
                }
            } else { // Otherwise, if all supervisors are selected, display "ALL SUPERVISORS"
                if ($supervisors->isNotEmpty()) {
                    $recipientStrings[] = 'ALL SUPERVISORS';
                }
            }
        }

        // Process Employees
        if (isset($groupedByRole[\App\Enums\UserRole::EMPLOYEE->value])) {
            $employees = $groupedByRole[\App\Enums\UserRole::EMPLOYEE->value];
            $totalEmployees = \App\Models\User::where('roles', \App\Enums\UserRole::EMPLOYEE)->count();

            if ($employees->isNotEmpty() && $employees->count() === $totalEmployees) {
                $recipientStrings[] = 'ALL RANK FILE EMPLOYEES';
            } else {
                foreach ($employees as $user) {
                    $recipientStrings[] = $user->first_name;
                }
            }
        }

        return implode(', ', $recipientStrings);
    }
}

if (!function_exists('versioned_asset')) {
    /**
     * Generates a versioned asset path with a cache-busting query string.
     *
     * @param string $path
     * @return string
     */
    function versioned_asset(string $path): string
    {
        $fullPath = public_path($path);
        if (file_exists($fullPath)) {
            return asset($path) . '?v=' . filemtime($fullPath);
        }
        return asset($path);
    }
}

if (!function_exists('getInitials')) {
    /**
     * Generate initials from a full name.
     *
     * @param string $fullName
     * @return string
     */
    function getInitials($fullName)
    {
        $fullName = trim($fullName);
        if (empty($fullName)) {
            return '';
        }

        $nameParts = explode(' ', $fullName);
        $initials = '';
        foreach ($nameParts as $part) {
            if (!empty($part)) {
                $initials .= mb_strtoupper(mb_substr($part, 0, 1));
            }
        }
        return $initials;
    }
}
