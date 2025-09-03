<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Exception;
use Carbon\Carbon;

class DatabaseBackupController extends Controller
{
    /**
     * Show the database backup page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $backups = $this->getBackups();
        $trashBackups = $this->getTrashBackups();
        return view('admin.backups.index', compact('backups', 'trashBackups'));
    }

    /**
     * Create a new database backup
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        try {
            // Get database configuration
            $dbHost = Config::get('database.connections.mysql.host');
            $dbUsername = Config::get('database.connections.mysql.username');
            $dbPassword = Config::get('database.connections.mysql.password');
            $dbName = Config::get('database.connections.mysql.database');
            $dbPort = Config::get('database.connections.mysql.port', 3306);

            // Generate backup filename with the requested format "rcsBackUp-mm-dd-yy-time"
            $now = Carbon::now();
            $dateFormat = $now->format('m-d-y');
            $timeFormat = $now->format('H-i-s');
            $filename = "rcsBackUp-{$dateFormat}-{$timeFormat}.sql";
            $storagePath = "backups/{$filename}";
            $fullBackupPath = storage_path("app/{$storagePath}");

            // Ensure backup directory exists with proper permissions
            $backupDir = dirname($fullBackupPath);
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0777, true);
                chmod($backupDir, 0777);
            }
            
            // Ensure the directory is writable
            if (!is_writable($backupDir)) {
                chmod($backupDir, 0777);
            }

            // First try with mysqldump (preferred method)
            try {
                // Build the mysqldump command
                $command = [
                    'mysqldump',
                    "--host={$dbHost}",
                    "--port={$dbPort}",
                    "--user={$dbUsername}",
                    "--password={$dbPassword}",
                    $dbName,
                    '--single-transaction',
                    '--quick',
                    '--lock-tables=false',
                ];

                // Create the process
                $process = new Process($command);
                $process->setTimeout(300); // 5 minutes timeout

                // Run the process and save the output to the backup file
                $process->run(function ($type, $buffer) use ($fullBackupPath) {
                    if ($type === Process::ERR) {
                        throw new ProcessFailedException(new Process(['error' => $buffer]));
                    } else {
                        file_put_contents($fullBackupPath, $buffer, FILE_APPEND);
                    }
                });

                if (!$process->isSuccessful()) {
                    throw new Exception('mysqldump failed: ' . $process->getErrorOutput());
                }
            } 
            // If mysqldump fails, fall back to PHP-based backup method
            catch (Exception $e) {
                $this->backupUsingPdo($fullBackupPath);
            }

            // Read the backup file content to ensure integrity during upload
            $backupContent = file_get_contents($fullBackupPath);
            if ($backupContent === false) {
                throw new Exception('Failed to read backup file for upload.');
            }

            // Attempt to upload to Google Drive (if disk is configured) with retry + optional retention
            $uploadResult = $this->uploadBackupToDrive($backupContent, $filename);

            $flashMessage = 'Database backup created successfully.';
            if ($uploadResult === true) {
                $flashMessage .= ' Backup was also uploaded to Google Drive.';
            } elseif (is_string($uploadResult)) {
                // uploadResult carries error string
                $flashMessage .= ' However, Google Drive upload failed: ' . $uploadResult;
            }

            return redirect()->route('database.backups')
                ->with('success', $flashMessage);

        } catch (Exception $e) {
            // Log detailed error for debugging
            Log::error('Database backup creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('database.backups')
                ->with('error', 'An error occurred while creating the backup. Please check the logs for details.');
        }
    }

    /**
     * Public wrapper to upload backup content to Google Drive and enforce retention if configured.
     * Returns true on success, an error string on failure, or null if upload is not configured.
     *
     * @param string $backupContent Raw SQL backup content to upload
     * @param string $destFilename  Destination filename to use in Google Drive
     * @return bool|string|null     True on success, error string on failure, or null if not configured
     */
    public function uploadBackupToDrive(string $backupContent, string $destFilename)
    {
        $result = $this->uploadToGoogleDrive($backupContent, $destFilename);

        if ($result === true) {
            // Apply retention if configured
            $keep = (int) (env('BACKUP_DRIVE_KEEP', 0));
            if ($keep > 0) {
                try {
                    $this->enforceDriveRetention($keep);
                } catch (\Throwable $t) {
                    Log::error('Failed to enforce Google Drive retention', [
                        'keep' => $keep,
                        'exception' => $t->getMessage(),
                    ]);
                }
            }
        }

        return $result;
    }

