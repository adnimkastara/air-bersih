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
    @Json(name = "no_hp") val noHp: String? = null,
    @Json(name = "desa_id") val desaId: Long? = null,
    @Json(name = "kecamatan_id") val kecamatanId: Long? = null,
    val role: UserRole? = null,
    val desa: UserDesa? = null,
    val kecamatan: UserKecamatan? = null
)

data class UserRole(
    val id: Long? = null,
    val name: String? = null
)

data class UserDesa(
    val id: Long? = null,
    val name: String? = null,
    @Json(name = "kecamatan_id") val kecamatanId: Long? = null
)

data class UserKecamatan(
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
    @Json(name = "kecamatan_id") val kecamatanId: Long? = null,
    @Json(name = "assigned_petugas_id") val assignedPetugasId: Long? = null,
    @Json(name = "desa_name") val desaName: String? = null,
    @Json(name = "kecamatan_name") val kecamatanName: String? = null,
    @Json(name = "assigned_petugas_name") val assignedPetugasName: String? = null,
    val status: String? = null,
    val latitude: Double? = null,
    val longitude: Double? = null
)

data class PelangganCreateRequest(
    val name: String,
    val email: String? = null,
    val phone: String? = null,
    val address: String,
    val dusun: String,
    @Json(name = "kecamatan_id") val kecamatanId: Long? = null,
    @Json(name = "desa_id") val desaId: Long? = null,
    @Json(name = "assigned_petugas_id") val assignedPetugasId: Long? = null,
    @Json(name = "jenis_pelanggan") val jenisPelanggan: String,
    @Json(name = "nomor_meter") val nomorMeter: String,
    val status: String = "aktif",
    val latitude: Double? = null,
    val longitude: Double? = null
)

data class TagihanGenerateRequest(
    val period: String
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
    val status: String? = null,
    val pelanggan: Pelanggan? = null
)

data class TagihanDetailResponse(
    val tagihan: Tagihan? = null,
    @Json(name = "total_paid") val totalPaid: Double? = 0.0,
    val remaining: Double? = 0.0
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
    @Json(name = "pelanggan_id") val pelangganId: Long? = null,
    val latitude: Double? = null,
    val longitude: Double? = null,
    @Json(name = "no_hp") val noHp: String,
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

data class PasswordUpdateRequest(
    @Json(name = "current_password") val currentPassword: String,
    val password: String,
    @Json(name = "password_confirmation") val passwordConfirmation: String
)

data class MeterRecordItem(
    val id: Long? = null,
    @Json(name = "meter_previous_month") val meterPreviousMonth: Int? = null,
    @Json(name = "meter_current_month") val meterCurrentMonth: Int? = null,
    @Json(name = "recorded_at") val recordedAt: String? = null,
    @Json(name = "verification_status") val verificationStatus: String? = null,
    val pelanggan: Pelanggan? = null
)

data class Pembayaran(
    val id: Long? = null,
    @Json(name = "tagihan_id") val tagihanId: Long? = null,
    val amount: Double? = 0.0,
    @Json(name = "payment_method") val paymentMethod: String? = null,
    @Json(name = "paid_at") val paidAt: String? = null,
    val tagihan: Tagihan? = null
)

data class CustomerAutoFill(
    val petugasId: Long? = null,
    val petugasName: String = "-",
    val desaId: Long? = null,
    val desaName: String = "-",
    val kecamatanId: Long? = null,
    val kecamatanName: String = "-"
)
