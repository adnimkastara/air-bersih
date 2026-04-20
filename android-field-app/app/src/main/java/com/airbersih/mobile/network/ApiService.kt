package com.airbersih.mobile.network

import com.airbersih.mobile.model.*
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Path
import retrofit2.http.Query

interface ApiService {
    @POST("api/v1/login")
    suspend fun login(@Body request: LoginRequest): Response<LoginResponse>

    @POST("api/v1/logout")
    suspend fun logout(): Response<ApiMessageResponse>

    @GET("api/v1/me")
    suspend fun me(): Response<User>

    @GET("api/v1/dashboard-ringkas")
    suspend fun dashboard(): Response<DashboardSummary>

    @GET("api/v1/pelanggan")
    suspend fun pelanggan(
        @Query("q") query: String? = null,
        @Query("desa") desa: String? = null
    ): Response<List<Pelanggan>>

    @GET("api/v1/pelanggan/{id}")
    suspend fun pelangganDetail(@Path("id") id: Long): Response<Pelanggan>

    @POST("api/v1/meter-records")
    suspend fun createMeter(@Body request: MeterRecordRequest): Response<ApiMessageResponse>

    @GET("api/v1/tagihan")
    suspend fun tagihan(@Query("pelanggan_id") pelangganId: Long? = null): Response<List<Tagihan>>

    @POST("api/v1/pembayaran")
    suspend fun pembayaran(@Body request: PembayaranRequest): Response<ApiMessageResponse>

    @GET("api/v1/keluhan")
    suspend fun keluhan(): Response<List<Keluhan>>

    @POST("api/v1/keluhan")
    suspend fun createKeluhan(@Body request: KeluhanRequest): Response<ApiMessageResponse>

    @GET("api/v1/monitoring/peta")
    suspend fun monitoringMap(): Response<MonitoringMapResponse>
}
