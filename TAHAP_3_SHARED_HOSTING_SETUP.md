# 📦 Tahap 3: Setup Project Laravel untuk Shared Hosting

**Panduan Lengkap Deploy Air Bersih ke Shared Hosting**

---

## Daftar Isi

1. [Struktur Folder Proyek](#struktur-folder-proyek)
2. [Konfigurasi Environment](#konfigurasi-environment)
3. [File Konfigurasi Penting](#file-konfigurasi-penting)
4. [Persiapan Build](#persiapan-build)
5. [Upload ke Shared Hosting](#upload-ke-shared-hosting)
6. [Setup Document Root](#setup-document-root)
7. [Troubleshooting](#troubleshooting)

---

## Struktur Folder Proyek

### Untuk Shared Hosting (Rekomendasi)

```
public_html/
├── public/                    ← Document root (ini yang diakses browser)
│   ├── index.php
│   ├── robots.txt
│   ├── .htaccess
│   └── uploads/              ← Folder untuk file upload
├── app/
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env                       ← JANGAN COMMIT
├── .env.example
├── .htaccess                  ← Important untuk rewrite URL
├── artisan
├── composer.json
├── composer.lock
└── README.md
```

### Alternatif: Jika Document Root Terbatas

```
public_html/
└── index.php                  ← Redirect ke /home/user/laravel/public/index.php

/home/user/laravel/           ← Folder utama (di luar public_html)
├── public/
├── app/
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
├── .htaccess
├── artisan
└── composer.json
```

---

## Konfigurasi Environment

### File .env untuk Shared Hosting

Buat file `.env` di root folder dengan konfigurasi:

```env
# APP Configuration
APP_NAME="Air Bersih"
APP_ENV=production
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX=
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database_cpanel
DB_USERNAME=user_database
DB_PASSWORD=password_database_anda

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_DRIVER=file

# Queue Configuration
QUEUE_CONNECTION=sync

# Mail Configuration (jika menggunakan SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email@gmail.com
MAIL_PASSWORD=app_password_anda
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Air Bersih"

# Upload Configuration
UPLOAD_PATH=public/uploads
MAX_UPLOAD_SIZE=5242880

# Security
APP_TIMEZONE=Asia/Jakarta
```

### File .env.example (untuk repository)

```env
APP_NAME="Air Bersih"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=database_name
DB_USERNAME=db_user
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_DRIVER=file

QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Air Bersih"

UPLOAD_PATH=public/uploads
MAX_UPLOAD_SIZE=5242880

APP_TIMEZONE=Asia/Jakarta
```

---

## File Konfigurasi Penting

### 1. Public/.htaccess (untuk URL Rewriting)

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 2. Root .htaccess (Protect Non-Public Folders)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Protect sensitive files
<FilesMatch "\.env|\.env\..*|composer\.(json|lock)">
    Deny from all
</FilesMatch>

# Protect .git folder
<DirectoryMatch "^/\.git/">
    Deny from all
</DirectoryMatch>
```

### 3. Config/app.php

```php
'timezone' => env('APP_TIMEZONE', 'UTC'),
'providers' => [
    // ... Laravel providers
    // Custom providers
    App\Providers\AppServiceProvider::class,
],
```

### 4. Config/database.php

```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', 'localhost'),
    'port' => env('DB_PORT', 3306),
    'database' => env('DB_DATABASE', 'air_bersih'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => [
        \PDO::ATTR_EMULATE_PREPARES => true,
    ],
],
```

### 5. Config/filesystems.php

```php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'private',
    ],

    'public' => [
        'driver' => 'local',
        'root' => public_path('uploads'),
        'url' => env('APP_URL').'/uploads',
        'visibility' => 'public',
    ],
],
```

---

## Persiapan Build

### 1. Bersihkan Development Dependencies

```bash
# Remove dev dependencies untuk production
composer install --no-dev --optimize-autoloader

# Clear temporary caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### 2. Buat Production Build

```bash
# Generate production app key (jika belum ada)
php artisan key:generate

# Optimize configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Dump autoloader for production
composer dump-autoload --optimize
```

### 3. Build Assets (Jika menggunakan Vite/NPM)

```bash
# Install dependencies
npm install

# Build untuk production
npm run build

# Output akan di public/build/ atau public/dist/
```

### 4. Siapkan Storage Permissions

```bash
# Create symlink (jika diperlukan)
php artisan storage:link

# Set permissions (Linux/SSH)
chmod -R 775 storage bootstrap/cache
chmod -R 755 public/uploads
```

---

## Upload ke Shared Hosting

### Metode 1: Menggunakan cPanel File Manager

#### Step 1: Persiapkan File Lokal
```bash
# Di folder project lokal
1. Hapus folder: vendor/, node_modules/, bootstrap/cache/*
2. Hapus file: .git, .env (hanya simpan .env.example)
3. Buat file: .htaccess (di root)
4. Buat file: .env (akan dibuat di server)
```

#### Step 2: Compress & Upload
```bash
# Windows PowerShell
Compress-Archive -Path ".\*" -DestinationPath "air-bersih.zip"

# macOS/Linux
zip -r air-bersih.zip . -x "node_modules/*" "vendor/*" ".git/*"
```

#### Step 3: Upload via cPanel
```
1. Login cPanel
2. File Manager
3. Navigate ke: public_html/ (atau folder custom)
4. Upload file air-bersih.zip
5. Extract All
6. Hapus file air-bersih.zip
```

#### Step 4: Setup SSH (Recommended)
```bash
# Buka Terminal SSH dari cPanel
ssh user@yourdomain.com

# Navigate ke folder project
cd public_html

# Install composer dependencies
composer install --no-dev --optimize-autoloader
```

### Metode 2: Menggunakan Git (Jika Server Support)

```bash
# Setup di cPanel SSH Terminal
cd public_html
git clone https://github.com/yourrepo/air-bersih.git .

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Copy .env
cp .env.example .env

# Setup permissions
chmod -R 775 storage bootstrap/cache
```

### Metode 3: Menggunakan FTP

```bash
# Persiapkan file lokal
1. Install Composer dependencies: composer install --no-dev
2. Build assets: npm run build
3. Persiapkan .env untuk production

# Upload via FTP Client (FileZilla, WinSCP, dll)
1. Upload semua folder ke public_html/
2. Upload .env ke root folder
3. Set folder permissions: 775 untuk storage/, bootstrap/cache/
```

---

## Setup Document Root

### Scenario 1: Document Root = public_html/

**Jika Anda bisa mengatur document root menjadi folder public:**

#### Via cPanel (cPanel Addon Domains / Parked Domains)
```
1. Login cPanel
2. Go to: Addon Domains / Parked Domains
3. For "Document Root", set to: public_html/public
4. Save
```

#### Via .htaccess (Alternatif)
```apache
# File: public_html/.htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### Scenario 2: Document Root Terbatas (Default public_html/)

**Jika hanya bisa akses public_html/, gunakan redirect:**

#### Step 1: Buat Struktur
```
public_html/
├── index.php              ← File ini yang di-akses
├── .htaccess
└── air-bersih/            ← Folder project
    ├── public/
    ├── app/
    ├── config/
    ├── .env
    └── ... (rest of files)
```

#### Step 2: Buat index.php
```php
<?php
// File: public_html/index.php
// Redirect ke Laravel public folder

require __DIR__.'/air-bersih/public/index.php';
```

#### Step 3: Setup .htaccess
```apache
# File: public_html/.htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/air-bersih/
    RewriteRule ^(.*)$ air-bersih/public/$1 [L]
</IfModule>
```

### Scenario 3: Separate Public & Private Folder

**Struktur terbaik untuk security:**

```
public_html/               ← Document root (Apache public)
├── index.php
├── uploads/
├── css/
├── js/
└── images/

/home/username/private/    ← Di luar public_html
└── air-bersih/
    ├── app/
    ├── config/
    ├── storage/
    ├── vendor/
    └── .env
```

#### index.php Content:
```php
<?php
require '/home/username/private/air-bersih/public/index.php';
```

---

## Persiapan Cek Sebelum Go-Live

### Security Checklist
- [ ] APP_DEBUG=false di .env
- [ ] APP_KEY sudah di-generate
- [ ] .env file tidak accessible dari browser
- [ ] vendor/ folder tidak accessible dari browser
- [ ] storage/ folder writable (775)
- [ ] bootstrap/cache/ folder writable (775)
- [ ] public/uploads/ folder created & writable
- [ ] HTTPS sudah diaktifkan (SSL Certificate)
- [ ] File .git tidak publicly accessible

### Performance Checklist
- [ ] php artisan config:cache sudah dijalankan
- [ ] php artisan route:cache sudah dijalankan
- [ ] npm run build sudah dijalankan
- [ ] composer dump-autoload --optimize sudah dijalankan
- [ ] Database indexes sudah dibuat
- [ ] Session driver = file (untuk shared hosting)
- [ ] Cache driver = file (untuk shared hosting)

### Database Checklist
- [ ] Database sudah dibuat di cPanel MySQL Databases
- [ ] User database sudah dibuat & dipilih
- [ ] User hanya memiliki privileges untuk database tsb
- [ ] Backup database sudah dijadwalkan

### Deployment Checklist
- [ ] Migrations sudah dijalankan: php artisan migrate
- [ ] Seeders sudah dijalankan: php artisan db:seed
- [ ] Storage symlink sudah dibuat: php artisan storage:link
- [ ] .env file sudah dibuat dengan nilai production
- [ ] Test login & navigasi semua halaman

---

## Troubleshooting

### 1. Error: "Missing .env file"
```bash
# Solution
cp .env.example .env
php artisan key:generate
```

### 2. Error: "No application encryption key has been specified"
```bash
# Solution
php artisan key:generate
```

### 3. Error: "Permission denied" untuk storage/
```bash
# Via SSH
chmod -R 777 storage bootstrap/cache

# Via FTP (jika tidak ada SSH)
# Set folder permissions ke 777 di FTP client
```

### 4. Error: "CORS or 404 errors setelah upload"
```
# Check .htaccess di public folder
# Pastikan sudah file public/.htaccess

# Jika masih error, test via SSH:
php artisan serve
# Jika berfungsi, berarti masalah .htaccess rewriting
```

### 5. Database Connection Error
```bash
# Cek konfigurasi .env
DB_HOST=localhost
DB_DATABASE=cpanel_username_dbname
DB_USERNAME=cpanel_username_dbuser
DB_PASSWORD=correct_password

# Test connection
php artisan tinker
DB::connection()->getPdo();
```

### 6. Folder public_html masih kosong setelah extract
```
# Extract mungkin membuat subfolder
# Coba:
1. Hapus semua file/folder di public_html
2. Extract lagi, pilih opsi "extract here"
3. Atau manual move file ke root public_html
```

### 7. 500 Error Internal Server
```bash
# Check error logs
# Via SSH
tail -f storage/logs/laravel.log

# Via cPanel
1. Error Logs → scroll untuk error terbaru
2. Copy error message
3. Debug sesuai error message
```

---

## Workflow Deployment Final

### 1. Local Development
```bash
npm run build
php artisan config:clear cache:clear view:clear
```

### 2. Upload ke Server
```bash
# Via cPanel File Manager
1. Create air-bersih.zip (exclude vendor, node_modules)
2. Upload & extract di public_html
```

### 3. Server Setup (SSH Terminal)
```bash
cd public_html

# Install dependencies
composer install --no-dev --optimize-autoloader

# Setup .env
cp .env.example .env
# Edit .env dengan production config

# Generate key
php artisan key:generate

# Cache configuration
php artisan config:cache
php artisan route:cache

# Set permissions
chmod -R 775 storage bootstrap/cache
chmod -R 755 public/uploads

# Setup database
php artisan migrate --force
php artisan db:seed
```

### 4. Verify
```bash
# Test di browser
https://yourdomain.com

# Login test
- Email: root@airbersih.com
- Password: (sesuai di seeder)
```

---

## Tips & Best Practices

### ✅ DO
- [x] Gunakan HTTPS (SSL Certificate)
- [x] Backup database secara regular
- [x] Monitor storage space
- [x] Update Laravel & dependencies secara berkala
- [x] Gunakan .env untuk production secrets
- [x] Setup error monitoring (Sentry, Rollbar)
- [x] Schedule backups automated

### ❌ DON'T
- [ ] Jangan commit .env ke repository
- [ ] Jangan set APP_DEBUG=true di production
- [ ] Jangan upload vendor folder (install di server)
- [ ] Jangan set permissions 777 (gunakan 775)
- [ ] Jangan share database password
- [ ] Jangan meninggalkan .git accessible
- [ ] Jangan skip migrations di production

---

## Next Steps

1. **Setelah Deploy:**
   - Test semua functionality di production
   - Monitor logs untuk errors
   - Setup monitoring & alerts

2. **Maintenance:**
   - Schedule regular backups
   - Update packages secara berkala
   - Review security logs

3. **Scaling (Future):**
   - Upgrade ke dedicated server jika traffic naik
   - Setup Redis caching
   - Setup CDN untuk static assets

---

**Status**: ✅ Ready for Production Deployment
