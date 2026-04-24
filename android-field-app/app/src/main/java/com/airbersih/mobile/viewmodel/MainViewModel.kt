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

    private val _exitAppEvent = MutableStateFlow(false)
    val exitAppEvent: StateFlow<Boolean> = _exitAppEvent

    private val _me = MutableStateFlow<User?>(null)
    val me: StateFlow<User?> = _me

    private val _customerAutoFill = MutableStateFlow(CustomerAutoFill())
    val customerAutoFill: StateFlow<CustomerAutoFill> = _customerAutoFill

    private val _customerCoordinates = MutableStateFlow<Pair<Double?, Double?>>(null to null)
    val customerCoordinates: StateFlow<Pair<Double?, Double?>> = _customerCoordinates

    private val _dashboard = MutableStateFlow(DashboardSummary())
    val dashboard: StateFlow<DashboardSummary> = _dashboard

    private val _pelanggan = MutableStateFlow<List<Pelanggan>>(emptyList())
    val pelanggan: StateFlow<List<Pelanggan>> = _pelanggan

    private val _tagihan = MutableStateFlow<List<Tagihan>>(emptyList())
    val tagihan: StateFlow<List<Tagihan>> = _tagihan
    private val _tagihanDetail = MutableStateFlow<TagihanDetailResponse?>(null)
    val tagihanDetail: StateFlow<TagihanDetailResponse?> = _tagihanDetail
    private val _meterRecords = MutableStateFlow<List<MeterRecordItem>>(emptyList())
    val meterRecords: StateFlow<List<MeterRecordItem>> = _meterRecords
    private val _pembayaranList = MutableStateFlow<List<Pembayaran>>(emptyList())
    val pembayaranList: StateFlow<List<Pembayaran>> = _pembayaranList

    private val _keluhan = MutableStateFlow<List<Keluhan>>(emptyList())
    val keluhan: StateFlow<List<Keluhan>> = _keluhan

    private val _monitoring = MutableStateFlow<MonitoringMapResponse?>(null)
    val monitoring: StateFlow<MonitoringMapResponse?> = _monitoring

    private val _monitoringError = MutableStateFlow<String?>(null)
    val monitoringError: StateFlow<String?> = _monitoringError

    private val _statusMessage = MutableStateFlow<String?>(null)
    val statusMessage: StateFlow<String?> = _statusMessage

    private val _loadingMenu = MutableStateFlow<String?>(null)
    val loadingMenu: StateFlow<String?> = _loadingMenu

    init {
        safeLaunch("initSession") {
            cachedToken = tokenManager.tokenFlow.first()
            _isLoggedIn.value = !cachedToken.isNullOrBlank()
            MenuLogger.session("session_restored loggedIn=${_isLoggedIn.value}")
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
                    applyCustomerAutoFill(_me.value)
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
            MenuLogger.session("logout_clicked")
            resetSessionData()
            _exitAppEvent.value = true
        }
    }

    fun onExitAppHandled() {
        _exitAppEvent.value = false
    }

    fun refreshInitialData() {
        safeLaunch("refreshInitialData") {
            MenuLogger.feature("dashboard bootstrap start")
            repository.me().consume(
                onSuccess = {
                    _me.value = it
                    applyCustomerAutoFill(it)
                },
                onError = { setMessage(it.message) }
            )
            repository.dashboard().consume(
                onSuccess = { _dashboard.value = it },
                onError = { setMessage(it.message) }
            )
            loadPelanggan()
            loadMeterRecords()
            loadPembayaranList()
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
                is ResultState.Error -> {
                    MenuLogger.error("menu=pelanggan load_failed message=${result.message}")
                    if (!handleUnauthorized(result)) setMessage(result.message)
                }
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

    fun loadTagihanDetail(tagihanId: Long) {
        safeLaunch("loadTagihanDetail") {
            when (val result = repository.tagihanDetail(tagihanId)) {
                is ResultState.Success -> _tagihanDetail.value = result.data
                is ResultState.Error -> if (!handleUnauthorized(result)) setMessage(result.message)
                ResultState.Loading -> Unit
            }
        }
    }

    fun publishTagihan(tagihanId: Long) {
        safeLaunch("publishTagihan") {
            when (val result = repository.publishTagihan(tagihanId)) {
                is ResultState.Success -> {
                    setMessage(result.data.message ?: "Tagihan diterbitkan.")
                    MenuLogger.api("form_submit_success menu=tagihan action=publish id=$tagihanId")
                    loadTagihan(null)
                }
                is ResultState.Error -> {
                    MenuLogger.error("form_submit_failed menu=tagihan action=publish id=$tagihanId message=${result.message}")
                    if (!handleUnauthorized(result)) setMessage(result.message)
                }
                ResultState.Loading -> Unit
            }
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
                is ResultState.Success -> {
                    MenuLogger.api("form_submit_success menu=meter pelanggan_id=$pelangganId")
                    setMessage(result.data.message ?: "Meter record tersimpan.")
                    loadMeterRecords()
                }
                is ResultState.Error -> {
                    MenuLogger.error("form_submit_failed menu=meter pelanggan_id=$pelangganId message=${result.message}")
                    if (!handleUnauthorized(result)) setMessage(result.message)
                }
                ResultState.Loading -> Unit
            }
            clearLoading("meter")
        }
    }

    fun loadMeterRecords() {
        safeLaunch("loadMeterRecords") {
            when (val result = repository.meterRecords()) {
                is ResultState.Success -> _meterRecords.value = result.data
                is ResultState.Error -> if (!handleUnauthorized(result)) setMessage(result.message)
                ResultState.Loading -> Unit
            }
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
                is ResultState.Success -> {
                    MenuLogger.api("form_submit_success menu=pembayaran tagihan_id=$tagihanId")
                    setMessage(result.data.message ?: "Pembayaran tersimpan.")
                    loadTagihan(null)
                    loadPembayaranList()
                }
                is ResultState.Error -> {
                    MenuLogger.error("form_submit_failed menu=pembayaran tagihan_id=$tagihanId message=${result.message}")
                    if (!handleUnauthorized(result)) setMessage(result.message)
                }
                ResultState.Loading -> Unit
            }
            clearLoading("pembayaran")
        }
    }

    fun loadPembayaranList() {
        safeLaunch("loadPembayaranList") {
            when (val result = repository.pembayaranList()) {
                is ResultState.Success -> _pembayaranList.value = result.data
                is ResultState.Error -> if (!handleUnauthorized(result)) {
                    MenuLogger.error("endpoint_not_available menu=pembayaran reason=${result.message}")
                    setMessage(result.message)
                }
                ResultState.Loading -> Unit
            }
        }
    }

    fun submitKeluhan(judul: String, deskripsi: String, kategori: String, prioritas: String, pelangganId: Long? = null) {
        safeLaunch("submitKeluhan") {
            if (judul.isBlank() || deskripsi.isBlank()) {
                setMessage("Judul dan deskripsi keluhan wajib diisi.")
                return@safeLaunch
            }
            setLoading("keluhan")
            val loc = locationHelper.getCurrentLocationOrNull()
            val request = KeluhanRequest(
                judul = judul,
                deskripsi = deskripsi,
                jenisLaporan = kategori,
                prioritas = prioritas,
                pelangganId = pelangganId,
                latitude = loc?.latitude,
                longitude = loc?.longitude,
                pelapor = _me.value?.name ?: "Petugas Lapangan",
                noHp = _me.value?.noHp?.ifBlank { null } ?: "0000"
            )
            when (val result = repository.createKeluhan(request)) {
                is ResultState.Success -> {
                    MenuLogger.api("form_submit_success menu=keluhan")
                    setMessage(result.data.message ?: "Keluhan tersimpan.")
                    loadKeluhan()
                }
                is ResultState.Error -> {
                    MenuLogger.keluhanError("KELUHAN_ERROR code=${result.code} message=${result.message}")
                    if (!handleUnauthorized(result)) setMessage(result.message)
                }
                ResultState.Loading -> Unit
            }
            clearLoading("keluhan")
        }
    }

    fun captureCustomerLocation() {
        safeLaunch("captureCustomerLocation") {
            val loc = locationHelper.getCurrentLocationOrNull()
            _customerCoordinates.value = loc?.latitude to loc?.longitude
            MenuLogger.customerForm("gps_captured lat=${loc?.latitude ?: "-"} lng=${loc?.longitude ?: "-"}")
            if (loc == null) {
                setMessage("GPS belum tersedia. Pastikan lokasi aktif.")
            }
        }
    }

    fun createPelanggan(request: PelangganCreateRequest) {
        safeLaunch("createPelanggan") {
            val autoFill = _customerAutoFill.value
            val coords = _customerCoordinates.value
            val payload = request.copy(
                desaId = request.desaId ?: autoFill.desaId,
                kecamatanId = request.kecamatanId ?: autoFill.kecamatanId,
                assignedPetugasId = request.assignedPetugasId ?: autoFill.petugasId,
                latitude = request.latitude ?: coords.first,
                longitude = request.longitude ?: coords.second
            )
            MenuLogger.customerForm("submit name=${payload.name} desa_id=${payload.desaId} kecamatan_id=${payload.kecamatanId} petugas_id=${payload.assignedPetugasId} lat=${payload.latitude ?: "-"} lng=${payload.longitude ?: "-"}")
            when (val result = repository.createPelanggan(payload)) {
                is ResultState.Success -> {
                    MenuLogger.api("form_submit_success menu=pelanggan action=create")
                    setMessage("Pelanggan ${result.data.nama ?: payload.name} berhasil ditambahkan.")
                    loadPelanggan()
                }
                is ResultState.Error -> {
                    MenuLogger.error("form_submit_failed menu=pelanggan action=create message=${result.message}")
                    if (!handleUnauthorized(result)) setMessage(result.message)
                }
                ResultState.Loading -> Unit
            }
        }
    }

    fun generateTagihan(period: String) {
        safeLaunch("generateTagihan") {
            if (!Regex("^\\d{4}-\\d{2}$").matches(period)) {
                setMessage("Format periode harus YYYY-MM, contoh 2026-04.")
                return@safeLaunch
            }
            setLoading("tagihan")
            when (val result = repository.generateTagihan(period)) {
                is ResultState.Success -> {
                    MenuLogger.api("form_submit_success menu=tagihan action=generate period=$period")
                    setMessage(result.data.message ?: "Tagihan berhasil digenerate.")
                    loadTagihan(null)
                }
                is ResultState.Error -> {
                    MenuLogger.error("form_submit_failed menu=tagihan action=generate period=$period message=${result.message}")
                    if (!handleUnauthorized(result)) setMessage(result.message)
                }
                ResultState.Loading -> Unit
            }
            clearLoading("tagihan")
        }
    }

    fun updatePassword(currentPassword: String, newPassword: String, confirmPassword: String) {
        safeLaunch("updatePassword") {
            if (newPassword.length < 8) {
                setMessage("Password baru minimal 8 karakter.")
                return@safeLaunch
            }
            when (
                val result = repository.updatePassword(
                    PasswordUpdateRequest(
                        currentPassword = currentPassword,
                        password = newPassword,
                        passwordConfirmation = confirmPassword
                    )
                )
            ) {
                is ResultState.Success -> {
                    MenuLogger.api("form_submit_success menu=profile action=update_password")
                    setMessage(result.data.message ?: "Password diperbarui.")
                }
                is ResultState.Error -> {
                    MenuLogger.error("form_submit_failed menu=profile action=update_password message=${result.message}")
                    if (!handleUnauthorized(result)) setMessage(result.message)
                }
                ResultState.Loading -> Unit
            }
        }
    }

    fun loadMonitoring() {
        safeLaunch("loadMonitoring") {
            setLoading("monitoring")
            _monitoringError.value = null
            MenuLogger.mapFlow("MAP_INIT loadMonitoring_called")
            val loc = locationHelper.getCurrentLocationOrNull()
            when (val result = repository.monitoringMap(loc?.latitude, loc?.longitude)) {
                is ResultState.Success -> {
                    _monitoring.value = result.data
                    val customerCount = result.data.pelanggan.size
                    val issueCount = result.data.keluhanAktif.size
                    val total = customerCount + issueCount
                    MenuLogger.mapFlow("MAP_DATA_RECEIVED pelanggan=$customerCount keluhan=$issueCount")
                    MenuLogger.mapMarkers("MAP_MARKERS pelanggan=$customerCount keluhan=$issueCount total=$total")
                    if (total == 0) {
                        _monitoringError.value = "Tidak ada data monitoring"
                        setMessage("Tidak ada data monitoring")
                    } else {
                        MenuLogger.mapFlow("MAP_LOAD_SUCCESS markers=$total")
                    }
                }
                is ResultState.Error -> {
                    _monitoring.value = MonitoringMapResponse()
                    _monitoringError.value = result.message
                    MenuLogger.mapFlow("MAP_LOAD_ERROR message=${result.message}")
                    if (!handleUnauthorized(result)) setMessage(result.message)
                }
                ResultState.Loading -> Unit
            }
            clearLoading("monitoring")
        }
    }

    private fun applyCustomerAutoFill(user: User?) {
        val resolvedKecamatanId = user?.kecamatanId ?: user?.kecamatan?.id ?: user?.desa?.kecamatanId
        val resolvedKecamatanName = user?.kecamatan?.name ?: "Kecamatan #${resolvedKecamatanId ?: "-"}"
        _customerAutoFill.value = CustomerAutoFill(
            petugasId = user?.id,
            petugasName = user?.name ?: "-",
            desaId = user?.desaId ?: user?.desa?.id,
            desaName = user?.desa?.name ?: "-",
            kecamatanId = resolvedKecamatanId,
            kecamatanName = resolvedKecamatanName
        )
        MenuLogger.customerForm("autofill petugas=${_customerAutoFill.value.petugasName} desa=${_customerAutoFill.value.desaName} kecamatan=${_customerAutoFill.value.kecamatanName}")
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
        _monitoringError.value = null
        _tagihanDetail.value = null
        _meterRecords.value = emptyList()
        _pembayaranList.value = emptyList()
        _customerAutoFill.value = CustomerAutoFill()
        _customerCoordinates.value = null to null
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
        MenuLogger.ui("ui_message=$message")
    }

    fun clearMessage() {
        _statusMessage.value = null
    }

    fun showMessage(message: String) {
        setMessage(message)
    }
}
