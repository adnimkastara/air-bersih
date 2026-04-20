package com.airbersih.mobile.model

import com.squareup.moshi.Json

data class LoginRequest(
    val email: String,
    val password: String,
    @Json(name = "device_name") val deviceName: String = "android-petugas"
)

data class LoginResponse(
    val token: String,
    val user: User
)

data class User(
    val id: Long,
    val name: String,
    val email: String,
    val role: String,
    val desa: String?
)

data class DashboardSummary(
    @Json(name = "jumlah_pelanggan") val jumlahPelanggan: Int,
    @Json(name = "keluhan_aktif") val keluhanAktif: Int,
    @Json(name = "meter_hari_ini") val meterHariIni: Int
)

data class Pelanggan(
    val id: Long,
    @Json(name = "kode_pelanggan") val kodePelanggan: String,
    val nama: String,
    val alamat: String,
    val desa: String,
    val status: String,
    val latitude: Double?,
    val longitude: Double?
)

data class MeterRecordRequest(
    @Json(name = "pelanggan_id") val pelangganId: Long,
    @Json(name = "angka_meter") val angkaMeter: Int,
    @Json(name = "tanggal_catat") val tanggalCatat: String,
    val latitude: Double?,
    val longitude: Double?,
    @Json(name = "foto_base64") val fotoBase64: String? = null
)

data class Tagihan(
    val id: Long,
    @Json(name = "pelanggan_id") val pelangganId: Long,
    val periode: String,
    val nominal: Long,
    val status: String
)

data class PembayaranRequest(
    @Json(name = "tagihan_id") val tagihanId: Long,
    val nominal: Long,
    val metode: String,
    val tanggal: String,
    val catatan: String? = null,
    @Json(name = "bukti_base64") val buktiBase64: String? = null
)

data class Keluhan(
    val id: Long,
    val judul: String,
    val deskripsi: String,
    val kategori: String,
    val prioritas: String,
    val status: String,
    val latitude: Double?,
    val longitude: Double?
)

data class KeluhanRequest(
    val judul: String,
    val deskripsi: String,
    val kategori: String,
    val prioritas: String,
    val latitude: Double?,
    val longitude: Double?,
    @Json(name = "foto_base64") val fotoBase64: String? = null
)

data class MonitoringMapResponse(
    @Json(name = "default_center_lat") val defaultCenterLat: Double,
    @Json(name = "default_center_lng") val defaultCenterLng: Double,
    val pelanggan: List<Pelanggan>,
    @Json(name = "keluhan_aktif") val keluhanAktif: List<Keluhan>
)

data class ApiMessageResponse(val message: String)
