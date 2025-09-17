<?php
/**
 * Test script to verify upload configuration
 * Run this script to check if all upload settings are properly configured
 */

echo "=== RCS App Upload Configuration Test ===\n\n";

// Test PHP configuration
echo "1. PHP Configuration:\n";
echo "   upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "   post_max_size: " . ini_get('post_max_size') . "\n";
echo "   max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "   file_uploads: " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "\n";
echo "   memory_limit: " . ini_get('memory_limit') . "\n";
echo "   max_execution_time: " . ini_get('max_execution_time') . " seconds\n";
echo "   max_input_time: " . ini_get('max_input_time') . " seconds\n\n";

// Test directory permissions
echo "2. Directory Permissions:\n";
$uploadDir = __DIR__ . '/public/uploads/avatars';
if (!is_dir($uploadDir)) {
    if (mkdir($uploadDir, 0755, true)) {
        echo "   ✓ Created upload directory: $uploadDir\n";
    } else {
        echo "   ✗ Failed to create upload directory: $uploadDir\n";
    }
} else {
    echo "   ✓ Upload directory exists: $uploadDir\n";
}

if (is_writable($uploadDir)) {
    echo "   ✓ Upload directory is writable\n";
} else {
    echo "   ✗ Upload directory is not writable\n";
}

// Test GD extension
echo "\n3. Image Processing:\n";
if (extension_loaded('gd')) {
    echo "   ✓ GD extension is loaded\n";
    $gdInfo = gd_info();
    echo "   - JPEG Support: " . ($gdInfo['JPEG Support'] ? 'Yes' : 'No') . "\n";
    echo "   - PNG Support: " . ($gdInfo['PNG Support'] ? 'Yes' : 'No') . "\n";
    echo "   - GIF Support: " . ($gdInfo['GIF Read Support'] ? 'Yes' : 'No') . "\n";
    echo "   - WebP Support: " . ($gdInfo['WebP Support'] ? 'Yes' : 'No') . "\n";
} else {
    echo "   ✗ GD extension is not loaded\n";
}

// Test Intervention Image (if available)
if (class_exists('Intervention\Image\Facades\Image')) {
    echo "   ✓ Intervention Image is available\n";
} else {
    echo "   ⚠ Intervention Image not available (will use GD fallback)\n";
}

// Test file size limits
echo "\n4. File Size Limits:\n";
$maxUploadSize = parseSize(ini_get('upload_max_filesize'));
$maxPostSize = parseSize(ini_get('post_max_size'));
$memoryLimit = parseSize(ini_get('memory_limit'));

echo "   Upload limit: " . formatBytes($maxUploadSize) . "\n";
echo "   POST limit: " . formatBytes($maxPostSize) . "\n";
echo "   Memory limit: " . formatBytes($memoryLimit) . "\n";

$targetSize = 10 * 1024 * 1024; // 10MB
if ($maxUploadSize >= $targetSize && $maxPostSize >= $targetSize) {
    echo "   ✓ Configuration supports 10MB uploads\n";
} else {
    echo "   ✗ Configuration does not support 10MB uploads\n";
    echo "   - Upload limit needs to be at least 10MB\n";
    echo "   - POST limit needs to be at least 12MB (10MB + overhead)\n";
}

echo "\n5. Recommendations:\n";
if ($maxUploadSize < $targetSize) {
    echo "   - Increase upload_max_filesize to 10M or higher\n";
}
if ($maxPostSize < $targetSize * 1.2) {
    echo "   - Increase post_max_size to 12M or higher\n";
}
if ($memoryLimit < 256 * 1024 * 1024) {
    echo "   - Increase memory_limit to 256M or higher\n";
}

echo "\n=== Test Complete ===\n";

/**
 * Parse size string (e.g., "10M", "1024K") to bytes
 */
function parseSize($size) {
    $unit = strtoupper(substr($size, -1));
    $value = (int) $size;

    switch ($unit) {
        case 'G':
            return $value * 1024 * 1024 * 1024;
        case 'M':
            return $value * 1024 * 1024;
        case 'K':
            return $value * 1024;
        default:
            return $value;
    }
}

/**
 * Format bytes to human readable format
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
}
