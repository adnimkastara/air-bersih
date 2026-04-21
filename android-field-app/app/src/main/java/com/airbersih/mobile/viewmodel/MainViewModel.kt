package com.airbersih.mobile.viewmodel

import android.app.Application
import android.util.Log
import androidx.lifecycle.AndroidViewModel
import androidx.lifecycle.viewModelScope
import com.airbersih.mobile.data.TokenManager
import com.airbersih.mobile.model.*
import com.airbersih.mobile.network.NetworkModule
import com.airbersih.mobile.repository.MainRepository
import com.airbersih.mobile.repository.ResultState
import com.airbersih.mobile.utils.DateTimeUtils
import com.airbersih.mobile.utils.LocationHelper
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch

class MainViewModel(app: Application) : AndroidViewModel(app) {

    private val tokenManager = TokenManager(app)
    private var cachedToken: String? = null
    private val api = NetworkModule.apiService { cachedToken }
    private val repository = MainRepository(api, tokenManager)
    private val locationHelper = LocationHelper(app)

    private val _isLoggedIn = MutableStateFlow(false)
    val isLoggedIn: StateFlow<Boolean> = _isLoggedIn

    private val _me = MutableStateFlow<User?>(null)
    val me: StateFlow<User?> = _me

    private val _dashboard = MutableStateFlow(DashboardSummary())
    val dashboard: StateFlow<DashboardSummary> = _dashboard

    private val _pelanggan = MutableStateFlow<List<Pelanggan>>(emptyList())
    val pelanggan: StateFlow<List<Pelanggan>> = _pelanggan

    private val _tagihan = MutableStateFlow<List<Tagihan>>(emptyList())
    val tagihan: StateFlow<List<Tagihan>> = _tagihan

    private val _keluhan = MutableStateFlow<List<Keluhan>>(emptyList())
    val keluhan: StateFlow<List<Keluhan>> = _keluhan

    private val _monitoring = MutableStateFlow<MonitoringMapResponse?>(null)
    val monitoring: StateFlow<MonitoringMapResponse?> = _monitoring

    private val _statusMessage = MutableStateFlow<String?>(null)
    val statusMessage: StateFlow<String?> = _statusMessage

    init {
        safeLaunch("initSession") {
            cachedToken = tokenManager.tokenFlow.first()
            _isLoggedIn.value = !cachedToken.isNullOrBlank()
            Log.d("MainViewModel", "initSession loggedIn=${_isLoggedIn.value}")
            if (_isLoggedIn.value) {
                refreshInitialData()
            }
        }
    }

    fun login(email: String, password: String) {
        if (email.isBlank() || password.isBlank()) {
            _statusMessage.value = "Email dan password wajib diisi."
            return
        }
        safeLaunch("login") {
            _statusMessage.value = null
            Log.d("MainViewModel", "Attempting login for email=$email")
            when (val result = repository.login(email, password)) {
                is ResultState.Success -> {
                    val token = result.data.data?.accessToken
                    if (token.isNullOrBlank()) {
                        _statusMessage.value = "Token login tidak valid."
                        return@safeLaunch
                    }
                    cachedToken = token
                    _isLoggedIn.value = true
                    _me.value = result.data.data.user
                    Log.d("MainViewModel", "login success, user=${_me.value?.email}, tokenStored=${token.isNotBlank()}")
                    refreshInitialData()
                }
                is ResultState.Error -> {
                    Log.w("MainViewModel", "login failed code=${result.code} message=${result.message}")
                    if (!handleUnauthorized(result)) _statusMessage.value = result.message
                }
                ResultState.Loading -> Unit
            }
        }
    }

    fun logout() {
        safeLaunch("logout") {
            repository.logout()
            resetSessionData()
        }
    }

    fun refreshInitialData() {
        safeLaunch("refreshInitialData") {
            Log.d("MainViewModel", "Refreshing initial data after login/session restore")
            repository.me().consume(
                onSuccess = { _me.value = it },
                onError = { _statusMessage.value = it.message }
            )
            repository.dashboard().consume(
                onSuccess = { _dashboard.value = it },
                onError = { _statusMessage.value = it.message }
            )
            loadPelanggan()
            loadKeluhan()
        }
    }

    fun loadPelanggan(query: String? = null, desa: String? = null) {
        safeLaunch("loadPelanggan") {
            when (val result = repository.pelanggan(query, desa)) {
                is ResultState.Success -> _pelanggan.value = result.data
                is ResultState.Error -> if (!handleUnauthorized(result)) _statusMessage.value = result.message
                ResultState.Loading -> Unit
            }
        }
    }

