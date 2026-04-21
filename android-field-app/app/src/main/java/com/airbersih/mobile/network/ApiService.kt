package com.airbersih.mobile.network

import com.airbersih.mobile.model.*
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Path
import retrofit2.http.Query

interface ApiService {
    @POST("login")
    suspend fun login(@Body request: LoginRequest): Response<LoginResponse>

    @POST("logout")
    suspend fun logout(): Response<ApiMessageResponse>

    @GET("me")
    suspend fun me(): Response<ApiDataResponse<User>>

    @GET("dashboard-ringkas")
    suspend fun dashboard(): Response<ApiDataResponse<DashboardSummary>>

    @GET("pelanggan")
    suspend fun pelanggan(
        @Query("q") query: String? = null,
        @Query("desa") desa: String? = null
    ): Response<List<Pelanggan>>

    @GET("pelanggan/{id}")
    suspend fun pelangganDetail(@Path("id") id: Long): Response<Pelanggan>

    @POST("meter-records")
    suspend fun createMeter(@Body request: MeterRecordRequest): Response<ApiMessageResponse>

    @GET("tagihan")
    suspend fun tagihan(@Query("pelanggan_id") pelangganId: Long? = null): Response<List<Tagihan>>

    @POST("pembayaran")
    suspend fun pembayaran(@Body request: PembayaranRequest): Response<ApiMessageResponse>

    @GET("keluhan")
    suspend fun keluhan(): Response<List<Keluhan>>

    @POST("keluhan")
    suspend fun createKeluhan(@Body request: KeluhanRequest): Response<ApiMessageResponse>

    @GET("monitoring/peta")
    suspend fun monitoringMap(): Response<MonitoringMapResponse>
}
