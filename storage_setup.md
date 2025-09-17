# Avatar Storage Setup Instructions

## 1. Create Storage Link
Run this command to create the symbolic link from `public/storage` to `storage/app/public`:

```bash
php artisan storage:link
```

## 2. Verify Directory Structure
Ensure these directories exist:
- `storage/app/public/avatars/` (created automatically by Laravel Storage)
- `public/storage/` (symbolic link created by storage:link command)

## 3. File Permissions (Production)
Set proper permissions for production:

```bash
# For storage directory
chmod -R 775 storage/
chown -R www-data:www-data storage/

# For public directory
chmod -R 755 public/
chown -R www-data:www-data public/
```

## 4. Environment Configuration
Add to `.env` file if needed:

```
FILESYSTEM_DISK=local
```

## 5. Web Server Configuration
Ensure your web server can serve files from the `public/storage` directory.
