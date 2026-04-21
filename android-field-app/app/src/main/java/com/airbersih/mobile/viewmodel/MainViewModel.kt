package com.airbersih.mobile.viewmodel

import android.app.Application
import androidx.lifecycle.AndroidViewModel
import androidx.lifecycle.viewModelScope
import com.airbersih.mobile.data.TokenManager
import com.airbersih.mobile.model.*
import com.airbersih.mobile.network.NetworkModule
import com.airbersih.mobile.repository.MainRepository
import com.airbersih.mobile.repository.ResultState
import com.airbersih.mobile.utils.DateTimeUtils
import com.airbersih.mobile.utils.LocationHelper
import com.airbersih.mobile.utils.MenuLogger
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

    private val _loadingMenu = MutableStateFlow<String?>(null)
    val loadingMenu: StateFlow<String?> = _loadingMenu

    init {
        safeLaunch("initSession") {
            cachedToken = tokenManager.tokenFlow.first()
            _isLoggedIn.value = !cachedToken.isNullOrBlank()
            MenuLogger.nav("session_restored loggedIn=${_isLoggedIn.value}")
            if (_isLoggedIn.value) refreshInitialData()
        }
    }

    fun login(email: String, password: String) {
        if (email.isBlank() || password.isBlank()) {
            setMessage("Email dan password wajib diisi.")
            return
        }
        safeLaunch("login") {
            _statusMessage.value = null
            when (val result = repository.login(email, password)) {
                is ResultState.Success -> {
                    val token = result.data.data?.accessToken
                    if (token.isNullOrBlank()) {
                        setMessage("Token login tidak valid.")
                        return@safeLaunch
                    }
                    cachedToken = token
                    _isLoggedIn.value = true
                    _me.value = result.data.data.user
                    MenuLogger.nav("login_success user=${_me.value?.email ?: "unknown"}")
                    refreshInitialData()
                }
                is ResultState.Error -> {
                    MenuLogger.error("login_failed code=${result.code} message=${result.message}")
                    if (!handleUnauthorized(result)) setMessage(result.message)
                }
                ResultState.Loading -> Unit
            }
        }
    }

    fun logout() {
        safeLaunch("logout") {
            repository.logout()
            MenuLogger.nav("logout_clicked")
            resetSessionData()
        }
    }

    fun refreshInitialData() {
        safeLaunch("refreshInitialData") {
            MenuLogger.api("dashboard bootstrap start")
            repository.me().consume(
                onSuccess = { _me.value = it },
                onError = { setMessage(it.message) }
            )
            repository.dashboard().consume(
                onSuccess = { _dashboard.value = it },
                onError = { setMessage(it.message) }
            )
            loadPelanggan()
            loadKeluhan()
        }
    }

    fun loadPelanggan(query: String? = null, desa: String? = null) {
        safeLaunch("loadPelanggan") {
            setLoading("pelanggan")
            when (val result = repository.pelanggan(query, desa)) {
                is ResultState.Success -> {
                    _pelanggan.value = result.data
                    MenuLogger.api("menu=pelanggan loaded_count=${result.data.size}")
                    if (result.data.isEmpty()) setMessage("Data pelanggan kosong untuk filter saat ini.")
                }
                is ResultState.Error -> if (!handleUnauthorized(result)) setMessage(result.message)
                ResultState.Loading -> Unit
            }
            clearLoading("pelanggan")
        }
    }

    fun loadTagihan(pelangganId: Long?) {
        safeLaunch("loadTagihan") {
            setLoading("tagihan")
            when (val result = repository.tagihan(pelangganId)) {
                is ResultState.Success -> {
                    _tagihan.value = result.data
                    MenuLogger.api("menu=tagihan loaded_count=${result.data.size}")
                    if (result.data.isEmpty()) setMessage("Tagihan tidak ditemukan.")
                }
                is ResultState.Error -> if (!handleUnauthorized(result)) setMessage(result.message)
                ResultState.Loading -> Unit
            }
            clearLoading("tagihan")
        }
    }

    fun loadKeluhan() {
        safeLaunch("loadKeluhan") {
            setLoading("keluhan")
            when (val result = repository.keluhan()) {
                is ResultState.Success -> {
                    _keluhan.value = result.data
                    MenuLogger.api("menu=keluhan loaded_count=${result.data.size}")
                    if (result.data.isEmpty()) setMessage("Belum ada keluhan aktif.")
                }
                is ResultState.Error -> if (!handleUnauthorized(result)) setMessage(result.message)
                ResultState.Loading -> Unit
            }
            clearLoading("keluhan")
        }
    }

    fun submitMeter(pelangganId: Long, angka: Int, tanggal: String = DateTimeUtils.todayIsoDate()) {
        safeLaunch("submitMeter") {
            if (angka < 0) {
                setMessage("Angka meter tidak valid.")
                return@safeLaunch
            }
            setLoading("meter")
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
                is ResultState.Success -> setMessage(result.data.message ?: "Meter record tersimpan.")
                is ResultState.Error -> if (!handleUnauthorized(result)) setMessage(result.message)
                ResultState.Loading -> Unit
            }
            clearLoading("meter")
        }
    }

    fun submitPembayaran(tagihanId: Long, nominal: Long, metode: String, tanggal: String, catatan: String) {
        safeLaunch("submitPembayaran") {
            if (nominal <= 0L) {
                setMessage("Nominal pembayaran harus lebih dari 0.")
                return@safeLaunch
            }
            setLoading("pembayaran")
            when (
                val result = repository.createPembayaran(
                    PembayaranRequest(
                        tagihanId = tagihanId,
                        amount = nominal.toDouble(),
                        paymentMethod = metode,
                        paidAt = tanggal,
                        notes = catatan.ifBlank { null }
                    )
                )
            ) {
                is ResultState.Success -> setMessage(result.data.message ?: "Pembayaran tersimpan.")
                is ResultState.Error -> if (!handleUnauthorized(result)) setMessage(result.message)
                ResultState.Loading -> Unit
            }
            clearLoading("pembayaran")
        }
    }

    fun submitKeluhan(judul: String, deskripsi: String, kategori: String, prioritas: String) {
        safeLaunch("submitKeluhan") {
            if (judul.isBlank() || deskripsi.isBlank()) {
                setMessage("Judul dan deskripsi keluhan wajib diisi.")
                return@safeLaunch
            }
            setLoading("keluhan")
            val loc = locationHelper.getCurrentLocationOrNull()
            when (
                val result = repository.createKeluhan(
                    KeluhanRequest(
                        judul = judul,
                        deskripsi = deskripsi,
                        jenisLaporan = kategori,
                        prioritas = prioritas,
                        latitude = loc?.latitude,
                        longitude = loc?.longitude,
                        pelapor = _me.value?.name
                    )
                )
            ) {
                is ResultState.Success -> {
                    setMessage(result.data.message ?: "Keluhan tersimpan.")
                    loadKeluhan()
                }
                is ResultState.Error -> if (!handleUnauthorized(result)) setMessage(result.message)
                ResultState.Loading -> Unit
            }
            clearLoading("keluhan")
        }
    }

    fun loadMonitoring() {
        safeLaunch("loadMonitoring") {
            setLoading("monitoring")
            val loc = locationHelper.getCurrentLocationOrNull()
            when (val result = repository.monitoringMap(loc?.latitude, loc?.longitude)) {
                is ResultState.Success -> {
                    _monitoring.value = result.data
                    val total = result.data.pelanggan.size + result.data.keluhanAktif.size
                    MenuLogger.api("menu=monitoring markers=$total")
                    if (total == 0) setMessage("Data monitoring kosong pada area ini.")
                }
                is ResultState.Error -> if (!handleUnauthorized(result)) setMessage(result.message)
                ResultState.Loading -> Unit
            }
            clearLoading("monitoring")
        }
    }

    private fun handleUnauthorized(result: ResultState.Error): Boolean {
        if (result.code == 401) {
            MenuLogger.error("unauthorized_detected code=401")
            resetSessionData()
            setMessage(result.message)
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
                MenuLogger.error("vm_unhandled tag=$tag message=${e.message}", e)
                setMessage("Terjadi kesalahan aplikasi. Silakan coba lagi.")
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

    private fun setLoading(menu: String) {
        _loadingMenu.value = menu
        MenuLogger.nav("loading_start menu=$menu")
    }

    private fun clearLoading(menu: String) {
        if (_loadingMenu.value == menu) _loadingMenu.value = null
        MenuLogger.nav("loading_end menu=$menu")
    }

    private fun setMessage(message: String) {
        _statusMessage.value = message
        MenuLogger.error("ui_message=$message")
    }

    fun clearMessage() {
        _statusMessage.value = null
    }

    fun showMessage(message: String) {
        setMessage(message)
    }
}
