# 🚀 Air Bersih - Quick Reference Guide

**Status**: ✅ Production Ready MVP  
**Version**: 1.0  
**Last Updated**: April 19, 2026

---

## 🔥 Quick Start (5 Minutes)

### 1. Start the Application
```bash
cd c:\xampp\htdocs\air-bersih
php artisan serve
# Access: http://localhost:8000
```

### 2. Login (Choose One)
| Email | Password | Role |
|-------|----------|------|
| petugas@airbersih.com | Petugas123! | Field Officer |
| root@airbersih.com | Admin1234! | Admin |

### 3. First Action
- **If Petugas**: Go to "Pencatatan Meter" → Record a meter reading
- **If Admin**: Go to "Tagihan" → Generate invoices from meter records

---

## 📋 Key URLs

| Feature | URL | Access |
|---------|-----|--------|
| Dashboard | /dashboard | After login |
| Kecamatan | /kecamatan | Read-only |
| Desa | /desa | Full CRUD |
| Pelanggan | /pelanggan | Full CRUD |
| Meter | /meter-records | Record only |
| Tagihan | /tagihan | Generate & publish |
| Pembayaran | /pembayaran | Record & receipt |
| Admin | /admin | Admin only |

---

## 📊 Database Info

**Connection**: localhost:3306  
**Database**: air_bersih  
**User**: root  
**Password**: (empty)

### Key Tables
- `users` - 4 test users (see test accounts)
- `roles` - 6 role types
- `pelanggans` - 1 test customer
- `meter_records` - 1 test record (100→150)
- `tagihans` - Generated invoices
- `pembayarans` - Payment records

---

## 🧪 End-to-End Test Flow

```
1. Login as petugas@airbersih.com
   ↓
2. Pencatatan Meter:
   Meter Bulan Lalu: 150
   Meter Bulan Ini: 200
   (Konsumsi: 50)
   ↓
3. Logout & Login as root@airbersih.com
   ↓
4. Tagihan → Generate
   (Auto-creates: 50 × 1000 = 50,000)
   ↓
5. Tagihan → Publish (draft → terbit)
   ↓
6. Pembayaran → Record Payment (50,000)
   ↓
7. Check Tagihan status: lunas ✅
   ↓
8. Dashboard: All KPIs updated ✅
```

---

## 🛠️ Common Commands

### Database
```bash
# Fresh reset with test data
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Show migration status
php artisan migrate:status
```

### Cache
```bash
# Clear all cache
php artisan cache:clear

# Cache config
php artisan config:cache

# Clear cache & route
php artisan cache:clear && php artisan route:cache
```

### Assets
```bash
# Production build
npm run build

# Development watch
npm run dev
```

### Tinker (PHP Shell)
```bash
php artisan tinker

# List all roles
>>> Role::all()

# Get user with role
>>> User::with('role')->first()

# Count records
>>> Pelanggan::count()
```

---

## 📁 Important Files

### Core Application
- `routes/web.php` - All URL routes
- `app/Http/Controllers/` - Business logic
- `app/Models/` - Data models
- `database/migrations/` - Schema
- `database/seeders/` - Test data

### Configuration
- `.env` - Database & app config
- `config/app.php` - App settings
- `config/database.php` - DB settings

### Views & Assets
- `resources/views/` - HTML templates
- `resources/css/app.css` - Styling
- `resources/js/app.js` - JavaScript
- `public/` - Static files

---

## 🔍 Debugging Tips

### Enable Debug Mode
Edit `.env`:
```
APP_DEBUG=true
```

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

### Database Query Log
```bash
# In controller:
DB::enableQueryLog();
// ... operations ...
dd(DB::getQueryLog());
```

### Activity Log Tracking
```sql
SELECT * FROM activity_logs 
ORDER BY created_at DESC 
LIMIT 20;
```

---

## ✅ Feature Checklist

### Master Data ✅
- [x] Kecamatan (read-only) - 1 record: "Kecamatan Utama"
- [x] Desa (full CRUD) - 1 record: "Desa Satu"

### Pelanggan ✅
- [x] Create, Read, Update, Delete
- [x] Status (aktif/nonaktif)
- [x] Petugas assignment

### Meter ✅
- [x] Dual fields (previous_month, current_month)
- [x] Consumption auto-calculation
- [x] Anomaly validation (current ≥ previous)

### Tagihan ✅
- [x] Auto-generate from meter
- [x] Consumption-based amount
- [x] Status workflow (draft→terbit→lunas/menunggak)
- [x] Period tracking (YYYY-MM)

### Pembayaran ✅
- [x] Record payment
- [x] Auto-update invoice status
- [x] Print receipt

### Dashboard ✅
- [x] Total Pelanggan
- [x] Total Tagihan
- [x] Total Pembayaran
- [x] Total Tunggakan
- [x] Jumlah Gangguan

---

## 🐛 Common Issues & Solutions

### "SQLSTATE[HY000]: General error: 1364"
**Issue**: Field doesn't have default value  
**Solution**: Check `.env` database config and ensure migration ran  
```bash
php artisan migrate:refresh --seed
```

### "Class not found" Error
**Issue**: Missing use statement or autoload issue  
**Solution**: Run composer autoload
```bash
composer dump-autoload
```