    /**
     * Create a database backup using PDO when mysqldump isn't available
     *
     * @param string $fullBackupPath Path to save the backup
     * @return void
     * @throws Exception
     */
    private function backupUsingPdo($fullBackupPath)
    {
        try {
            // Get database configuration
            $dbHost = Config::get('database.connections.mysql.host');
            $dbUsername = Config::get('database.connections.mysql.username');
            $dbPassword = Config::get('database.connections.mysql.password');
            $dbName = Config::get('database.connections.mysql.database');
            $dbPort = Config::get('database.connections.mysql.port', 3306);

            // Connect to the database
            $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8";
            $pdo = new \PDO($dsn, $dbUsername, $dbPassword, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
            ]);

            // Start the backup file with header comments
            $output = "-- Database Backup for {$dbName}\n";
            $output .= "-- Generated on " . date('Y-m-d H:i:s') . "\n";
            $output .= "-- Using PHP PDO Backup Method\n\n";
            $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
            
            file_put_contents($fullBackupPath, $output);

            // Get all tables
            $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                // Get the create table statement
                $tableStructure = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
                $createTable = $tableStructure['Create Table'] ?? $tableStructure['Create View'] ?? null;
                
                if (!$createTable) {
                    continue;
                }
                
                $output = "\n-- Table structure for table `{$table}`\n\n";
                $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $output .= $createTable . ";\n\n";
                
                file_put_contents($fullBackupPath, $output, FILE_APPEND);
                
                // Skip data for certain tables that might be large
                $skipDataTables = ['jobs', 'failed_jobs', 'logs', 'activity_log', 'sessions']; 
                if (in_array($table, $skipDataTables)) {
                    $output = "-- Data for table `{$table}` has been skipped\n\n";
                    file_put_contents($fullBackupPath, $output, FILE_APPEND);
                    continue;
                }
                
                // Get table data
                $rows = $pdo->query("SELECT * FROM `{$table}`");
                $columnCount = $rows->columnCount();
                
                if ($columnCount > 0) {
                    $output = "-- Data for table `{$table}`\n";
                    file_put_contents($fullBackupPath, $output, FILE_APPEND);
                    
                    $insertCounter = 0;
                    $insertHeader = null;
                    $insertValues = [];
                    
                    while ($row = $rows->fetch(\PDO::FETCH_NUM)) {
                        // Create column header only once
                        if ($insertHeader === null) {
                            $insertHeader = "INSERT INTO `{$table}` VALUES";
                        }
                        
                        // Format each row's values
                        $rowValues = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $rowValues[] = 'NULL';
                            } elseif (is_numeric($value)) {
                                $rowValues[] = $value;
                            } else {
                                $rowValues[] = $pdo->quote($value);
                            }
                        }
                        
                        $insertValues[] = '(' . implode(',', $rowValues) . ')';
                        $insertCounter++;
                        
                        // Write in chunks of 100 to avoid memory issues
                        if ($insertCounter >= 100) {
                            if ($insertHeader && !empty($insertValues)) {
                                $output = $insertHeader . "\n" . implode(",\n", $insertValues) . ";\n";
                                file_put_contents($fullBackupPath, $output, FILE_APPEND);
                            }
                            $insertCounter = 0;
                            $insertValues = [];
                        }
                    }
                    
