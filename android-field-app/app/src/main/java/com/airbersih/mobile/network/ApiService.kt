package com.airbersih.mobile.network

import com.airbersih.mobile.model.*
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Path
import retrofit2.http.PUT
import retrofit2.http.Query

interface ApiService {
    @POST("login")
    suspend fun login(@Body request: LoginRequest): Response<LoginResponse>

    @POST("logout")
    suspend fun logout(): Response<ApiMessageResponse>

    @GET("me")
    suspend fun me(): Response<ApiEnvelope<User>>

    @PUT("profile/password")
    suspend fun updatePassword(@Body request: PasswordUpdateRequest): Response<ApiMessageResponse>

    @GET("dashboard-ringkas")
    suspend fun dashboard(): Response<ApiEnvelope<DashboardSummary>>

    @GET("pelanggan")
    suspend fun pelanggan(
        @Query("q") query: String? = null,
        @Query("desa") desa: String? = null
    ): Response<ApiPagination<Pelanggan>>

    @GET("pelanggan/{id}")
    suspend fun pelangganDetail(@Path("id") id: Long): Response<Pelanggan>

    @POST("pelanggan")
    suspend fun createPelanggan(@Body request: PelangganCreateRequest): Response<ApiEnvelope<Pelanggan>>

    @GET("meter-records")
    suspend fun meterRecords(): Response<ApiEnvelope<ApiPagination<MeterRecordItem>>>

    @POST("meter-records")
    suspend fun createMeter(@Body request: MeterRecordRequest): Response<ApiMessageResponse>

    @GET("tagihan")
    suspend fun tagihan(@Query("pelanggan_id") pelangganId: Long? = null): Response<ApiEnvelope<ApiPagination<Tagihan>>>

    @GET("tagihan/{id}")
    suspend fun tagihanDetail(@Path("id") id: Long): Response<ApiEnvelope<TagihanDetailResponse>>

    @POST("tagihan/{id}/publish")
    suspend fun publishTagihan(@Path("id") id: Long): Response<ApiMessageResponse>

    @GET("pembayaran")
    suspend fun pembayaranList(): Response<ApiEnvelope<ApiPagination<Pembayaran>>>

    @POST("pembayaran")
    suspend fun pembayaran(@Body request: PembayaranRequest): Response<ApiMessageResponse>

    @GET("keluhan")
    suspend fun keluhan(): Response<ApiEnvelope<ApiPagination<Keluhan>>>

    @POST("keluhan")
    suspend fun createKeluhan(@Body request: KeluhanRequest): Response<ApiMessageResponse>

    @GET("monitoring/peta")
    suspend fun monitoringMap(
        @Query("gps_latitude") gpsLatitude: Double? = null,
        @Query("gps_longitude") gpsLongitude: Double? = null
    ): Response<ApiEnvelope<MonitoringMapResponse>>
}
