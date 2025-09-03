<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\DatabaseBackupController;

class GoogleDriveBackupTest extends TestCase
{
    /** @test */
    public function it_attempts_to_upload_backup_to_google_drive_and_can_be_faked()
    {
        // Arrange: fake the google disk
        Storage::fake('google');

        // Create a dummy backup file
        $fileName = 'rcsBackUp-01-01-25-00-00-00.sql';
        $localPath = storage_path('app/backups/' . $fileName);
        if (! is_dir(dirname($localPath))) {
            mkdir(dirname($localPath), 0777, true);
        }
        file_put_contents($localPath, "-- dummy backup\n");

        // Act: call the controller wrapper
        $controller = new DatabaseBackupController();
        $result = $controller->uploadBackupToDrive($localPath, $fileName);

        // Assert: upload succeeded and file exists on the fake disk
        $this->assertTrue($result === true, 'Expected upload to return true');
        Storage::disk('google')->assertExists($fileName);

        // Cleanup
        @unlink($localPath);
    }
}
