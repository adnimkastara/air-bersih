# Catatan Perubahan

## 2026-05-02 - Refactor UI Modern Dashboard

- Merapikan layout admin Blade dengan gaya Minimalist SaaS: background `#F8FAFC`, surface putih, soft shadow, radius 16px, dan spacing yang lebih lega.
- Memperbarui sidebar menjadi Deep Navy/Charcoal dengan active state yang lebih jelas, ikon konsisten, dan pengelompokan menu `Main Menu` serta `Pengaturan`.
- Menstandarkan komponen global untuk card, tombol, form input, focus ring, badge status, toolbar, dan tabel.
- Mengubah tabel agar hanya memakai garis horizontal tipis, header abu muda, row height lebih lapang, dan hover state lembut.
- Mengubah tombol aksi utama di tabel seperti Edit/Hapus menjadi tombol ikon soft-tint.
- Membatasi form ganti password ke lebar nyaman sekitar 500px.
- Membangun ulang dashboard agar konsisten dengan layout admin: kartu statistik dengan ikon lingkaran berwarna, grafik bar gradient, panel monitoring, ring progress, dan peta preview modern.
- Tidak ada perubahan pada controller, route, model, migration, atau logika bisnis backend.

## Verifikasi

- `php -l` berhasil untuk Blade utama yang diubah.
- `git diff --check` bersih.
- `php artisan view:cache` belum bisa dijalankan karena folder `vendor/` belum tersedia di workspace ini (`vendor/autoload.php` tidak ditemukan).
