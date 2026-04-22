# Emergency Runbook: API HTTP 503 (Laravel)

Dokumen ini untuk penanganan darurat saat endpoint API (misalnya `/api/v1/login`) mengembalikan **HTTP 503 Service Unavailable**.

## 1) Gejala penting

- Endpoint API mengembalikan halaman HTML `Service Unavailable`.
- Tidak ada response JSON standar dari aplikasi.
- Di lingkungan ini, akar penyebab yang terverifikasi adalah:
  - `vendor/autoload.php` tidak ditemukan, sehingga Laravel gagal bootstrap sangat awal.

## 2) Verifikasi cepat akar masalah

Jalankan dari root project:

```bash
php artisan --version
```

Jika muncul error seperti `Failed opening required '.../vendor/autoload.php'`, artinya dependency Composer tidak terpasang/korup.

Cek langsung file autoload:

```bash
test -f vendor/autoload.php && echo "autoload OK" || echo "autoload MISSING"
```

## 3) Pemulihan utama

```bash
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> Jika hosting memblokir koneksi GitHub/Packagist, jalankan Composer install dari environment build yang punya akses internet, lalu deploy artifact hasil build.

## 4) Verifikasi setelah pemulihan

```bash
curl -i -X POST 'https://airbersih.pelayanan.id/api/v1/login' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"email":"invalid@example.com","password":"salah"}'
```

Ekspektasi minimal:
- Bukan lagi HTTP 503.
- Response berformat JSON.
- Untuk kredensial salah, status 422 (sesuai implementasi saat ini).

## 5) Checklist server eksternal (jika masih 503)

1. **PHP-FPM / LiteSpeed / Apache error log**
   - Cari fatal error saat load `public/index.php`.
2. **File `.env` production**
   - Pastikan ada, `APP_KEY` valid, `APP_ENV=production`, `APP_DEBUG=false`.
3. **Permission**
   - `storage` dan `bootstrap/cache` writable user web server.
4. **Maintenance mode**
   - Cek apakah `storage/framework/maintenance.php` ada.
5. **OPcache stale**
   - Reload PHP-FPM / restart web service agar file terbaru terbaca.
6. **Ketersediaan layanan dependency**
   - DB, Redis/Memcached, queue backend (jika dipakai) harus reachable.

## 6) Catatan hardening di kode

`public/index.php` sudah ditambahkan guard agar:

- Saat `vendor/autoload.php` hilang, API route merespons JSON 503 (bukan halaman HTML default).
- Saat bootstrap exception sebelum kernel siap, API route tetap merespons JSON 503.
- Error detail dicatat ke server error log tanpa membocorkan credential sensitif.
