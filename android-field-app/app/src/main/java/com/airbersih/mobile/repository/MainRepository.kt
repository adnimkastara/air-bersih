package com.airbersih.mobile.repository

import com.airbersih.mobile.data.TokenManager
import com.airbersih.mobile.model.*
import com.airbersih.mobile.network.ApiService
import com.airbersih.mobile.utils.MenuLogger
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
        safeApiCall("login", "POST /login") {
            handleResponse(api.login(LoginRequest(email, password)), "POST /login").also { result ->
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
        val response = safeApiCall("logout", "POST /logout") { handleResponse(api.logout(), "POST /logout") }
        tokenManager.clearToken()
        return response
    }

    suspend fun me(): ResultState<User> =
        safeApiCall("me", "GET /me") {
            handleResponse(api.me(), "GET /me") { envelope ->
                envelope.data ?: throw IllegalStateException("Data profil kosong")
            }
        }

    suspend fun dashboard(): ResultState<DashboardSummary> =
        safeApiCall("dashboard", "GET /dashboard-ringkas") {
            handleResponse(api.dashboard(), "GET /dashboard-ringkas") { envelope ->
                envelope.data ?: DashboardSummary()
            }
        }

    suspend fun pelanggan(query: String?, desa: String?): ResultState<List<Pelanggan>> =
        safeApiCall("pelanggan", "GET /pelanggan") {
            MenuLogger.api("endpoint=GET /pelanggan query='${query.orEmpty()}' desa='${desa.orEmpty()}'")
            handleResponse(api.pelanggan(query, desa), "GET /pelanggan") { payload -> payload.data ?: emptyList() }
        }

    suspend fun pelangganDetail(id: Long) = safeApiCall("pelangganDetail", "GET /pelanggan/{id}") {
        handleResponse(api.pelangganDetail(id), "GET /pelanggan/$id")
    }

    suspend fun createMeter(request: MeterRecordRequest) = safeApiCall("createMeter", "POST /meter-records") {
        MenuLogger.api("endpoint=POST /meter-records pelangganId=${request.pelangganId} recordedAt=${request.recordedAt}")
        handleResponse(api.createMeter(request), "POST /meter-records")
    }

    suspend fun tagihan(pelangganId: Long?): ResultState<List<Tagihan>> =
        safeApiCall("tagihan", "GET /tagihan") {
            MenuLogger.api("endpoint=GET /tagihan pelanggan_id=${pelangganId ?: "all"}")
            handleResponse(api.tagihan(pelangganId), "GET /tagihan") { envelope -> envelope.data?.data ?: emptyList() }
        }

    suspend fun createPembayaran(request: PembayaranRequest) = safeApiCall("createPembayaran", "POST /pembayaran") {
        MenuLogger.api("endpoint=POST /pembayaran tagihanId=${request.tagihanId} method=${request.paymentMethod}")
        handleResponse(api.pembayaran(request), "POST /pembayaran")
    }

    suspend fun keluhan(): ResultState<List<Keluhan>> =
        safeApiCall("keluhan", "GET /keluhan") {
            handleResponse(api.keluhan(), "GET /keluhan") { envelope -> envelope.data?.data ?: emptyList() }
        }

    suspend fun createKeluhan(request: KeluhanRequest) = safeApiCall("createKeluhan", "POST /keluhan") {
        MenuLogger.api("endpoint=POST /keluhan jenis=${request.jenisLaporan} prioritas=${request.prioritas}")
        handleResponse(api.createKeluhan(request), "POST /keluhan")
    }

    suspend fun monitoringMap(gpsLatitude: Double?, gpsLongitude: Double?): ResultState<MonitoringMapResponse> =
        safeApiCall("monitoringMap", "GET /monitoring/peta") {
            MenuLogger.api("endpoint=GET /monitoring/peta gps=${gpsLatitude ?: "-"},${gpsLongitude ?: "-"}")
            handleResponse(api.monitoringMap(gpsLatitude, gpsLongitude), "GET /monitoring/peta") { envelope ->
                envelope.data ?: MonitoringMapResponse()
            }
        }

    private suspend fun <T> handleResponse(response: Response<T>, endpoint: String): ResultState<T> =
        handleResponse(response, endpoint) { it }

    private suspend fun <T, R> handleResponse(response: Response<T>, endpoint: String, mapper: (T) -> R): ResultState<R> {
        val code = response.code()
        if (response.isSuccessful) {
            val body = response.body() ?: return ResultState.Error("Respons kosong dari server", code)
            return try {
                val mapped = mapper(body)
                MenuLogger.api("endpoint=$endpoint status=success code=$code")
                ResultState.Success(mapped)
            } catch (e: Exception) {
                if (e is CancellationException) throw e
                MenuLogger.error("endpoint=$endpoint status=parse_failed code=$code message=${e.message}", e)
                ResultState.Error("Format data dari server tidak sesuai.", code)
            }
        }

        val serverMessage = extractServerMessage(response.errorBody())
        MenuLogger.error("endpoint=$endpoint status=failed code=$code message=${serverMessage ?: response.message()}")
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

    private suspend fun <T> safeApiCall(tag: String, endpoint: String, block: suspend () -> ResultState<T>): ResultState<T> {
        return try {
            MenuLogger.api("request_start tag=$tag endpoint=$endpoint")
            block()
        } catch (e: Exception) {
            if (e is CancellationException) throw e
            MenuLogger.error("endpoint=$endpoint status=exception message=${e.message}", e)
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
