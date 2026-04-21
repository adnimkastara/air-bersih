package com.airbersih.mobile.repository

import android.util.Log
import com.airbersih.mobile.data.TokenManager
import com.airbersih.mobile.model.*
import com.airbersih.mobile.network.ApiService
import com.squareup.moshi.JsonDataException
import kotlinx.coroutines.CancellationException
import okhttp3.ResponseBody
import okio.EOFException
import retrofit2.HttpException
import retrofit2.Response
import java.io.IOException
import java.net.SocketTimeoutException

class MainRepository(
    private val api: ApiService,
    private val tokenManager: TokenManager
) {

    suspend fun login(email: String, password: String): ResultState<LoginResponse> =
        safeApiCall("login") {
            handleResponse(api.login(LoginRequest(email, password))).also { result ->
                if (result is ResultState.Success) {
                    val token = result.data.data?.accessToken
                    if (!token.isNullOrBlank()) {
                        tokenManager.saveToken(token)
                    } else {
                        throw IllegalStateException("Token login kosong")
                    }
                }
            }
        }

    suspend fun logout(): ResultState<ApiMessageResponse> {
        val response = safeApiCall("logout") { handleResponse(api.logout()) }
        tokenManager.clearToken()
        return response
    }

    suspend fun me(): ResultState<User> =
        safeApiCall("me") {
            handleResponse(api.me()) { envelope ->
                envelope.data ?: throw IllegalStateException("Data profil kosong")
            }
        }

    suspend fun dashboard(): ResultState<DashboardSummary> =
        safeApiCall("dashboard") {
            handleResponse(api.dashboard()) { envelope ->
                envelope.data ?: DashboardSummary()
            }
        }

    suspend fun pelanggan(query: String?, desa: String?): ResultState<List<Pelanggan>> =
        safeApiCall("pelanggan") {
            handleResponse(api.pelanggan(query, desa)) { payload -> payload.data ?: emptyList() }
        }

    suspend fun pelangganDetail(id: Long) = safeApiCall("pelangganDetail") { handleResponse(api.pelangganDetail(id)) }
    suspend fun createMeter(request: MeterRecordRequest) = safeApiCall("createMeter") { handleResponse(api.createMeter(request)) }

    suspend fun tagihan(pelangganId: Long?): ResultState<List<Tagihan>> =
        safeApiCall("tagihan") {
            handleResponse(api.tagihan(pelangganId)) { envelope -> envelope.data?.data ?: emptyList() }
        }

    suspend fun createPembayaran(request: PembayaranRequest) = safeApiCall("createPembayaran") { handleResponse(api.pembayaran(request)) }

    suspend fun keluhan(): ResultState<List<Keluhan>> =
        safeApiCall("keluhan") {
            handleResponse(api.keluhan()) { envelope -> envelope.data?.data ?: emptyList() }
        }

    suspend fun createKeluhan(request: KeluhanRequest) = safeApiCall("createKeluhan") { handleResponse(api.createKeluhan(request)) }

    suspend fun monitoringMap(gpsLatitude: Double?, gpsLongitude: Double?): ResultState<MonitoringMapResponse> =
        safeApiCall("monitoringMap") {
            handleResponse(api.monitoringMap(gpsLatitude, gpsLongitude)) { envelope ->
                envelope.data ?: MonitoringMapResponse()
            }
        }

    private suspend fun <T> handleResponse(response: Response<T>): ResultState<T> =
        handleResponse(response) { it }

    private suspend fun <T, R> handleResponse(response: Response<T>, mapper: (T) -> R): ResultState<R> {
        if (response.isSuccessful) {
            val body = response.body() ?: return ResultState.Error("Respons kosong dari server", response.code())
            return try {
                ResultState.Success(mapper(body))
            } catch (e: Exception) {
                if (e is CancellationException) throw e
                Log.e("MainRepository", "Mapping gagal", e)
                ResultState.Error("Format data dari server tidak sesuai.", response.code())
            }
        }

        val code = response.code()
        val serverMessage = extractServerMessage(response.errorBody())
        return when (code) {
            401 -> {
                tokenManager.clearToken()
                ResultState.Error(serverMessage ?: "Sesi habis. Silakan login ulang.", 401)
            }
            403 -> ResultState.Error(serverMessage ?: "Akses ditolak (403).", 403)
            404 -> ResultState.Error(serverMessage ?: "Data tidak ditemukan (404).", 404)
            422 -> ResultState.Error(serverMessage ?: "Validasi gagal. Periksa input.", 422)
            in 500..599 -> ResultState.Error(serverMessage ?: "Server sedang bermasalah ($code).", code)
            else -> ResultState.Error(serverMessage ?: "Gagal ($code) ${response.message()}", code)
        }
    }

    private suspend fun <T> safeApiCall(tag: String, block: suspend () -> ResultState<T>): ResultState<T> {
        return try {
            block()
        } catch (e: Exception) {
            if (e is CancellationException) throw e
            Log.e("MainRepository", "API error on $tag", e)
            when (e) {
                is SocketTimeoutException -> ResultState.Error("Permintaan timeout. Coba lagi.")
                is JsonDataException,
                is EOFException,
                is IllegalArgumentException,
                is IllegalStateException -> ResultState.Error("Format respons tidak dikenali.")
                is HttpException -> ResultState.Error("HTTP ${e.code()} ${e.message()}", e.code())
                is IOException -> ResultState.Error("Tidak dapat terhubung ke server.")
                else -> ResultState.Error("Terjadi kesalahan tidak terduga.")
            }
        }
    }

    private fun extractServerMessage(errorBody: ResponseBody?): String? {
        return runCatching {
            val body = errorBody?.string().orEmpty().trim()
            if (body.isBlank()) return@runCatching null
            val messageRegex = "\"message\"\\s*:\\s*\"([^\"]+)\"".toRegex()
            messageRegex.find(body)?.groupValues?.getOrNull(1)
        }.getOrNull()
    }
}
