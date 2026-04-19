# 🌊 Air Bersih - Sistem Manajemen Air Minum

## Deskripsi Proyek

**Air Bersih** adalah sistem terintegrasi untuk mengelola pelayanan air minum dengan fitur lengkap meliputi:
- Manajemen pelanggan
- Pencatatan meter air
- Pembuatan tagihan otomatis
- Pencatatan pembayaran
- Dashboard analitik
- Audit trail komprehensif

Sistem ini dirancang khusus untuk PDAM atau operator layanan air minum skala menengah.

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- MySQL 5.7+
- Composer
- Node.js (untuk asset compilation)
- XAMPP atau local server

### Installation

#### 1. Clone/Setup Project
```bash
cd c:\xampp\htdocs
# Buat folder air-bersih jika belum ada
mkdir air-bersih
cd air-bersih
```

#### 2. Install Dependencies
```bash
composer install
npm install
```

#### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

#### 4. Database Configuration
Edit `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=air_bersih
DB_USERNAME=root
DB_PASSWORD=
```

#### 5. Database Migration & Seeding
```bash
php artisan migrate:fresh --seed
```

#### 6. Build Assets
```bash
npm run build
# atau untuk development dengan watch:
npm run dev
```

#### 7. Start Development Server
```bash
php artisan serve
# atau akses via XAMPP Apache:
# http://localhost/air-bersih
```

---

## 📊 MVP Features (Fitur Wajib)

✅ **Autentikasi & Manajemen User**
- Login/Register dengan role-based access
- 6 jenis role tersedia
- Admin user management

✅ **Master Wilayah**
- Kecamatan (Read-only, setup sekali saja)
- Desa (Full CRUD management)

✅ **Manajemen Pelanggan**
- CRUD customer lengkap
- Tracking lokasi (Kecamatan, Desa, Koordinat)
- Assign petugas lapangan

✅ **Pencatatan Meter Air**
- Dua field: Meter Bulan Lalu & Meter Bulan Ini
- Validasi anomali (tidak boleh turun)
- Auto-calculation konsumsi

✅ **Pembuatan Tagihan**
- Auto-generate dari meter records
- Hitung hanya jika konsumsi > 0
- Amount = Konsumsi × 1,000
- Status workflow: draft → terbit → lunas/menunggak

✅ **Pencatatan Pembayaran**
- Record pembayaran untuk tagihan
- Auto-update status tagihan
- Bukti pembayaran (receipt) dengan print

✅ **Dashboard & Monitoring**
- 5 KPI utama (Pelanggan, Tagihan, Pembayaran, Tunggakan, Gangguan)
- Quick access menu
- Real-time statistics

✅ **Activity Logging**
- Audit trail lengkap untuk semua operasi
- Track user dan timestamp

---

## 👥 Test Accounts

| Email | Password | Role |
|-------|----------|------|
| root@airbersih.com | Admin1234! | Root Admin |
| kecamatan@airbersih.com | Admin1234! | Admin Kecamatan |
| desa@airbersih.com | Admin1234! | Admin Desa |
| petugas@airbersih.com | Petugas123! | Petugas Lapangan |

---

## 📁 Project Structure

```
air-bersih/
├── app/
│   ├── Http/Controllers/          # Controller untuk setiap fitur
│   └── Models/                    # Eloquent models
├── database/
│   ├── migrations/                # Database schema
│   └── seeders/                   # Test data
├── resources/
│   ├── views/                     # Blade templates
│   ├── css/                       # Styling
│   └── js/                        # JavaScript
├── routes/
│   └── web.php                    # URL routing
├── storage/                       # File storage & logs
├── bootstrap/                     # Laravel bootstrap
├── public/                        # Public files (index.php)
├── config/                        # Configuration files
├── .env                           # Environment variables
├── composer.json                  # PHP dependencies
├── package.json                   # Node dependencies
├── vite.config.js                 # Asset bundler config
├── MVP_FEATURES.md               # Feature documentation
├── TESTING_GUIDE.md              # Testing procedures
└── TECHNICAL_DOCS.md             # Technical details
```

