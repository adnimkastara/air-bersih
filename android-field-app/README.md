# Air Bersih Petugas (Android)

Aplikasi Android Kotlin + Jetpack Compose untuk petugas lapangan, sebagai client mobile untuk backend Laravel.

## Arsitektur singkat
- `model/`: request/response API.
- `network/`: Retrofit + OkHttp + auth header bearer token.
- `data/`: DataStore token.
- `repository/`: orkestrasi API + error handling.
- `viewmodel/`: state aplikasi + aksi UI.
- `ui/screens/`: layar Login, Dashboard, Pelanggan, Meter, Tagihan, Pembayaran, Keluhan, Monitoring Map.
- `utils/`: helper GPS (`FusedLocationProviderClient`).

## Endpoint Laravel yang dipakai
- `POST /api/v1/login`
- `POST /api/v1/logout`
- `GET /api/v1/me`
- `GET /api/v1/pelanggan`
- `GET /api/v1/pelanggan/{id}`
- `POST /api/v1/meter-records`
- `GET /api/v1/tagihan`
- `POST /api/v1/pembayaran`
- `GET /api/v1/keluhan`
- `POST /api/v1/keluhan`
- `GET /api/v1/dashboard-ringkas`
- `GET /api/v1/monitoring/peta`

## Ganti Base URL
Atur di `app/build.gradle.kts`:
- `BASE_URL_DEV`
- `BASE_URL_PROD`
- `USE_PROD`

## Jalankan
1. Buka folder `android-field-app` di Android Studio (Hedgehog+).
2. Sync Gradle.
3. Isi Google Maps API key pada placeholder manifest.
4. Jalankan di emulator / device Android.

## Install ke HP
1. Aktifkan Developer options (tap Build Number 7x).
2. Aktifkan USB debugging.
3. Sambungkan HP via USB.
4. Pilih device di Android Studio, klik Run.

## Build APK
- Debug APK: `Build > Build Bundle(s) / APK(s) > Build APK(s)`.
- Release APK: set keystore dulu, lalu `Build > Generate Signed Bundle / APK`.

## Catatan stabilitas v1
- Tidak memindahkan logika billing utama dari Laravel.
- GPS tidak memblokir total: jika tidak tersedia, kirim `null` dan gunakan fallback center map.
- Offline full belum diimplementasi; pengembangan lanjutan: cache Room + retry queue.
