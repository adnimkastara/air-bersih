package com.airbersih.mobile.ui.screens

import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import com.airbersih.mobile.viewmodel.MainViewModel
import com.google.android.gms.maps.model.LatLng
import com.google.maps.android.compose.*

@Composable
fun MonitoringScreen(vm: MainViewModel) {
    val monitoring by vm.monitoring.collectAsState()

    LaunchedEffect(Unit) { vm.loadMonitoring() }

    val fallback = LatLng(-6.2, 106.8)
    val center = monitoring?.let { LatLng(it.defaultCenterLat, it.defaultCenterLng) } ?: fallback
    val cameraPositionState = rememberCameraPositionState {
        position = com.google.android.gms.maps.model.CameraPosition.fromLatLngZoom(center, 13f)
    }

    GoogleMap(
        modifier = Modifier.fillMaxSize(),
        cameraPositionState = cameraPositionState
    ) {
        monitoring?.pelanggan?.forEach { p ->
            if (p.latitude != null && p.longitude != null) {
                Marker(
                    state = MarkerState(position = LatLng(p.latitude, p.longitude)),
                    title = p.nama,
                    snippet = "${p.kodePelanggan} | ${p.status}"
                )
            }
        }
        monitoring?.keluhanAktif?.forEach { k ->
            if (k.latitude != null && k.longitude != null) {
                Marker(
                    state = MarkerState(position = LatLng(k.latitude, k.longitude)),
                    title = k.judul,
                    snippet = "${k.kategori} | ${k.prioritas}"
                )
            }
        }
    }
}