---

## 🔄 Business Flow (End-to-End)

### Fase 1: Setup Awal
1. Login sebagai root admin
2. Setup Kecamatan (hanya sekali, no CRUD setelah)
3. Buat Desa under Kecamatan
4. Registrasi pelanggan dengan assign petugas

### Fase 2: Bulanan - Pencatatan Meter
1. Petugas turun lapangan
2. Catat meter: "Meter Bulan Lalu" dan "Meter Bulan Ini"
3. Sistem auto-hitung: Konsumsi = Bulan Ini - Bulan Lalu
4. Input validated (tidak boleh mundur)

### Fase 3: Bulanan - Billing
1. Admin klik "Generate Tagihan"
2. Sistem auto-create invoice dari meter records
3. Amount = Konsumsi × 1,000
4. Status default: draft
5. Admin publish (draft → terbit)

### Fase 4: Pembayaran
1. Petugas/Admin record pembayaran
2. Sistem auto-update tagihan status:
   - Full payment → status lunas (hijau)
   - Overdue → status menunggak (merah)
   - Partial → status terbit (biru)
3. Print receipt untuk customer

### Fase 5: Monitoring
1. Lihat dashboard untuk KPI
2. Check activity logs untuk audit trail

---

## 🛠️ Development Guide

### Adding New Feature

1. **Create Migration**
   ```bash
   php artisan make:migration create_feature_table
   # Edit database/migrations/
   php artisan migrate
   ```

2. **Create Model**
   ```bash
   php artisan make:model FeatureName
   # Edit app/Models/FeatureName.php
   ```

3. **Create Controller**
   ```bash
   php artisan make:controller FeatureController
   # Edit app/Http/Controllers/FeatureController.php
   ```

4. **Create Views**
   ```
   resources/views/feature/
   ├── index.blade.php
   ├── create.blade.php
   └── edit.blade.php
   ```

5. **Add Routes** (routes/web.php)
   ```php
   Route::resource('feature', FeatureController::class);
   ```

---

## 📋 API Routes

### Authentication
- `GET /login` - Login form
- `POST /login` - Process login
- `GET /register` - Register form
- `POST /register` - Process register
- `POST /logout` - Logout

### Master Data
- `GET /kecamatan` - List kecamatan (read-only)
- `GET /desa` - List desa
- `POST /desa` - Create desa
- `PATCH /desa/{id}` - Update desa
- `DELETE /desa/{id}` - Delete desa

### Customers
- `GET /pelanggan` - List pelanggan
- `POST /pelanggan` - Create pelanggan
- `PATCH /pelanggan/{id}` - Update pelanggan
- `DELETE /pelanggan/{id}` - Delete pelanggan

### Meter
- `GET /meter-records` - List meter records
- `POST /meter-records` - Record meter

### Invoicing
- `GET /tagihan` - List invoices
- `POST /tagihan/generate` - Generate invoices
- `POST /tagihan/{id}/publish` - Publish invoice

### Payments
- `GET /pembayaran` - List payments
- `POST /pembayaran` - Record payment
- `GET /pembayaran/{id}/receipt` - View receipt

### Dashboard
- `GET /dashboard` - Dashboard dengan KPI

---

## 🔐 Security

- ✅ CSRF Protection (Built-in Laravel)
- ✅ Password Hashing (Bcrypt)
- ✅ SQL Injection Prevention (Eloquent ORM)
- ✅ Activity Logging (Audit trail)
- ✅ Foreign Key Constraints (Data integrity)

### To-Do Security Enhancements:
- [ ] Add role-based middleware protection
- [ ] Implement API rate limiting
- [ ] Add two-factor authentication
- [ ] Implement backup system
- [ ] Add data encryption

---

## 📊 Database Schema Overview

### Core Tables
- `users` - User profiles & authentication
- `roles` - Role definitions
- `activity_logs` - Audit trail

