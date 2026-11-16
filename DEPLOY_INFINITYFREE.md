# Deploying SanFran HealthSys to InfinityFree

This guide targets InfinityFree free hosting, where `htdocs` is the web root and shell/Artisan access is unavailable. Treat this as a demo or test deployment only. Do not store real patient or health information on free hosting; use a provider with appropriate security controls, backups, and operational support for real records.

## Follow-up reminders

The app creates in-app reminders when a follow-up is set within three days. For a farther date, the scheduled command creates the reminder once it enters the three-day window. The notification bell also checks for due reminders at most once per minute while a midwife is using the app, and opening the Notifications page runs the same check. For example, a follow-up saved on November 13 for November 17 will be added to the midwife's notifications when the app is opened on November 14, even if no scheduler process is running.

For reminders to be generated while nobody is using the site, Laravel's scheduler must keep running:

- When using `composer run dev`, the scheduler runs alongside the local server, queue listener, and Vite.
- When using XAMPP/Apache, open a separate terminal in the project folder and run `C:\xampp\php\php.exe artisan schedule:work` while Apache is running.

Keep that terminal open. To run the reminder check immediately, run `C:\xampp\php\php.exe artisan records:send-schedule-reminders`.

## 1. Prepare the release on your PC

In PowerShell, go to this project and use XAMPP's PHP before running Composer. XAMPP PHP must be 8.2 or newer. This also avoids using another PHP installation (for example, Herd) from your `PATH`:

```powershell
Set-Location 'C:\xampp\htdocs\sanfran-healthsys'
$env:Path = 'C:\xampp\php;' + $env:Path
php -v
```

In `C:\xampp\php\php.ini`, make sure these extensions are enabled (remove the leading `;` if present), then save the file:

```ini
extension=gd
extension=zip
```

Open a new PowerShell terminal after changing `php.ini`, return to the project root, and run:

```powershell
composer install --no-dev --no-interaction
npm.cmd ci
npm.cmd run build
php artisan optimize:clear
```

Confirm that `vendor/autoload.php` and `public/build/manifest.json` exist. Both `vendor` and `public/build` are ignored by Git in this project, so they must still be included in the FTP upload. Do not upload `node_modules`.

InfinityFree may reject PHP files larger than 1 MB. Check the generated Composer class map:

```powershell
Get-Item .\vendor\composer\autoload_classmap.php | Select-Object Name, Length
```

If that file is over 1 MB or is missing after upload, regenerate the autoloader and upload the resulting `vendor` folder again:

```powershell
composer dump-autoload --no-dev --optimize=false
```

## 2. Create a MySQL schema

1. In InfinityFree's control panel, create a MySQL database and note its host, database name, username, and password. Use the exact host shown there; it is not necessarily `localhost`.
2. In XAMPP, create a separate empty local MySQL database for the release. Temporarily point the local `.env` to it (`DB_CONNECTION=mysql` and the local XAMPP credentials).
3. Run the migrations locally:

   ```powershell
   php artisan migrate --force
   ```

4. In local phpMyAdmin, export the migrated database as **Structure only** (no data). Import that SQL file into the InfinityFree database using its phpMyAdmin.
5. Keep the local `.env` pointed at this temporary MySQL database until you finish creating and exporting the initial admin account below. Never upload the local `.env` or a local SQLite database.

This app has no public registration route. After migrating the separate, empty local MySQL database, create the first account locally:

```powershell
php artisan tinker
```

At the Tinker prompt, run this after replacing the username, email, and password with your own values. Use a unique temporary password and do not paste it into chat or commit it:

```php
\App\Models\User::create([
   'name' => 'System Admin',
   'username' => 'YOUR_ADMIN_USERNAME',
   'email' => 'YOUR_ADMIN_EMAIL',
   'password' => 'YOUR_UNIQUE_TEMPORARY_PASSWORD',
   'role' => \App\Enums\UserRole::SystemAdmin,
   'is_active' => true,
]);
```

The `User` model hashes the password. In local phpMyAdmin, export only this new `users` row as SQL data and import that row into the production database. Do not export the rest of the local database. Do not run `DatabaseSeeder`: it creates demo users with the password `password` and sample patient records. Change the temporary password after the first login, then restore your local `.env` to its normal development settings.

## 3. Create the production `.env`

Create a fresh `.env` for the server; do not reuse or upload your development `.env`. Generate a key locally:

```powershell
php artisan key:generate --show
```

Copy the displayed key into the server `.env`. Configure at least:

```dotenv
APP_NAME="SanFran HealthSys"
APP_ENV=production
APP_KEY=base64:PASTE_THE_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://YOUR_DOMAIN

DB_CONNECTION=mysql
DB_HOST=HOST_FROM_INFINITYFREE
DB_PORT=3306
DB_DATABASE=DATABASE_NAME
DB_USERNAME=DATABASE_USERNAME
DB_PASSWORD=DATABASE_PASSWORD

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

IPROG_API_TOKEN=YOUR_IPROG_TOKEN
VAPID_PUBLIC_KEY=YOUR_VAPID_PUBLIC_KEY
VAPID_PRIVATE_KEY=YOUR_VAPID_PRIVATE_KEY
VAPID_SUBJECT=mailto:YOUR_CONTACT_EMAIL
```

