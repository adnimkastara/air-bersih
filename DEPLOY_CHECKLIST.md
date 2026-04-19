# 📋 Checklist Deploy ke Shared Hosting

**Quick Reference Untuk Setup Production**

---

## Pre-Deployment Checklist

### 1. Local Machine
```bash
# ✅ Clear everything
php artisan config:clear cache:clear view:clear

# ✅ Build assets
npm install
npm run build

# ✅ Optimize autoloader
composer install --no-dev --optimize-autoloader

# ✅ Create compressed file
# EXCLUDE: vendor/, node_modules/, .git/, .env
```

### 2. .env Configuration (Local)
```env
APP_ENV=production
APP_KEY=base64:XXXXXXXXXXXXX=
APP_DEBUG=false

DB_HOST=localhost
DB_DATABASE=production_db
DB_USERNAME=db_user
DB_PASSWORD=strong_password
```

### 3. Server Files Ready
- [ ] .env.example di root
- [ ] public/.htaccess ready
- [ ] Root .htaccess ready (jika perlu)
- [ ] Migration files ready
- [ ] Seeder files ready

---

## Upload & Setup di Shared Hosting

### Step 1: Upload File (5-10 menit)
```
1. Compress project tanpa vendor/
2. Login cPanel
3. File Manager → public_html/
4. Upload air-bersih.zip
5. Extract
6. Delete air-bersih.zip
```

### Step 2: SSH Setup (10-15 menit)
```bash
# 1. Login SSH
ssh user@yourdomain.com

# 2. Navigate
cd public_html

# 3. Install dependencies
composer install --no-dev

# 4. Build assets
npm install && npm run build

# 5. Setup environment
cp .env.example .env
nano .env  # Edit dengan production config

# 6. Generate app key
php artisan key:generate

# 7. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Permissions
chmod -R 775 storage bootstrap/cache
chmod -R 755 public/uploads

# 9. Database
php artisan migrate --force
php artisan db:seed
```

### Step 3: Verify (5 menit)
```bash
# Test aplikasi di browser
https://yourdomain.com

# Test login
email: root@airbersih.com
password: (dari seeder)

# Check logs
tail -f storage/logs/laravel.log
```

---

## Document Root Configuration

### Type A: Full Control (Document Root = public_html/public)
```
cPanel → Addon Domains
Set Document Root: public_html/public
✅ Best security & performance
```

### Type B: Limited (Document Root = public_html/)
```
Option 1 - Root .htaccess redirect:
RewriteRule ^(.*)$ public/$1 [L]

Option 2 - Separate index.php:
Create public_html/index.php
Require: /path/to/air-bersih/public/index.php
```

### Type C: Outside public_html/
```
Structure:
/home/user/laravel/          ← Project root
/home/user/public_html/      ← Acces point
  └── index.php              ← Requires project root

✅ Best security
⚠️ May require special setup
```

---

## Post-Deployment

### First Login
- [ ] Email: root@airbersih.com
- [ ] Password: (check seeder)
- [ ] Test all menu items
- [ ] Test create/edit/delete

### Monitoring
- [ ] Storage space: Check regularly
- [ ] Backup: Setup automatic backup
- [ ] Logs: Monitor storage/logs/laravel.log
- [ ] Email: Test email sending (if applicable)

### Maintenance
- [ ] Weekly: Check error logs
- [ ] Monthly: Database backup
- [ ] Monthly: Update packages (composer, npm)
- [ ] Quarterly: Full security audit

---

## Common Issues & Fixes

| Issue | Solution |
|-------|----------|
| 500 Error | Check storage/logs/laravel.log |
| Database Connection Error | Verify .env credentials |
| Permission Denied (storage) | chmod -R 775 storage/ |
| 404 on every route | Check public/.htaccess |
| Missing assets (CSS/JS) | npm run build, php artisan cache:clear |
| Upload fails | Check public/uploads/ permissions |
| Mail not sending | Verify MAIL_* settings in .env |

---

## Quick Commands

```bash
# Clear everything
php artisan config:clear cache:clear view:clear route:cache

# Run migrations
php artisan migrate
php artisan migrate:rollback
php artisan migrate:refresh --seed

# Database
php artisan db:seed
php artisan db:seed --class=DatabaseSeeder

# Tinker (debug)
php artisan tinker
DB::select('SELECT COUNT(*) FROM users')

# Generate key
php artisan key:generate

# Check routes
php artisan route:list

# Logs
tail -f storage/logs/laravel.log
grep "error" storage/logs/laravel.log
```

---

## .env Template (Shared Hosting)

```env
# === APPLICATION ===
APP_NAME="Air Bersih"
APP_ENV=production
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX=
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=Asia/Jakarta

# === DATABASE ===
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpanel_username_dbname
DB_USERNAME=cpanel_username_dbuser
DB_PASSWORD=your_secure_password

# === CACHE & SESSION ===
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync

# === MAIL ===
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=app_specific_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Air Bersih"

# === UPLOAD ===
UPLOAD_PATH=public/uploads
MAX_UPLOAD_SIZE=5242880
```

---

## Success Indicators ✅

- [ ] Site loads without errors
- [ ] Dashboard shows correct data
- [ ] Can login with test accounts
- [ ] Can create/edit/delete records
- [ ] Meter records show correctly
- [ ] Invoices generate properly
- [ ] Receipts can be printed
- [ ] Activity logs recorded
- [ ] No 500 errors in logs
- [ ] HTTPS working properly

---

**Status**: Ready for Production
