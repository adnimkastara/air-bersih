package com.airbersih.mobile.ui.screens

import android.content.pm.PackageManager
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.airbersih.mobile.viewmodel.MainViewModel
import com.airbersih.mobile.utils.MenuLogger
import com.google.android.gms.maps.model.LatLng
import com.google.maps.android.compose.GoogleMap
import com.google.maps.android.compose.Marker
import com.google.maps.android.compose.MarkerState
import com.google.maps.android.compose.rememberCameraPositionState

@Composable
fun MonitoringScreen(vm: MainViewModel) {
    val context = LocalContext.current
    val monitoring by vm.monitoring.collectAsState()

    LaunchedEffect(Unit) { vm.loadMonitoring() }

    val mapsApiKey = runCatching {
        val appInfo = context.packageManager.getApplicationInfo(context.packageName, PackageManager.GET_META_DATA)
        appInfo.metaData?.getString("com.google.android.geo.API_KEY").orEmpty()
    }.getOrDefault("")
    val isMapConfigValid = mapsApiKey.isNotBlank() && mapsApiKey != "YOUR_GOOGLE_MAPS_API_KEY"

    if (!isMapConfigValid) {
        MenuLogger.error("feature_not_implemented menu=monitoring reason=google_maps_api_key_missing")
        Column(modifier = Modifier.fillMaxSize().padding(16.dp)) {
            MenuStatusBanner(vm)
            Text("Google Maps belum dikonfigurasi. Peta dinonaktifkan agar aplikasi tidak crash.")
            Text(
                "Tetap aman: data monitoring API masih dapat dimuat.",
                style = MaterialTheme.typography.bodyMedium
            )
        }
        return
    }

    val fallback = monitoring?.fallbackCenter?.let {
        LatLng(it.latitude ?: -6.2, it.longitude ?: 106.8)
    } ?: LatLng(-6.2, 106.8)

    val cameraPositionState = rememberCameraPositionState {
        position = com.google.android.gms.maps.model.CameraPosition.fromLatLngZoom(fallback, 13f)
    }

    GoogleMap(
        modifier = Modifier.fillMaxSize(),
        cameraPositionState = cameraPositionState
    ) {
        monitoring?.pelanggan?.forEach { p ->
            if (p.latitude != null && p.longitude != null) {
                Marker(
                    state = MarkerState(position = LatLng(p.latitude, p.longitude)),
                    title = p.nama ?: "Pelanggan",
                    snippet = "${p.kodePelanggan ?: "-"} | ${p.status ?: "-"}"
                )
            }
        }
        monitoring?.keluhanAktif?.forEach { k ->
            if (k.latitude != null && k.longitude != null) {
                Marker(
                    state = MarkerState(position = LatLng(k.latitude, k.longitude)),
                    title = k.judul ?: "Keluhan",
                    snippet = "${k.jenisLaporan ?: "-"} | ${k.prioritas ?: "-"}"
                )
            }
        }
    }
}
