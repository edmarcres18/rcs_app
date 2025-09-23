<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class FileUploadService
{
    /**
     * Maximum file size in bytes (25MB)
     */
    const MAX_FILE_SIZE = 26214400; // 25 * 1024 * 1024

    /**
     * Allowed file types with their MIME types
     */
    const ALLOWED_TYPES = [
        // Images
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'bmp' => ['image/bmp'],
        'webp' => ['image/webp'],
        'svg' => ['image/svg+xml'],
        'tiff' => ['image/tiff'],
        'ico' => ['image/x-icon', 'image/vnd.microsoft.icon'],
        
        // Documents
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'ppt' => ['application/vnd.ms-powerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'txt' => ['text/plain'],
        'rtf' => ['application/rtf', 'text/rtf'],
        'odt' => ['application/vnd.oasis.opendocument.text'],
        'ods' => ['application/vnd.oasis.opendocument.spreadsheet'],
        'odp' => ['application/vnd.oasis.opendocument.presentation'],
        
        // Archives
        'zip' => ['application/zip'],
        'rar' => ['application/vnd.rar', 'application/x-rar-compressed'],
        '7z' => ['application/x-7z-compressed'],
        
        // Text files
        'csv' => ['text/csv', 'application/csv'],
        'json' => ['application/json'],
        'xml' => ['application/xml', 'text/xml'],
    ];

    /**
     * Upload a file and return file information
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return array
     * @throws Exception
     */
    public function uploadFile(UploadedFile $file, string $directory = 'instruction-attachments'): array
    {
        // Validate file
        $this->validateFile($file);

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = $this->generateUniqueFilename($extension);
        
        // Store file
        $path = $file->storeAs($directory, $filename, 'public');
        
        if (!$path) {
            throw new Exception('Failed to store the uploaded file.');
        }

        return [
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'url' => Storage::disk('public')->url($path)
        ];
    }

    /**
     * Validate uploaded file
     *
     * @param UploadedFile $file
     * @throws Exception
     */
    private function validateFile(UploadedFile $file): void
    {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new Exception('The uploaded file is not valid.');
        }

        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new Exception('File size exceeds the maximum limit of 25MB.');
        }

        // Get file extension and MIME type
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        // Check if extension is allowed
        if (!array_key_exists($extension, self::ALLOWED_TYPES)) {
            throw new Exception("File type '{$extension}' is not allowed.");
        }

        // Check if MIME type matches the extension
        if (!in_array($mimeType, self::ALLOWED_TYPES[$extension])) {
            throw new Exception("File MIME type '{$mimeType}' does not match the file extension '{$extension}'.");
        }

        // Additional security check: scan for malicious content
        $this->scanForMaliciousContent($file);
    }

    /**
     * Generate a unique filename
     *
     * @param string $extension
     * @return string
     */
    private function generateUniqueFilename(string $extension): string
    {
        return date('Y/m/d') . '/' . Str::uuid() . '.' . $extension;
    }

    /**
     * Basic scan for malicious content
     *
     * @param UploadedFile $file
     * @throws Exception
     */
    private function scanForMaliciousContent(UploadedFile $file): void
    {
        // Read first 1KB of file to check for suspicious patterns
        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle) {
            $content = fread($handle, 1024);
            fclose($handle);

            // Check for common malicious patterns
            $maliciousPatterns = [
                '<?php',
                '<%',
                '<script',
                'javascript:',
                'vbscript:',
                'onload=',
                'onerror=',
                'eval(',
                'base64_decode',
                'shell_exec',
                'system(',
                'exec(',
                'passthru(',
                'file_get_contents',
            ];

            foreach ($maliciousPatterns as $pattern) {
                if (stripos($content, $pattern) !== false) {
                    throw new Exception('File contains potentially malicious content and cannot be uploaded.');
                }
            }
        }
    }

    /**
     * Delete a file
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        try {
            return Storage::disk('public')->delete($path);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get file URL
     *
     * @param string $path
     * @return string|null
     */
    public function getFileUrl(string $path): ?string
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }
        return null;
    }

    /**
     * Get human readable file size
     *
     * @param int $bytes
     * @return string
     */
    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Check if file type is an image
     *
     * @param string $mimeType
     * @return bool
     */
    public static function isImage(string $mimeType): bool
    {
        return strpos($mimeType, 'image/') === 0;
    }

    /**
     * Get file icon class with color based on file extension or MIME type
     *
     * @param string $extension
     * @param string $mimeType
     * @return string
     */
    public static function getFileIcon(string $extension, string $mimeType): string
    {
        $extension = strtolower($extension);
        
        if (self::isImage($mimeType)) {
            return 'fas fa-image text-info';
        }

        $iconMap = [
            // Microsoft Office - Original brand colors
            'doc' => 'fas fa-file-word text-word',
            'docx' => 'fas fa-file-word text-word',
            'xls' => 'fas fa-file-excel text-excel',
            'xlsx' => 'fas fa-file-excel text-excel',
            'ppt' => 'fas fa-file-powerpoint text-powerpoint',
            'pptx' => 'fas fa-file-powerpoint text-powerpoint',
            
            // PDF
            'pdf' => 'fas fa-file-pdf text-pdf',
            
            // Text files
            'txt' => 'fas fa-file-alt text-secondary',
            'rtf' => 'fas fa-file-alt text-secondary',
            'csv' => 'fas fa-file-csv text-excel',
            'json' => 'fas fa-file-code text-warning',
            'xml' => 'fas fa-file-code text-warning',
            
            // Archives
            'zip' => 'fas fa-file-archive text-archive',
            'rar' => 'fas fa-file-archive text-archive',
            '7z' => 'fas fa-file-archive text-archive',
            
            // OpenDocument
            'odt' => 'fas fa-file-word text-primary',
            'ods' => 'fas fa-file-excel text-success',
            'odp' => 'fas fa-file-powerpoint text-warning',
            
            // Images (specific types)
            'jpg' => 'fas fa-image text-image',
            'jpeg' => 'fas fa-image text-image',
            'png' => 'fas fa-image text-image',
            'gif' => 'fas fa-image text-image',
            'bmp' => 'fas fa-image text-image',
            'webp' => 'fas fa-image text-image',
            'svg' => 'fas fa-image text-image',
            'tiff' => 'fas fa-image text-image',
            'ico' => 'fas fa-image text-image',
        ];

        return $iconMap[$extension] ?? 'fas fa-file text-secondary';
    }

    /**
     * Get file type category for styling purposes
     *
     * @param string $extension
     * @param string $mimeType
     * @return string
     */
    public static function getFileCategory(string $extension, string $mimeType): string
    {
        $extension = strtolower($extension);
        
        if (self::isImage($mimeType)) {
            return 'image';
        }

        $categoryMap = [
            'doc' => 'word',
            'docx' => 'word',
            'odt' => 'word',
            'xls' => 'excel',
            'xlsx' => 'excel',
            'ods' => 'excel',
            'csv' => 'excel',
            'ppt' => 'powerpoint',
            'pptx' => 'powerpoint',
            'odp' => 'powerpoint',
            'pdf' => 'pdf',
            'txt' => 'text',
            'rtf' => 'text',
            'json' => 'code',
            'xml' => 'code',
            'zip' => 'archive',
            'rar' => 'archive',
            '7z' => 'archive',
        ];

        return $categoryMap[$extension] ?? 'document';
    }
}
