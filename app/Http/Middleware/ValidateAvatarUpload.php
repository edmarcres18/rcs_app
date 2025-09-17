<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateAvatarUpload
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only validate if avatar file is present
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            
            // Additional security checks
            if (!$avatar->isValid()) {
                return back()->withErrors([
                    'avatar' => 'The uploaded file is corrupted or invalid.'
                ])->withInput();
            }

            // Check for malicious file types by examining file content
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $avatar->getPathname());
            finfo_close($finfo);

            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($mimeType, $allowedMimes)) {
                return back()->withErrors([
                    'avatar' => 'Invalid file type detected. Only image files are allowed.'
                ])->withInput();
            }

            // Check file signature (magic bytes) for additional security
            $handle = fopen($avatar->getPathname(), 'rb');
            $header = fread($handle, 12);
            fclose($handle);

            $validSignatures = [
                'jpeg' => ["\xFF\xD8\xFF"],
                'png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
                'gif' => ["GIF87a", "GIF89a"],
                'webp' => ["RIFF"]
            ];

            $isValidSignature = false;
            foreach ($validSignatures as $type => $signatures) {
                foreach ($signatures as $signature) {
                    if (strpos($header, $signature) === 0) {
                        $isValidSignature = true;
                        break 2;
                    }
                }
            }

            if (!$isValidSignature) {
                return back()->withErrors([
                    'avatar' => 'File appears to be corrupted or is not a valid image.'
                ])->withInput();
            }
        }

        return $next($request);
    }
}