### Master Data
- `kecamatans` - Sub-districts
- `desas` - Villages

### Business Data
- `pelanggans` - Customers
- `meter_records` - Monthly readings (dual-field: previous/current)
- `tagihans` - Invoices
- `pembayarans` - Payments

**Total Records**: 13 migrations applied successfully

---

## 🐛 Troubleshooting

### Database Connection Error
```
Error: SQLSTATE[HY000]: General error...
```
**Solution**: Check `.env` database config and ensure MySQL is running
```bash
# Check connection
php artisan tinker
```

### Migration Fails
```bash
# Rollback and retry
php artisan migrate:rollback
php artisan migrate
```

### Permission Denied (storage/)
```bash
# Fix permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache

# Windows: Set folder writable via Properties
```

### Asset Compilation Issues
```bash
# Clear cache and rebuild
rm -rf node_modules package-lock.json
npm install
npm run build
```

---

## 📚 Documentation

- **MVP_FEATURES.md** - Complete feature documentation
- **TESTING_GUIDE.md** - Step-by-step testing procedures
- **TECHNICAL_DOCS.md** - Architecture & technical details
- **README.md** - This file (Quick start guide)

---

## 📈 Performance Tips

1. **Enable Query Caching**
   ```php
   // Use eager loading
   $records = Model::with('relationship')->get();
   ```

2. **Database Indexing**
   ```sql
   ALTER TABLE meter_records ADD INDEX (pelanggan_id);
   ALTER TABLE meter_records ADD INDEX (recorded_at);
   ```

3. **Pagination for Large Datasets**
   ```php
   $records = Model::paginate(25);
   ```

4. **Cache Dashboard Stats**
   ```php
   $stats = Cache::remember('stats', 3600, fn() => [...]);
   ```

---

## 🚀 Deployment (Production)

### Server Requirements
- PHP 8.2+ with extensions: PDO, Bcrypt, OpenSSL
- MySQL 5.7+ or MariaDB
- Composer
- Git (for version control)

### Deployment Steps
```bash
# 1. Clone repository
git clone <repo-url> /var/www/air-bersih

# 2. Install dependencies
cd /var/www/air-bersih
composer install --no-dev
npm install && npm run build

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Database
php artisan migrate --force
php artisan db:seed --force

# 5. Permissions
chown -R www-data:www-data /var/www/air-bersih
chmod -R 755 /var/www/air-bersih
chmod -R 777 /var/www/air-bersih/storage

# 6. Web server (nginx/apache)
# Configure document root: /var/www/air-bersih/public
```

---

## 📞 Support & Maintenance

### Regular Maintenance
- [ ] Backup database weekly
- [ ] Check activity logs for issues
- [ ] Monitor storage space
- [ ] Update Laravel & dependencies quarterly

### Common Issues & Solutions
See **TESTING_GUIDE.md** for troubleshooting section

### Future Enhancements
- Role-based access control middleware
- SMS/Email notifications
- Payment gateway integration
- Mobile app
- Advanced reporting
- Multi-tenant support

---

## 📄 License & Terms

**Air Bersih** © 2026  
Status: MVP - Production Ready  
Last Updated: April 2026

---

## 🎯 Quick Reference

**Start Development Server**
```bash
cd c:\xampp\htdocs\air-bersih
php artisan serve
# Access at http://localhost:8000
```

**Fresh Database Reset**
```bash
php artisan migrate:fresh --seed
```

**Build Assets**
```bash
npm run build      # Production
npm run dev        # Development with watch
```

**Clear Cache**
```bash
php artisan cache:clear
php artisan config:cache
```

---

## 📞 Need Help?

1. Check **MVP_FEATURES.md** for feature details
2. Read **TESTING_GUIDE.md** for test procedures
3. Review **TECHNICAL_DOCS.md** for architecture
4. Check Laravel documentation: https://laravel.com/docs

---

**Happy Coding! 🚀**