Set the iProg token only if you intend to send SMS. Generate a VAPID key pair locally with `php artisan tinker --execute="dump(\Minishlink\WebPush\VAPID::createVapidKeys());"`, then copy the returned public and private keys into the production `.env`. On XAMPP for Windows, set `OPENSSL_CONF` to `C:\xampp\php\extras\ssl\openssl.cnf` in PowerShell before generating the pair. Keep the same key pair after deployment; replacing it will invalidate existing phone subscriptions. Keep the private key secret and never commit or paste it into chat.

Web Push requires HTTPS and outbound HTTPS access from the server to browser push services. Any public visitor can subscribe without a staff account, but each user must enable offline alerts and accept the browser permission prompt once. On iOS, install the site to the Home Screen first and enable alerts from the installed shortcut; supported desktop and Android browsers can subscribe from the website. After opt-in, the service worker can show the announcement title and summary while the site or shortcut is closed or offline; opening the full post still requires a connection. Home-screen icon number badges depend on browser and phone support. If the host blocks outbound requests to push services, use hosting that permits them.

## 4. Upload to `htdocs`

Upload the project so the layout is directly under `htdocs`, not nested inside another project folder:

```text
htdocs/
|-- app/
|-- bootstrap/
|-- config/
|-- database/
|-- public/
|   |-- build/
|   |-- index.php
|   `-- .htaccess
|-- resources/
|-- routes/
|-- storage/
|-- vendor/
|-- .env
|-- .htaccess
`-- artisan
```

Upload the production `.env`, including hidden files if your FTP client hides dotfiles. Keep `public/index.php` and `public/.htaccess` inside `public`; do not move them to `htdocs`.

Do not upload `.git`, `node_modules`, local `.env` backups, `database/database.sqlite`, `public/hot`, or `public/storage` symlinks. Keep the `storage` and `bootstrap/cache` directories present and writable by PHP. Do not set permissions to `777`.

Create a second `.htaccess` directly in `htdocs` with these rules (the storage rule must come first):

```apache
RewriteEngine On
RewriteRule ^storage/(.*)$ /storage/app/public/$1 [END]
RewriteRule (.*) /public/$1 [L]
```

This routes requests into Laravel's `public` directory and serves uploaded files from `storage/app/public`. Do not replace `public/.htaccess`. See the [InfinityFree Laravel guide](https://forum.infinityfree.com/t/how-to-install-a-laravel-site-on-infinityfree/118578).

## 5. Verify the deployment

1. Select PHP 8.2 or newer in the InfinityFree control panel. Check that required extensions are enabled if Composer reports missing platform requirements locally (`composer check-platform-reqs --no-dev`).
2. Enable HTTPS and make `APP_URL` use the HTTPS domain.
3. Visit the homepage and login page. Sign in with the initial admin, then change its password. On a staff record list, type a patient name or a searchable word and confirm matching names appear. Suggestions use a same-origin authenticated request; if they do not appear, verify that the latest `routes/web.php`, `app/Http/Controllers/SearchSuggestionController.php`, `resources/views/components/record-search-input.blade.php`, `resources/js/app.js`, and complete rebuilt `public/build` assets were uploaded. Also check that production session files are writable and `APP_URL` uses the HTTPS domain.
4. Test a small announcement image and profile picture upload, then confirm each image loads. Test a report/PDF and SMS only with non-sensitive dummy data.
5. Confirm errors are not shown to visitors (`APP_DEBUG=false`). If Laravel reports a stale configuration or view, remove generated files inside `bootstrap/cache`, `storage/framework/views`, and `storage/framework/cache/data` over FTP; preserve each directory and `.gitignore` files. Do not delete sessions, logs, or uploaded images.
6. Export a database backup and download uploaded files regularly. Test that a backup can be restored.

## Limits and updates

InfinityFree free hosting does not provide SSH/Artisan, cron jobs, or persistent queue workers. On InfinityFree, a reminder is created when an active midwife opens the app after the record enters its three-day window; the bell also checks while the app remains open. If nobody visits during that window, it will be created on the next visit, not in the background on the exact day. Keep `CACHE_STORE=file` and ensure `storage/framework/cache/data` is writable so the request-triggered check can be throttled. Queue jobs must run synchronously. For dependable background reminders even when the site is idle, use a host that can run Laravel's scheduler every minute. On a server with cron access, configure:

```cron
* * * * * cd /path/to/sanfran-healthsys && php artisan schedule:run >> /dev/null 2>&1
```

The schedule uses the `Asia/Manila` timezone and creates in-app reminders for active midwives when a record's date is within the next three days. For real patient records or dependable operation, use a suitable paid host with cron access, backups, and operational support. Database schema changes must be exported from local MySQL and imported with phpMyAdmin for each InfinityFree release. Free-host CPU, file-count, and upload limits can also affect Laravel deployments.

For each code update, rebuild with `composer install --no-dev`, `npm.cmd ci`, and `npm.cmd run build` locally; upload the changed application files plus the complete `vendor` and `public/build` contents. Back up the database and uploads before replacing files.
