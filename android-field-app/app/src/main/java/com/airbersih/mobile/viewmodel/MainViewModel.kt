package com.airbersih.mobile.viewmodel

import android.app.Application
import androidx.lifecycle.AndroidViewModel
import androidx.lifecycle.viewModelScope
import com.airbersih.mobile.data.TokenManager
import com.airbersih.mobile.model.*
import com.airbersih.mobile.network.NetworkModule
import com.airbersih.mobile.repository.MainRepository
import com.airbersih.mobile.repository.ResultState
import com.airbersih.mobile.utils.LocationHelper
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch
import java.time.LocalDate

class MainViewModel(app: Application) : AndroidViewModel(app) {

    private val tokenManager = TokenManager(app)
    private val api = NetworkModule.apiService { cachedToken }
    private val repository = MainRepository(api, tokenManager)
    private val locationHelper = LocationHelper(app)

    private var cachedToken: String? = null

    private val _isLoggedIn = MutableStateFlow(false)
    val isLoggedIn: StateFlow<Boolean> = _isLoggedIn

    private val _me = MutableStateFlow<User?>(null)
    val me: StateFlow<User?> = _me

    private val _dashboard = MutableStateFlow<DashboardSummary?>(null)
    val dashboard: StateFlow<DashboardSummary?> = _dashboard

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
        viewModelScope.launch {
            cachedToken = tokenManager.tokenFlow.first()
            _isLoggedIn.value = !cachedToken.isNullOrBlank()
            if (_isLoggedIn.value) refreshInitialData()
        }
    }

    fun login(email: String, password: String) {
        viewModelScope.launch {
            when (val result = repository.login(email, password)) {
                is ResultState.Success -> {
                    cachedToken = result.data.data.accessToken
                    _isLoggedIn.value = true
                    _me.value = result.data.data.user
                    refreshInitialData()
                }
                is ResultState.Error -> if (!handleUnauthorized(result)) _statusMessage.value = result.message
                ResultState.Loading -> Unit
            }
        }
    }

    fun logout() {
        viewModelScope.launch {
            repository.logout()
            cachedToken = null
            _isLoggedIn.value = false
            _me.value = null
        }
    }

    fun refreshInitialData() {
        viewModelScope.launch {
            repository.me().let {
                when (it) {
                    is ResultState.Success -> _me.value = it.data
                    is ResultState.Error -> handleUnauthorized(it)
                    ResultState.Loading -> Unit
                }
            }
            repository.dashboard().let {
                when (it) {
                    is ResultState.Success -> _dashboard.value = it.data
                    is ResultState.Error -> handleUnauthorized(it)
                    ResultState.Loading -> Unit
                }
            }
            loadPelanggan()
            loadKeluhan()
        }
    }

    fun loadPelanggan(query: String? = null, desa: String? = null) {
        viewModelScope.launch {
            when (val result = repository.pelanggan(query, desa)) {
                is ResultState.Success -> _pelanggan.value = result.data
                is ResultState.Error -> if (!handleUnauthorized(result)) _statusMessage.value = result.message
                ResultState.Loading -> Unit
            }
        }
    }

    fun loadTagihan(pelangganId: Long?) {
        viewModelScope.launch {
            when (val result = repository.tagihan(pelangganId)) {
                is ResultState.Success -> _tagihan.value = result.data
                is ResultState.Error -> if (!handleUnauthorized(result)) _statusMessage.value = result.message
                ResultState.Loading -> Unit
            }
        }
    }

    fun loadKeluhan() {
        viewModelScope.launch {
            when (val result = repository.keluhan()) {
                is ResultState.Success -> _keluhan.value = result.data
                is ResultState.Error -> if (!handleUnauthorized(result)) _statusMessage.value = result.message
                ResultState.Loading -> Unit
            }
        }
    }

    fun submitMeter(pelangganId: Long, angka: Int, tanggal: String = LocalDate.now().toString()) {
        viewModelScope.launch {
            val loc = locationHelper.getCurrentLocationOrNull()
            val request = MeterRecordRequest(
                pelangganId = pelangganId,
                angkaMeter = angka,
                tanggalCatat = tanggal,
                latitude = loc?.latitude,
                longitude = loc?.longitude
            )
            when (val result = repository.createMeter(request)) {
                is ResultState.Success -> _statusMessage.value = result.data.message
                is ResultState.Error -> if (!handleUnauthorized(result)) _statusMessage.value = result.message
                ResultState.Loading -> Unit
            }
        }
    }

    fun submitPembayaran(tagihanId: Long, nominal: Long, metode: String, tanggal: String, catatan: String) {
        viewModelScope.launch {
            when (val result = repository.createPembayaran(
                PembayaranRequest(tagihanId, nominal, metode, tanggal, catatan)
            )) {
                is ResultState.Success -> _statusMessage.value = result.data.message
                is ResultState.Error -> if (!handleUnauthorized(result)) _statusMessage.value = result.message
                ResultState.Loading -> Unit
            }
        }
    }

    fun submitKeluhan(judul: String, deskripsi: String, kategori: String, prioritas: String) {
        viewModelScope.launch {
            val loc = locationHelper.getCurrentLocationOrNull()
            when (val result = repository.createKeluhan(
                KeluhanRequest(
                    judul = judul,
                    deskripsi = deskripsi,
                    kategori = kategori,
                    prioritas = prioritas,
                    latitude = loc?.latitude,
                    longitude = loc?.longitude
                )
            )) {
                is ResultState.Success -> {
                    _statusMessage.value = result.data.message
                    loadKeluhan()
                }
                is ResultState.Error -> if (!handleUnauthorized(result)) _statusMessage.value = result.message
                ResultState.Loading -> Unit
            }
        }
    }

    fun loadMonitoring() {
        viewModelScope.launch {
            when (val result = repository.monitoringMap()) {
                is ResultState.Success -> _monitoring.value = result.data
                is ResultState.Error -> if (!handleUnauthorized(result)) _statusMessage.value = result.message
                ResultState.Loading -> Unit
            }
        }
    }


    private fun handleUnauthorized(result: ResultState.Error): Boolean {
        if (result.code == 401) {
            cachedToken = null
            _isLoggedIn.value = false
            _me.value = null
            _dashboard.value = null
            _pelanggan.value = emptyList()
            _tagihan.value = emptyList()
            _keluhan.value = emptyList()
            _monitoring.value = null
            _statusMessage.value = result.message
            return true
        }
        return false
    }

    fun clearMessage() {
        _statusMessage.value = null
    }
}
