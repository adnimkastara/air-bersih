package com.airbersih.mobile.repository

import com.airbersih.mobile.data.TokenManager
import com.airbersih.mobile.model.*
import com.airbersih.mobile.network.ApiService
import retrofit2.Response

class MainRepository(
    private val api: ApiService,
    private val tokenManager: TokenManager
) {
    suspend fun login(email: String, password: String): ResultState<LoginResponse> =
        handleResponse(api.login(LoginRequest(email, password))).also {
            if (it is ResultState.Success) tokenManager.saveToken(it.data.token)
        }

    suspend fun logout(): ResultState<ApiMessageResponse> {
        val response = handleResponse(api.logout())
        tokenManager.clearToken()
        return response
    }

    suspend fun me() = handleResponse(api.me())
    suspend fun dashboard() = handleResponse(api.dashboard())
    suspend fun pelanggan(query: String?, desa: String?) = handleResponse(api.pelanggan(query, desa))
    suspend fun pelangganDetail(id: Long) = handleResponse(api.pelangganDetail(id))
    suspend fun createMeter(request: MeterRecordRequest) = handleResponse(api.createMeter(request))
    suspend fun tagihan(pelangganId: Long?) = handleResponse(api.tagihan(pelangganId))
    suspend fun createPembayaran(request: PembayaranRequest) = handleResponse(api.pembayaran(request))
    suspend fun keluhan() = handleResponse(api.keluhan())
    suspend fun createKeluhan(request: KeluhanRequest) = handleResponse(api.createKeluhan(request))
    suspend fun monitoringMap() = handleResponse(api.monitoringMap())

    private fun <T> handleResponse(response: Response<T>): ResultState<T> {
        return if (response.isSuccessful) {
            response.body()?.let { ResultState.Success(it) }
                ?: ResultState.Error("Respons kosong dari server", response.code())
        } else {
            when (response.code()) {
                401 -> ResultState.Error("Sesi habis. Silakan login ulang.", 401)
                422 -> ResultState.Error("Validasi gagal. Periksa input.", 422)
                else -> ResultState.Error("Gagal (${response.code()}) ${response.message()}", response.code())
            }
        }
    }
}
