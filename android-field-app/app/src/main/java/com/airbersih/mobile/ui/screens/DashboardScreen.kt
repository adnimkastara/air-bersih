package com.airbersih.mobile.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import com.airbersih.mobile.viewmodel.MainViewModel

@Composable
fun DashboardScreen(
    vm: MainViewModel,
    onOpenPelanggan: () -> Unit,
    onOpenMeter: () -> Unit,
    onOpenTagihan: () -> Unit,
    onOpenPembayaran: () -> Unit,
    onOpenKeluhan: () -> Unit,
    onOpenMonitoring: () -> Unit,
    onOpenProfile: () -> Unit,
    onLogout: () -> Unit
) {
    val me by vm.me.collectAsState()
    val summary by vm.dashboard.collectAsState()

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(10.dp)
    ) {
        Text(
            text = "SIPAM Mobile",
            style = MaterialTheme.typography.headlineSmall,
            fontWeight = FontWeight.Bold
        )
        Card(
            Modifier.fillMaxWidth(),
            colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceVariant)
        ) {
            Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(4.dp)) {
                Text("Petugas: ${me?.name ?: "-"}")
                Text("Desa: ${me?.desa?.name ?: "-"}")
                Text("Pelanggan: ${summary.jumlahPelanggan ?: 0}")
                Text("Keluhan aktif: ${summary.keluhanAktif ?: 0}")
                Text("Tagihan aktif: ${summary.tagihanAktif ?: 0}")
            }
        }
        Button(onClick = onOpenPelanggan, modifier = Modifier.fillMaxWidth()) { Text("Pelanggan") }
        Button(onClick = onOpenMeter, modifier = Modifier.fillMaxWidth()) { Text("Input Meter") }
        Button(onClick = onOpenTagihan, modifier = Modifier.fillMaxWidth()) { Text("Tagihan") }
        Button(onClick = onOpenPembayaran, modifier = Modifier.fillMaxWidth()) { Text("Pembayaran") }
        Button(onClick = onOpenKeluhan, modifier = Modifier.fillMaxWidth()) { Text("Keluhan") }
        Button(onClick = onOpenMonitoring, modifier = Modifier.fillMaxWidth()) { Text("Monitoring Peta") }
        Button(onClick = onOpenProfile, modifier = Modifier.fillMaxWidth()) { Text("Profil Saya") }
        Button(onClick = onLogout, modifier = Modifier.fillMaxWidth()) { Text("Logout") }
        MenuStatusBanner(vm)
    }
}
