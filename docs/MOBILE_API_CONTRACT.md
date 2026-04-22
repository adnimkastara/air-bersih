# Mobile API Contract (Android)

Dokumen ini menetapkan kontrak endpoint API Laravel untuk aplikasi Android petugas lapangan.

## Base URL
- `/api/v1`

## Header wajib
- `Authorization: Bearer <token>`
- `Accept: application/json`
- `Content-Type: application/json` (untuk request body JSON)

## Standar response JSON

### Sukses
```json
{
  "success": true,
  "message": "Pesan sukses",
  "data": {}
}
```

### Validasi gagal
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field": ["Pesan error"]
  }
}
```

### Tidak ditemukan
```json
{
  "success": false,
  "message": "Data tidak ditemukan"
}
```

---

## 1) Pelanggan

### GET `/pelanggan`
Query opsional:
- `q` (search: nama/kode/no meter/no hp)
- `per_page` (default 20)

### GET `/pelanggan/{id}`
Ambil detail pelanggan.

### POST `/pelanggan`
Field wajib:
- `name`
- `address`
- `dusun`
- `jenis_pelanggan`
- `nomor_meter`
- `status` (`aktif`/`nonaktif`)

Field opsional:
- `email`, `phone`
- `kecamatan_id`, `desa_id` (untuk user kecamatan, `desa_id` wajib)
- `assigned_petugas_id`
- `latitude`, `longitude`

Contoh request:
```json
{
  "name": "Budi",
  "address": "Jl. Melati 01",
  "dusun": "Dusun I",
  "jenis_pelanggan": "rumah_tangga",
  "nomor_meter": "MTR-7788",
  "status": "aktif",
  "phone": "08123456789"
}
```

### PUT/PATCH `/pelanggan/{id}`
Update pelanggan (field sama dengan create, bersifat parsial).

### DELETE `/pelanggan/{id}`
Hapus pelanggan jika belum punya dependensi transaksi.

---

## 2) Tagihan

### GET `/tagihan`
Query opsional:
- `pelanggan_id`
- `status` (`draft`/`terbit`/`menunggak`/`lunas`)
- `period` (`YYYY-MM`)
- `unpaid_only` (`true/false`)
- `per_page`

### GET `/tagihan/{id}`
Detail tagihan, termasuk:
- `tagihan.id`
- `tagihan.pelanggan`
- `tagihan.period`
- `tagihan.amount`
- `tagihan.status`
- `tagihan.due_date`
- `tagihan.meter_record`
- `total_paid`
- `remaining`

### GET `/tagihan-terbuka`
Alias untuk daftar tagihan belum lunas (`unpaid_only=true`).

### POST `/tagihan/generate`
Generate tagihan dari catat meter per periode.

Request:
```json
{
  "period": "2026-04"
}
```

---

## 3) Pembayaran

### GET `/payment-methods`
Ambil metadata metode pembayaran valid.

### GET `/pembayaran`
Query opsional:
- `tagihan_id`
- `payment_method`
- `per_page`

### GET `/pembayaran/{id}`
Detail pembayaran.

### POST `/pembayaran`
Field wajib:
- `tagihan_id`
- `payment_method`
- `amount`
- `paid_at`

Field opsional:
- `notes`

Contoh request:
```json
{
  "tagihan_id": 123,
  "payment_method": "cash",
  "amount": 75000,
  "paid_at": "2026-04-22",
  "notes": "Bayar di tempat"
}
```

> `payment_method` akan dinormalisasi ke nilai canonical backend.

### Canonical payment method
- `tunai`
- `transfer_bank`
- `e_wallet`

### Alias yang diterima backend
- Untuk `tunai`: `tunai`, `cash`, `CASH`, `Tunai`
- Untuk `transfer_bank`: `transfer_bank`, `transfer`, `bank_transfer`, `Transfer Bank`
- Untuk `e_wallet`: `e_wallet`, `ewallet`, `e-wallet`, `dompet_digital`, `E-Wallet`

