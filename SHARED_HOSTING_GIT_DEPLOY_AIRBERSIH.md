# Panduan Deploy Laravel via Git (Shared Hosting + SSH)

Target Anda:
- Domain: `airbersih.pelayanan.id`
- Folder web: `public_html/airbersih`
- Akses: SSH tersedia

Panduan ini fokus **aman untuk production** dan minim risiko.

---

## 0) Prasyarat awal

Pastikan di hosting:
1. PHP >= 8.2
2. Extension wajib aktif: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`
3. Composer tersedia (`composer -V`)
4. Git tersedia (`git --version`)
5. Database MySQL/MariaDB + user database sudah dibuat

---

## 1) Rekomendasi struktur folder (paling aman)

**Ideal** (core Laravel tidak langsung di web root):

- `~/repos/air-bersih` → source aplikasi (hasil git clone)
- `~/public_html/airbersih` → hanya berisi isi folder `public/`

Kenapa: `.env`, `app/`, `config/`, `storage/` tidak ikut terekspos publik.

---

## 2) Clone project dengan Git

Masuk SSH, lalu:

```bash
cd ~
mkdir -p repos
cd repos
git clone <URL_REPO_ANDA> air-bersih
cd air-bersih
git checkout <branch-production-anda>
```

Jika pakai private repo (GitHub/GitLab), pastikan SSH key hosting sudah didaftarkan.

---

## 3) Install dependency production

Di folder repo:

```bash
cd ~/repos/air-bersih
composer install --no-dev --optimize-autoloader
```

Opsional (jika build frontend diperlukan dan Node tersedia):

```bash
npm ci
npm run build
```

Kalau Node tidak tersedia di hosting, build aset di lokal/CI lalu push hasil build.

---

## 4) Setup `.env` production

Buat file env dari contoh:

```bash
cp .env.example .env
```

Edit `.env` minimal:

```env
APP_NAME="Air Bersih Desa"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://airbersih.pelayanan.id

LOG_CHANNEL=daily
LOG_LEVEL=warning
LOG_DAILY_DAYS=14

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_db
DB_USERNAME=user_db
DB_PASSWORD=password_db

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=airbersih.pelayanan.id

CACHE_STORE=database
QUEUE_CONNECTION=sync

FILESYSTEM_DISK=local
```

Generate app key:

```bash
php artisan key:generate --force
```

---

## 5) Inisialisasi database

Jika first deploy:

```bash
php artisan migrate --force
```

Jika Anda restore dump manual (phpMyAdmin), lakukan import dulu lalu jalankan migrate untuk delta terbaru.

---

## 6) Storage link + permission

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

Jika `chmod` dibatasi provider, set permission via File Manager cPanel.

---

## 7) Hubungkan web root `public_html/airbersih` ke folder `public`

### Opsi A (disarankan): symlink

Kosongkan isi folder web target, lalu:

```bash
rm -rf ~/public_html/airbersih
ln -s ~/repos/air-bersih/public ~/public_html/airbersih
```

### Opsi B: copy isi `public/` ke web root (fallback)

Jika symlink dilarang:

```bash
mkdir -p ~/public_html/airbersih
rsync -av --delete ~/repos/air-bersih/public/ ~/public_html/airbersih/
```

Lalu edit `index.php` pada `public_html/airbersih/index.php` agar path `vendor/autoload.php` dan `bootstrap/app.php` menunjuk ke folder repo (`~/repos/air-bersih`).

---

## 8) Optimasi cache production

Jalankan:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Saat ada perubahan konfigurasi/env, selalu ulangi `config:cache`.

---

## 9) Scheduler/Cron minimal

Tambahkan cron (cPanel Cron Jobs):

```cron
* * * * * /usr/bin/php /home/USERNAME/repos/air-bersih/artisan schedule:run >> /dev/null 2>&1
```

Ganti `USERNAME` sesuai akun hosting.

Jika queue worker permanen tidak tersedia, tetap aman dengan `QUEUE_CONNECTION=sync`.

---

## 10) Alur update deploy via Git (harian)

Setiap update rilis:

```bash
cd ~/repos/air-bersih
php artisan down --render="errors::503"
git fetch --all
git checkout <branch-production-anda>
git pull origin <branch-production-anda>
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

Jika pakai opsi B (copy public), sinkronkan ulang folder `public/` setelah pull.

---

## 11) Verifikasi setelah deploy

Checklist cepat:
1. `https://airbersih.pelayanan.id` bisa diakses
2. Login berhasil
3. Role berjalan sesuai scope (admin kecamatan/admin desa/petugas)
4. Upload file jalan
5. PDF laporan bisa di-generate
6. `APP_DEBUG=false` (error page tidak bocor stack trace)
7. File di `storage` tampil benar

---

## 12) Rollback cepat

Jika deploy gagal:

```bash
cd ~/repos/air-bersih
git log --oneline -n 5
git checkout <commit-sebelumnya-yang-stabil>
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Lalu uji akses ulang aplikasi.

---

## 13) Catatan keamanan penting

1. Jangan simpan `.env` di dalam `public_html/airbersih`.
2. Pastikan `APP_DEBUG=false` pada production.
3. Gunakan HTTPS penuh pada subdomain.
4. Backup database terjadwal (harian/mingguan).
5. Batasi user DB hanya ke 1 database aplikasi.

