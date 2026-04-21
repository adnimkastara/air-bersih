package com.airbersih.mobile.model

import com.squareup.moshi.Json

data class LoginRequest(
    val email: String,
    val password: String,
    @Json(name = "device_name") val deviceName: String = "android-petugas"
)

data class LoginResponse(
    val message: String? = null,
    val data: LoginData? = null
)

data class LoginData(
    @Json(name = "token_type") val tokenType: String? = null,
    @Json(name = "access_token") val accessToken: String? = null,
    @Json(name = "device_name") val deviceName: String? = null,
    val user: User? = null
)

data class ApiEnvelope<T>(
    val message: String? = null,
    val data: T? = null
)

data class ApiPagination<T>(
    val data: List<T>? = emptyList()
)

data class User(
    val id: Long? = null,
    val name: String? = null,
    val email: String? = null,
    val role: UserRole? = null,
    val desa: UserDesa? = null
)

data class UserRole(
    val id: Long? = null,
    val name: String? = null
)

data class UserDesa(
    val id: Long? = null,
    val name: String? = null
)

data class DashboardSummary(
    @Json(name = "total_pelanggan") val jumlahPelanggan: Int? = 0,
    @Json(name = "total_keluhan_aktif") val keluhanAktif: Int? = 0,
    @Json(name = "total_tagihan_aktif") val tagihanAktif: Int? = 0
)

data class Pelanggan(
    val id: Long? = null,
    @Json(name = "kode_pelanggan") val kodePelanggan: String? = null,
    @Json(name = "name") val nama: String? = null,
    @Json(name = "address") val alamat: String? = null,
    @Json(name = "desa_id") val desaId: Long? = null,
    val status: String? = null,
    val latitude: Double? = null,
    val longitude: Double? = null
)

data class MeterRecordRequest(
    @Json(name = "pelanggan_id") val pelangganId: Long,
    @Json(name = "meter_previous_month") val meterPreviousMonth: Int,
    @Json(name = "meter_current_month") val meterCurrentMonth: Int,
    @Json(name = "recorded_at") val recordedAt: String,
    @Json(name = "gps_latitude") val gpsLatitude: Double? = null,
    @Json(name = "gps_longitude") val gpsLongitude: Double? = null,
    @Json(name = "gps_recorded_at") val gpsRecordedAt: String? = null,
    val notes: String? = null
)

data class Tagihan(
    val id: Long? = null,
    @Json(name = "pelanggan_id") val pelangganId: Long? = null,
    @Json(name = "period") val periode: String? = null,
    @Json(name = "amount") val nominal: Double? = 0.0,
    val status: String? = null
)

data class PembayaranRequest(
    @Json(name = "tagihan_id") val tagihanId: Long,
    @Json(name = "amount") val amount: Double,
    @Json(name = "payment_method") val paymentMethod: String,
    @Json(name = "paid_at") val paidAt: String,
    @Json(name = "notes") val notes: String? = null
)

data class Keluhan(
    val id: Long? = null,
    val judul: String? = null,
    val deskripsi: String? = null,
    @Json(name = "jenis_laporan") val jenisLaporan: String? = null,
    val prioritas: String? = null,
    @Json(name = "status_penanganan") val statusPenanganan: String? = null,
    val latitude: Double? = null,
    val longitude: Double? = null
)

data class KeluhanRequest(
    val judul: String,
    val deskripsi: String,
    @Json(name = "jenis_laporan") val jenisLaporan: String,
    val prioritas: String,
    val latitude: Double? = null,
    val longitude: Double? = null,
    @Json(name = "no_hp") val noHp: String = "-",
    val pelapor: String? = null
)

data class MonitoringMapResponse(
    @Json(name = "user_current_location") val userCurrentLocation: MonitoringLocation? = null,
    @Json(name = "fallback_center") val fallbackCenter: MonitoringLocation? = null,
    @Json(name = "pelanggans") val pelanggan: List<Pelanggan> = emptyList(),
    @Json(name = "keluhans") val keluhanAktif: List<Keluhan> = emptyList()
)

data class MonitoringLocation(
    val latitude: Double? = null,
    val longitude: Double? = null
)

data class ApiMessageResponse(val message: String? = null)
