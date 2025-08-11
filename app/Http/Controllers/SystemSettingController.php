<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SystemSettingController extends Controller
{
    /**
     * Show the form for editing the system settings.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('system-settings.index');
    }

    /**
     * Show the form for editing the mail settings.
     *
     * @return \Illuminate\View\View
     */
    public function mail()
    {
        return view('system-settings.mail');
    }

    /**
     * Update the mail settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateMail(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'mail_mailer' => 'sometimes|required|string|max:255',
                'mail_host' => 'sometimes|required|string|max:255',
                'mail_port' => 'sometimes|required|numeric',
                'mail_username' => 'nullable|string|max:255',
                'mail_password' => 'nullable|string|max:255',
                'mail_encryption' => 'nullable|string|max:255',
                'mail_from_address' => 'sometimes|required|email|max:255',
            ]);

            $this->updateEnvVariable('MAIL_MAILER', $validatedData['mail_mailer']);
            $this->updateEnvVariable('MAIL_HOST', $validatedData['mail_host']);
            $this->updateEnvVariable('MAIL_PORT', $validatedData['mail_port']);
            $this->updateEnvVariable('MAIL_USERNAME', $validatedData['mail_username'] ?? '');
            if ($request->filled('mail_password')) {
                $this->updateEnvVariable('MAIL_PASSWORD', $validatedData['mail_password']);
            }
            $this->updateEnvVariable('MAIL_ENCRYPTION', $validatedData['mail_encryption'] ?? '');
            $this->updateEnvVariable('MAIL_FROM_ADDRESS', $validatedData['mail_from_address']);

            Artisan::call('config:clear');
            Artisan::call('config:cache');

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Mail settings updated successfully.']);
            }

            return redirect()->route('admin.system-settings.mail')->with('success', 'Mail settings updated successfully.');

        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating mail settings: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to update mail settings. Please try again.'], 500);
            }
            return redirect()->back()->with('error', 'Failed to update mail settings. Please try again.')->withInput();
        }
    }

    /**
     * Update the system settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'app_name' => 'sometimes|required|string|max:255',
                'app_logo' => 'nullable|image|mimes:png,jpg,jpeg',
                'auth_logo' => 'nullable|image|mimes:png,jpg,jpeg',
            ]);

            // Ensure the public storage symlink exists (public/storage) - best effort
            if (!File::exists(public_path('storage'))) {
                try {
                    Artisan::call('storage:link');
                } catch (\Exception $e) {
                    Log::warning('Unable to create storage symlink: ' . $e->getMessage());
                }
            }

            // Ensure the logo directory exists on the public disk
            $logoDirectory = 'app_logo';
            $publicDisk = Storage::disk('public');
            if (!$publicDisk->exists($logoDirectory)) {
                $publicDisk->makeDirectory($logoDirectory);
            }

            // Also ensure legacy public path exists as a fallback
            $legacyPublicDir = public_path('images/app_logo');
            if (!File::isDirectory($legacyPublicDir)) {
                File::makeDirectory($legacyPublicDir, 0755, true);
            }

            $newAppName = null;
            if ($request->has('app_name')) {
                $newAppName = $validatedData['app_name'];
                $this->updateEnvVariable('APP_NAME', $newAppName);
            }

            $appLogoPath = null;
            if ($request->hasFile('app_logo')) {
                $publicDisk->delete($logoDirectory . '/logo.png');
                $request->file('app_logo')->storeAs($logoDirectory, 'logo.png', 'public');

                // Copy to legacy public path as fallback for environments without storage symlink
                try {
                    $storedPath = $publicDisk->path($logoDirectory . '/logo.png');
                    $legacyTarget = $legacyPublicDir . DIRECTORY_SEPARATOR . 'logo.png';
                    if (File::exists($legacyTarget)) {
                        File::delete($legacyTarget);
                    }
                    File::copy($storedPath, $legacyTarget);
                } catch (\Exception $e) {
                    Log::warning('Failed to copy app logo to legacy public path: ' . $e->getMessage());
                }

                // Choose the best accessible URL
                $appLogoPath = file_exists(public_path('storage/' . $logoDirectory . '/logo.png'))
                    ? versioned_asset('storage/' . $logoDirectory . '/logo.png')
                    : versioned_asset('images/app_logo/logo.png');
            }

            $authLogoPath = null;
            if ($request->hasFile('auth_logo')) {
                $publicDisk->delete($logoDirectory . '/auth_logo.png');
                $request->file('auth_logo')->storeAs($logoDirectory, 'auth_logo.png', 'public');

                // Copy to legacy public path as fallback
                try {
                    $storedPath = $publicDisk->path($logoDirectory . '/auth_logo.png');
                    $legacyTarget = $legacyPublicDir . DIRECTORY_SEPARATOR . 'auth_logo.png';
                    if (File::exists($legacyTarget)) {
                        File::delete($legacyTarget);
                    }
                    File::copy($storedPath, $legacyTarget);
                } catch (\Exception $e) {
                    Log::warning('Failed to copy auth logo to legacy public path: ' . $e->getMessage());
                }

                $authLogoPath = file_exists(public_path('storage/' . $logoDirectory . '/auth_logo.png'))
                    ? versioned_asset('storage/' . $logoDirectory . '/auth_logo.png')
                    : versioned_asset('images/app_logo/auth_logo.png');
            }

            try {
                Artisan::call('config:clear');
                Artisan::call('config:cache');
            } catch (\Exception $e) {
                Log::warning('Failed to refresh config cache: ' . $e->getMessage());
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'System settings updated successfully.',
                    'new_app_name' => $newAppName,
                    'app_logo_url' => $appLogoPath,
                    'auth_logo_url' => $authLogoPath,
                ]);
            }

            return redirect()->route('admin.system-settings.index')->with('success', 'System settings updated successfully.');

        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating system settings: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to update system settings. Please try again.'], 500);
            }
            return redirect()->back()->with('error', 'Failed to update system settings. Please try again.')->withInput();
        }
    }

    /**
     * Update or add an environment variable in the .env file.
     *
     * @param  string  $key
     * @param  string  $value
     * @return void
     */
    protected function updateEnvVariable(string $key, string $value)
    {
        $envFilePath = base_path('.env');

        $value = preg_match('/\s/', $value) ? '"' . $value . '"' : $value;

        $envFileContent = File::get($envFilePath);

        $newEntry = "{$key}={$value}";

        $pattern = "/^{$key}=.*$/m";

        if (preg_match($pattern, $envFileContent)) {
            $envFileContent = preg_replace($pattern, $newEntry, $envFileContent, 1);
        } else {
            $envFileContent .= "\n" . $newEntry;
        }

        File::put($envFilePath, $envFileContent);
    }
}