                    // Insert any remaining rows
                    if ($insertHeader && !empty($insertValues)) {
                        $output = $insertHeader . "\n" . implode(",\n", $insertValues) . ";\n\n";
                        file_put_contents($fullBackupPath, $output, FILE_APPEND);
                    } else {
                        $output = "\n";
                        file_put_contents($fullBackupPath, $output, FILE_APPEND);
                    }
                }
            }
            
            // End the backup file
            $output = "\nSET FOREIGN_KEY_CHECKS=1;\n";
            file_put_contents($fullBackupPath, $output, FILE_APPEND);
            
        } catch (Exception $e) {
            throw new Exception('PDO backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Download a specific backup file - SYSTEM_ADMIN only
     *
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($filename)
    {
        try {
            // Validate the filename
            if (!preg_match('/^[\w\-\.]+\.sql$/', $filename)) {
                return redirect()->route('database.backups')
                    ->with('error', 'Invalid backup filename.');
            }

            $path = storage_path("app/backups/{$filename}");

            // Check if file exists
            if (!file_exists($path)) {
                return redirect()->route('database.backups')
                    ->with('error', 'Backup file not found.');
            }

            // Return the file as a download
            return Response::download($path, $filename, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename=' . $filename,
            ]);
        } catch (Exception $e) {
            return redirect()->route('database.backups')
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Delete a specific backup file - SYSTEM_ADMIN only
     *
     * @param string $filename
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($filename)
    {
        try {
            // Validate the filename to prevent path traversal attacks
            if (!preg_match('/^[\w\-\.]+\.sql$/', $filename)) {
                return redirect()->route('database.backups')
                    ->with('error', 'Invalid backup filename.');
            }

            $path = "backups/{$filename}";

            if (!Storage::exists($path)) {
                return redirect()->route('database.backups')
                    ->with('error', 'Backup file not found.');
            }

            // Soft delete: move the file to backups/trash with a unique name
            $moved = $this->moveToTrash($filename);
            if ($moved === true) {
                return redirect()->route('database.backups')
                    ->with('success', 'Backup moved to trash. You can restore it from trash later.');
            }

            // If moveToTrash returns an error string
            return redirect()->route('database.backups')
                ->with('error', is_string($moved) ? $moved : 'Failed to move backup to trash.');
        } catch (Exception $e) {
            return redirect()->route('database.backups')
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Move a backup file to trash with a unique name to avoid collisions
     *
     * @param string $filename
     * @return bool|string  True on success, error message on failure
     */
    private function moveToTrash(string $filename)
    {
        try {
            $src = "backups/{$filename}";
            $trashDir = 'backups/trash';

            if (!Storage::exists($src)) {
                return 'Backup file not found.';
            }

            // Ensure trash directory exists
            if (!Storage::exists($trashDir)) {
                Storage::makeDirectory($trashDir);
            }

            // Ensure unique name in trash (append timestamp if needed)
            $dest = $trashDir . '/' . $filename;
            if (Storage::exists($dest)) {
                $name = pathinfo($filename, PATHINFO_FILENAME);
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $timestamp = Carbon::now()->format('Ymd_His');
                $dest = sprintf('%s/%s-deleted-%s.%s', $trashDir, $name, $timestamp, $ext ?: 'sql');
            }

            if (Storage::move($src, $dest)) {
                Log::info('Backup moved to trash', ['source' => $src, 'dest' => $dest]);
                return true;
            }

            return 'Failed to move file to trash.';
        } catch (\Throwable $t) {
            Log::error('Error moving backup to trash', [
                'filename' => $filename,
                'exception' => $t->getMessage(),
            ]);
            return $t->getMessage();
        }
    }

    /**
     * Restore a backup from trash back to backups directory
     *
     * @param string $filename  The trashed filename (as it appears in trash)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($filename)
    {
        try {
            if (!preg_match('/^[\w\-\.]+\.sql$/', $filename)) {
                return redirect()->route('database.backups')
                    ->with('error', 'Invalid backup filename.');
            }

            $trashPath = "backups/trash/{$filename}";
            if (!Storage::exists($trashPath)) {
                return redirect()->route('database.backups')
                    ->with('error', 'Trashed backup not found.');
            }

            $restoreName = $filename;
            // If original name had a -deleted- suffix, optionally strip it for restore preference
            if (preg_match('/^(.*)-deleted-\d{8}_\d{6}\.sql$/', $filename, $m)) {
                $restoreName = $m[1] . '.sql';
            }

            $destPath = 'backups/' . $restoreName;
            if (Storage::exists($destPath)) {
                // Keep both by appending timestamp
                $name = pathinfo($restoreName, PATHINFO_FILENAME);
                $ext = pathinfo($restoreName, PATHINFO_EXTENSION);
                $timestamp = Carbon::now()->format('Ymd_His');
                $destPath = sprintf('backups/%s-restored-%s.%s', $name, $timestamp, $ext ?: 'sql');
            }

            if (Storage::move($trashPath, $destPath)) {
                return redirect()->route('database.backups')
                    ->with('success', 'Backup restored successfully.');
            }

            return redirect()->route('database.backups')
                ->with('error', 'Failed to restore backup.');
        } catch (Exception $e) {
            return redirect()->route('database.backups')
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a backup from trash
     *
     * @param string $filename  The trashed filename
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($filename)
    {
        try {
            if (!preg_match('/^[\w\-\.]+\.sql$/', $filename)) {
                return redirect()->route('database.backups')
                    ->with('error', 'Invalid backup filename.');
            }

            $trashPath = "backups/trash/{$filename}";
            if (!Storage::exists($trashPath)) {
                return redirect()->route('database.backups')
                    ->with('error', 'Trashed backup not found.');
            }

            if (Storage::delete($trashPath)) {
                return redirect()->route('database.backups')
                    ->with('success', 'Backup permanently deleted from trash.');
            }

            return redirect()->route('database.backups')
                ->with('error', 'Failed to permanently delete backup.');
        } catch (Exception $e) {
            return redirect()->route('database.backups')
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Get trashed backups list (for use in a future trash view)
     *
     * @return array
     */
    private function getTrashBackups(): array
    {
        $backups = [];

        if (Storage::exists('backups/trash')) {
            $files = Storage::files('backups/trash');

            foreach ($files as $file) {
                if (Str::endsWith($file, '.sql')) {
                    $filename = basename($file);
                    $size = Storage::size($file);
                    $lastModified = Storage::lastModified($file);

                    $backups[] = [
                        'filename' => $filename,
                        'size' => $this->formatFileSize($size),
                        'date' => Carbon::createFromTimestamp($lastModified)->format('Y-m-d H:i:s'),
                        'age' => Carbon::createFromTimestamp($lastModified)->diffForHumans(),
                    ];
                }
            }

            usort($backups, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
        }

        return $backups;
    }

    /**
     * Get all available backup files
     *
     * @return array
     */
    private function getBackups()
    {
        $backups = [];
        
        if (Storage::exists('backups')) {
            $files = Storage::files('backups');
            
            foreach ($files as $file) {
                if (Str::endsWith($file, '.sql')) {
                    $filename = basename($file);
                    $size = Storage::size($file);
                    $lastModified = Storage::lastModified($file);
                    
                    $backups[] = [
                        'filename' => $filename,
                        'size' => $this->formatFileSize($size),
                        'date' => Carbon::createFromTimestamp($lastModified)->format('Y-m-d H:i:s'),
                        'age' => Carbon::createFromTimestamp($lastModified)->diffForHumans(),
                    ];
                }
            }
            
            // Sort backups by date (newest first)
            usort($backups, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
        }
        
        return $backups;
    }

    /**
     * Format file size to human-readable format
     *
     * @param int $bytes
     * @return string
     */
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Upload backup content to Google Drive.
     * Attempts using the configured 'google' filesystem disk with retries; on failure or when
     * the disk is not configured, falls back to direct Google Drive HTTP API upload.
     *
     * @param string $backupContent Raw SQL backup content to upload
     * @param string $destFilename  Destination filename to use in Drive
     * @return bool|string|null     True on success, error string on failure, or null if not configured
     */
    private function uploadToGoogleDrive(string $backupContent, string $destFilename)
    {
        try {
            // Verify the disk is configured in filesystems.php
            $disks = Config::get('filesystems.disks', []);
            if (!array_key_exists('google', $disks)) {
                // Disk not configured — fallback to direct Google Drive API
                $res = $this->uploadViaGoogleApi($backupContent, $destFilename);
                if ($res !== true) {
                    Log::error('Google Drive API upload (disk missing) failed', ['result' => $res]);
                }
                return $res;
            }

            // Retry loop for uploading via disk
            $maxAttempts = 3;
            $attempt = 0;
            while ($attempt < $maxAttempts) {
                $attempt++;
                try {
                    $putOk = Storage::disk('google')->put($destFilename, $backupContent);
                    if ($putOk === true) {
                        Log::info('Backup uploaded to Google Drive', [
                            'filename' => $destFilename,
                            'attempt' => $attempt,
                        ]);
                        return true;
                    }
                    Log::error('Google Drive upload returned false', [
                        'filename' => $destFilename,
                        'attempt' => $attempt,
                    ]);
                } catch (\Throwable $t) {
                    Log::error('Google Drive upload attempt failed', [
                        'filename' => $destFilename,
                        'attempt' => $attempt,
                        'exception' => $t->getMessage(),
                    ]);
                }
                // Exponential backoff: 2, 4 seconds
                sleep(2 ** $attempt);
            }

            // After disk attempts, try API as fallback (single attempt inside method)
            $fallback = $this->uploadViaGoogleApi($backupContent, $destFilename);
            if ($fallback === true) {
                Log::info('Backup uploaded to Google Drive via API fallback', [
                    'filename' => $destFilename,
                ]);
                return true;
            }
            // If fallback is null, API likely not configured (missing/invalid GOOGLE_* env)
            if (is_null($fallback)) {
                return 'Google Drive not configured or OAuth token fetch failed. Verify GOOGLE_DRIVE_CLIENT_ID/SECRET/REFRESH_TOKEN and clear config cache.';
            }
            // Otherwise return the specific error string
            return is_string($fallback) ? $fallback : 'Unknown error during upload.';
        } catch (\Throwable $t) {
            // If driver isn't supported or any error from disk upload, fallback to API
            $fallback = $this->uploadViaGoogleApi($backupContent, $destFilename);
            if ($fallback === true || is_null($fallback)) {
                return $fallback ?? 'Upload fallback performed with no status.';
            }
            // Return the specific error from fallback otherwise
            Log::error('Google Drive upload failed after disk attempt and API fallback', [
                'exception' => $t->getMessage(),
                'fallback' => $fallback,
            ]);
            return is_string($fallback) ? $fallback : $t->getMessage();
        }
    }

    /**
     * Obtain an access token from Google's OAuth2 token endpoint using a refresh token.
     *
     * @return string|null Access token or null on failure
     */
    private function getGoogleAccessToken(): ?string
    {
        $clientId = Config::get('filesystems.disks.google.clientId');
        $clientSecret = Config::get('filesystems.disks.google.clientSecret');
        $refreshToken = Config::get('filesystems.disks.google.refreshToken');

        if (!$clientId || !$clientSecret || !$refreshToken) {
            return null;
        }

        $resp = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        if (!$resp->successful()) {
            Log::error('Google OAuth token refresh failed', [
                'status' => $resp->status(),
                'body' => $resp->body(),
            ]);
            return null;
        }

        return $resp->json('access_token');
    }

    /**
     * Upload to Google Drive via HTTP API (without filesystem driver).
     * Two-step approach: create file metadata, then upload media content.
     *
     * @param string $backupContent Raw SQL backup content to upload
     * @param string $destFilename  Destination filename to use in Drive
     * @return bool|string|null     True on success, error string on failure, or null if not configured
     */
    private function uploadViaGoogleApi(string $backupContent, string $destFilename)
    {
        try {
            $token = $this->getGoogleAccessToken();
            if (!$token) {
                // Not properly configured for API either
                return null;
            }

            $folderId = Config::get('filesystems.disks.google.folderId');

            // Optional: validate folderId if provided
            if (!empty($folderId)) {
                $folderCheck = Http::withToken($token)
                    ->acceptJson()
                    ->get('https://www.googleapis.com/drive/v3/files/' . urlencode($folderId) . '?fields=id,mimeType&supportsAllDrives=true');
                if ($folderCheck->status() == 404) {
                    Log::error('GOOGLE_DRIVE_FOLDER_ID not found', ['folderId' => $folderId]);
                    return 'Provided GOOGLE_DRIVE_FOLDER_ID was not found (404).';
                }
                if (!$folderCheck->successful()) {
                    Log::error('Failed to validate GOOGLE_DRIVE_FOLDER_ID', ['response' => $folderCheck->body()]);
                    return 'Failed to validate GOOGLE_DRIVE_FOLDER_ID: ' . $folderCheck->body();
                }
                $mime = $folderCheck->json('mimeType');
                if ($mime !== 'application/vnd.google-apps.folder') {
                    Log::error('GOOGLE_DRIVE_FOLDER_ID is not a folder', ['mimeType' => $mime]);
                    return 'GOOGLE_DRIVE_FOLDER_ID does not point to a folder.';
                }
            }

            // 1) Create file metadata
            $metadata = [
                'name' => $destFilename,
            ];
            if (!empty($folderId)) {
                $metadata['parents'] = [$folderId];
            }

            $metaResp = Http::withToken($token)
                ->acceptJson()
                ->post('https://www.googleapis.com/drive/v3/files?supportsAllDrives=true', $metadata);

            if (!$metaResp->successful()) {
                Log::error('Failed to create Drive file metadata', [
                    'status' => $metaResp->status(),
                    'body' => $metaResp->body(),
                ]);
                return 'Failed to create Drive file metadata: ' . $metaResp->body();
            }

            $fileId = $metaResp->json('id');
            if (!$fileId) {
                return 'Drive API did not return a file ID (check folder ID and permissions).';
            }

            // 2) Upload media content
            $uploadUrl = sprintf('https://www.googleapis.com/upload/drive/v3/files/%s?uploadType=media&supportsAllDrives=true', urlencode($fileId));
            $uploadResp = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/octet-stream'])
                ->withBody($backupContent, 'application/octet-stream')
                ->patch($uploadUrl);

            if (!$uploadResp->successful()) {
                Log::error('Failed to upload Drive file content', [
                    'status' => $uploadResp->status(),
                    'body' => $uploadResp->body(),
                ]);
                return 'Failed to upload file content: ' . $uploadResp->body();
            }

            return true;
        } catch (\Throwable $t) {
            return $t->getMessage();
        }
    }

    /**
     * Enforce retention on Google Drive by keeping only the latest N backup files.
     * Uses the configured 'google' disk. If folderId is set in the adapter, it is the root.
     *
     * @param int $keep
     * @return void
     */
    private function enforceDriveRetention(int $keep): void
    {
        if ($keep <= 0) {
            return;
        }

        $disks = Config::get('filesystems.disks', []);
        if (!array_key_exists('google', $disks)) {
            return;
        }

        $items = collect(Storage::disk('google')->listContents('', false))
            ->filter(function ($item) {
                // Support both array and object style items depending on adapter
                $type = is_array($item) ? ($item['type'] ?? null) : ($item->type ?? null);
                $path = is_array($item) ? ($item['path'] ?? '') : ($item->path ?? '');
                return $type === 'file' && str_ends_with($path, '.sql') && str_starts_with(basename($path), 'rcsBackUp-');
            })
            ->map(function ($item) {
                $path = is_array($item) ? ($item['path'] ?? '') : ($item->path ?? '');
                $timestamp = is_array($item) ? ($item['timestamp'] ?? null) : ($item->timestamp ?? null);
                $lastModified = is_null($timestamp) ? time() : (int) $timestamp;
                return [
                    'path' => $path,
                    'lastModified' => $lastModified,
                ];
            })
            ->sortByDesc('lastModified')
            ->values();

        if ($items->count() <= $keep) {
            return; // nothing to delete
        }

        $toDelete = $items->slice($keep);
        foreach ($toDelete as $file) {
            try {
                Storage::disk('google')->delete($file['path']);
                Log::info('Deleted old backup from Google Drive due to retention', [
                    'path' => $file['path'],
                ]);
            } catch (\Throwable $t) {
                Log::error('Failed to delete old backup from Google Drive', [
                    'path' => $file['path'],
                    'exception' => $t->getMessage(),
                ]);
            }
        }
    }

    /**
     * Static version of formatFileSize for use in views
     *
     * @param int $bytes
     * @return string
     */
    public static function formatFileSizeStatic($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