### 404 Not Found on Route
**Issue**: Route not registered  
**Solution**: Check routes/web.php and cache
```bash
php artisan route:cache
php artisan cache:clear
```

### Permission Denied (storage/)
**Issue**: Write permission on storage folder  
**Solution**: Set permissions
```bash
chmod -R 755 storage bootstrap/cache
```

---

## 📞 Need Help?

### Documentation Files
1. **MVP_FEATURES.md** - Feature details & business rules
2. **TESTING_GUIDE.md** - Complete test procedures
3. **TECHNICAL_DOCS.md** - Architecture & code details
4. **README_SETUP.md** - Installation & setup
5. **PROJECT_SUMMARY.md** - Completion status

### Quick Links
- Laravel Docs: https://laravel.com/docs
- Blade Templates: https://laravel.com/docs/blade
- Eloquent ORM: https://laravel.com/docs/eloquent
- MySQL Docs: https://dev.mysql.com/doc

---

## 🚀 What's Next?

### Phase 2 Features (Not in MVP)
- [ ] SMS/Email notifications
- [ ] Payment gateway integration
- [ ] Advanced reporting
- [ ] Mobile app
- [ ] API endpoints

### Current Limitations
- No role-based route protection (can add middleware)
- No pagination (add for large datasets)
- File-based sessions (can move to database)
- No image storage (can add for bill/receipt images)

---

## 📊 Stats at a Glance

| Component | Count | Status |
|-----------|-------|--------|
| Database Tables | 13 | ✅ |
| Controllers | 9 | ✅ |
| Models | 9 | ✅ |
| Routes | 37 | ✅ |
| Views | 12 | ✅ |
| Roles | 6 | ✅ |
| Test Users | 4 | ✅ |
| Test Data | 1 full flow | ✅ |
| Documentation | 5 files | ✅ |

---

## 🔐 Default Credentials (Development Only)

```
Root Admin:
  Email: root@airbersih.com
  Password: Admin1234!

Test Accounts:
  kecamatan@airbersih.com / Admin1234!
  desa@airbersih.com / Admin1234!
  petugas@airbersih.com / Petugas123!
```

⚠️ **IMPORTANT**: Change passwords in production!

---

## 💾 Backup & Restore

### Backup Database
```bash
mysqldump -u root air_bersih > backup.sql
```

### Restore Database
```bash
mysql -u root air_bersih < backup.sql
```

### Full Project Backup
```bash
# Zip entire project
tar -czf air-bersih-backup.tar.gz /path/to/air-bersih
```

---

## 🎯 Success Indicators

When system is working correctly:
- ✅ Login succeeds with test account
- ✅ Dashboard loads with 5 KPI cards
- ✅ Can create meter record with two fields
- ✅ Can generate invoice (50 × 1000 = 50,000)
- ✅ Can record payment and status updates
- ✅ Activity log shows all operations
- ✅ No JavaScript console errors
- ✅ All forms validate properly

---

## 🔧 Development Environment Setup

### Fresh Start
```bash
# Navigate to project
cd c:\xampp\htdocs\air-bersih

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate:fresh --seed

# Build assets
npm run build

# Start development
php artisan serve
```

### Access Points
- **Web**: http://localhost:8000
- **Via XAMPP**: http://localhost/air-bersih
- **Admin**: /dashboard (after login)
- **Database**: localhost:3306/air_bersih (root/no-password)

---

## 📈 Production Deployment

### Pre-Deployment
```bash
# Run all tests
php artisan test

# Clear cache
php artisan cache:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build assets
npm run build
```

### Deployment Commands
```bash
# On production server
composer install --no-dev
php artisan migrate --force
php artisan db:seed --force
chmod -R 755 storage bootstrap/cache
```

---

## 🎓 Learning Resources

### Key Concepts
1. **Eloquent ORM** - Database query builder
2. **Migrations** - Database versioning
3. **Middleware** - Request/response filters
4. **Blade Templates** - View templating engine
5. **Routes** - URL mapping to controllers

### File You'll Edit Most
- `routes/web.php` - Add new routes
- `app/Http/Controllers/` - Business logic
- `resources/views/` - HTML templates
- `.env` - Configuration

---

## ✨ Tips & Tricks

### Speed Up Development
```bash
# Watch for file changes and rebuild
npm run dev

# Run server with debug
php artisan serve --debug

# Tail logs in real-time
tail -f storage/logs/laravel.log
```

### Database Queries
```php
// Find user with role
$user = User::with('role')->find(1);

// Get users by role name
$admins = User::whereHas('role', function($q) {
    $q->where('name', 'admin');
})->get();

// Count by status
$active = Pelanggan::where('status', 'aktif')->count();
```

### Debugging
```php
// Log to file
\Log::info('Debug message', ['key' => $value]);

// Dump and die
dd($variable);

// Pretty dump
dump($variable);
```

---

## 🎉 You're All Set!

Air Bersih MVP is ready to use. Start with:
1. Login to dashboard
2. Review existing data
3. Follow TESTING_GUIDE.md for complete test
4. Check MVP_FEATURES.md for detailed feature info

**Happy coding!** 🚀

---

**Last Updated**: April 19, 2026  
**Version**: MVP 1.0  
**Maintained By**: Development Team