    fun loadTagihan(pelangganId: Long?) {
        safeLaunch("loadTagihan") {
            when (val result = repository.tagihan(pelangganId)) {
                is ResultState.Success -> _tagihan.value = result.data
                is ResultState.Error -> if (!handleUnauthorized(result)) _statusMessage.value = result.message
                ResultState.Loading -> Unit
            }
        }
    }

    fun loadKeluhan() {
        safeLaunch("loadKeluhan") {
            when (val result = repository.keluhan()) {
                is ResultState.Success -> _keluhan.value = result.data
                is ResultState.Error -> if (!handleUnauthorized(result)) _statusMessage.value = result.message
                ResultState.Loading -> Unit
            }
        }
    }

    fun submitMeter(pelangganId: Long, angka: Int, tanggal: String = DateTimeUtils.todayIsoDate()) {
        safeLaunch("submitMeter") {
            val loc = locationHelper.getCurrentLocationOrNull()
            val request = MeterRecordRequest(
                pelangganId = pelangganId,
                meterPreviousMonth = (angka - 1).coerceAtLeast(0),
                meterCurrentMonth = angka,
                recordedAt = tanggal,
                gpsLatitude = loc?.latitude,
                gpsLongitude = loc?.longitude,
                gpsRecordedAt = DateTimeUtils.todayIsoDate()
            )
            when (val result = repository.createMeter(request)) {
                is ResultState.Success -> _statusMessage.value = result.data.message ?: "Meter record tersimpan."
                is ResultState.Error -> if (!handleUnauthorized(result)) _statusMessage.value = result.message
                ResultState.Loading -> Unit
            }
        }
    }

    fun submitPembayaran(tagihanId: Long, nominal: Long, metode: String, tanggal: String, catatan: String) {
        safeLaunch("submitPembayaran") {
            when (val result = repository.createPembayaran(
                PembayaranRequest(
                    tagihanId = tagihanId,
                    amount = nominal.toDouble(),
                    paymentMethod = metode,
                    paidAt = tanggal,
                    notes = catatan.ifBlank { null }
                )
            )) {
                is ResultState.Success -> _statusMessage.value = result.data.message ?: "Pembayaran tersimpan."
                is ResultState.Error -> if (!handleUnauthorized(result)) _statusMessage.value = result.message
                ResultState.Loading -> Unit
            }
        }
    }

    fun submitKeluhan(judul: String, deskripsi: String, kategori: String, prioritas: String) {
        safeLaunch("submitKeluhan") {
            val loc = locationHelper.getCurrentLocationOrNull()
            when (val result = repository.createKeluhan(
                KeluhanRequest(
                    judul = judul,
                    deskripsi = deskripsi,
                    jenisLaporan = kategori,
                    prioritas = prioritas,
                    latitude = loc?.latitude,
                    longitude = loc?.longitude
                )
            )) {
                is ResultState.Success -> {
                    _statusMessage.value = result.data.message ?: "Keluhan tersimpan."
                    loadKeluhan()
                }
                is ResultState.Error -> if (!handleUnauthorized(result)) _statusMessage.value = result.message
                ResultState.Loading -> Unit
            }
        }
    }

    fun loadMonitoring() {
        safeLaunch("loadMonitoring") {
            val loc = locationHelper.getCurrentLocationOrNull()
            when (val result = repository.monitoringMap(loc?.latitude, loc?.longitude)) {
                is ResultState.Success -> _monitoring.value = result.data
                is ResultState.Error -> if (!handleUnauthorized(result)) _statusMessage.value = result.message
                ResultState.Loading -> Unit
            }
        }
    }

    private fun handleUnauthorized(result: ResultState.Error): Boolean {
        if (result.code == 401) {
            resetSessionData()
            _statusMessage.value = result.message
            return true
        }
        return false
    }

    private fun resetSessionData() {
        cachedToken = null
        _isLoggedIn.value = false
        _me.value = null
        _dashboard.value = DashboardSummary()
        _pelanggan.value = emptyList()
        _tagihan.value = emptyList()
        _keluhan.value = emptyList()
        _monitoring.value = null
    }

    private fun safeLaunch(tag: String, block: suspend () -> Unit) {
        viewModelScope.launch {
            try {
                block()
            } catch (e: Exception) {
                Log.e("MainViewModel", "Unhandled error in $tag", e)
                _statusMessage.value = "Terjadi kesalahan aplikasi. Silakan coba lagi."
            }
        }
    }

    private fun <T> ResultState<T>.consume(onSuccess: (T) -> Unit, onError: (ResultState.Error) -> Unit) {
        when (this) {
            is ResultState.Success -> onSuccess(data)
            is ResultState.Error -> if (!handleUnauthorized(this)) onError(this)
            ResultState.Loading -> Unit
        }
    }

    fun clearMessage() {
        _statusMessage.value = null
    }
}
