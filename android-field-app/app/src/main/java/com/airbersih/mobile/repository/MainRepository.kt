package com.airbersih.mobile.repository

import com.airbersih.mobile.data.TokenManager
import com.airbersih.mobile.model.*
import com.airbersih.mobile.network.ApiService
import com.airbersih.mobile.utils.MenuLogger
import com.squareup.moshi.JsonAdapter
import com.squareup.moshi.JsonDataException
import com.squareup.moshi.Moshi
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
    private val moshi = Moshi.Builder().build()
    private val mapAdapter: JsonAdapter<Map<String, Any?>> = moshi.adapter<Map<String, Any?>>(Map::class.java) as JsonAdapter<Map<String, Any?>>

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

    suspend fun updatePassword(request: PasswordUpdateRequest) =
        safeApiCall("updatePassword", "PUT /profile/password") {
            handleResponse(api.updatePassword(request), "PUT /profile/password")
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
            handleResponse(api.pelanggan(query, desa), "GET /pelanggan") { payload ->
                payload.data?.data ?: emptyList()
            }
        }

    suspend fun pelangganDetail(id: Long) = safeApiCall("pelangganDetail", "GET /pelanggan/{id}") {
        handleResponse(api.pelangganDetail(id), "GET /pelanggan/$id") { it.data ?: throw IllegalStateException("Data pelanggan kosong") }
    }

    suspend fun createPelanggan(request: PelangganCreateRequest): ResultState<Pelanggan> =
        safeApiCall("createPelanggan", "POST /pelanggan") {
            MenuLogger.payload("feature=tambah_pelanggan endpoint=POST /pelanggan name=${request.name} meter=${request.nomorMeter} desa_id=${request.desaId} kecamatan_id=${request.kecamatanId} assigned_petugas_id=${request.assignedPetugasId} has_latlng=${request.latitude != null && request.longitude != null}")
            handleResponse(api.createPelanggan(request), "POST /pelanggan") { envelope ->
                envelope.data ?: Pelanggan(nama = request.name, alamat = request.address, desaId = request.desaId, kecamatanId = request.kecamatanId, assignedPetugasId = request.assignedPetugasId, latitude = request.latitude, longitude = request.longitude, status = request.status)
            }
        }

    suspend fun meterRecords(): ResultState<List<MeterRecordItem>> =
        safeApiCall("meterRecords", "GET /meter-records") {
            handleResponse(api.meterRecords(), "GET /meter-records") { envelope -> envelope.data?.data ?: emptyList() }
        }

    suspend fun createMeter(request: MeterRecordRequest) = safeApiCall("createMeter", "POST /meter-records") {
        MenuLogger.payload("endpoint=POST /meter-records pelangganId=${request.pelangganId} recordedAt=${request.recordedAt} gps=${request.gpsLatitude ?: "-"},${request.gpsLongitude ?: "-"}")
        handleResponse(api.createMeter(request), "POST /meter-records")
    }

    suspend fun tagihan(pelangganId: Long?): ResultState<List<Tagihan>> =
        safeApiCall("tagihan", "GET /tagihan") {
            MenuLogger.api("endpoint=GET /tagihan pelanggan_id=${pelangganId ?: "all"}")
            handleResponse(api.tagihan(pelangganId), "GET /tagihan") { envelope -> envelope.data?.data ?: emptyList() }
        }

    suspend fun tagihanDetail(id: Long): ResultState<TagihanDetailResponse> =
        safeApiCall("tagihanDetail", "GET /tagihan/{id}") {
            handleResponse(api.tagihanDetail(id), "GET /tagihan/$id") { it.data ?: TagihanDetailResponse() }
        }

    suspend fun generateTagihan(period: String) = safeApiCall("generateTagihan", "POST /tagihan/generate") {
        MenuLogger.api("endpoint=POST /tagihan/generate period=$period")
        handleResponse(api.generateTagihan(TagihanGenerateRequest(period)), "POST /tagihan/generate")
    }

    suspend fun publishTagihan(id: Long) = safeApiCall("publishTagihan", "POST /tagihan/{id}/publish") {
        handleResponse(api.publishTagihan(id), "POST /tagihan/$id/publish")
    }

    suspend fun pembayaranList(): ResultState<List<Pembayaran>> =
        safeApiCall("pembayaranList", "GET /pembayaran") {
            handleResponse(api.pembayaranList(), "GET /pembayaran") { envelope -> envelope.data?.data ?: emptyList() }
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
        MenuLogger.keluhanPayload("endpoint=POST /keluhan jenis=${request.jenisLaporan} prioritas=${request.prioritas} pelanggan_id=${request.pelangganId ?: "null"} has_latlng=${request.latitude != null && request.longitude != null} pelapor=${request.pelapor ?: "-"} no_hp=${request.noHp.take(4)}****")
        handleResponse(api.createKeluhan(request), "POST /keluhan")
    }

    suspend fun monitoringMap(gpsLatitude: Double?, gpsLongitude: Double?): ResultState<MonitoringMapResponse> =
        safeApiCall("monitoringMap", "GET /monitoring/peta") {
            MenuLogger.mapFlow("MAP_INIT endpoint=GET /monitoring/peta gps=${gpsLatitude ?: "-"},${gpsLongitude ?: "-"}")
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
            val parsed = mapAdapter.fromJson(body)
            val topMessage = parsed?.get("message") as? String
            val errors = parsed?.get("errors")
            val firstError = when (errors) {
                is Map<*, *> -> errors.values.firstOrNull()?.toString()
                is List<*> -> errors.firstOrNull()?.toString()
                else -> null
            }
            listOfNotNull(topMessage, firstError).firstOrNull { it.isNotBlank() }
        }.getOrNull()
    }
}
