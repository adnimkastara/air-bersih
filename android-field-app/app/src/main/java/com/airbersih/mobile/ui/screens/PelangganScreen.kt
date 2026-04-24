package com.airbersih.mobile.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import com.airbersih.mobile.model.Pelanggan
import com.airbersih.mobile.model.PelangganCreateRequest
import com.airbersih.mobile.viewmodel.MainViewModel

@Composable
fun PelangganScreen(vm: MainViewModel) {
    var query by remember { mutableStateOf("") }
    var nama by remember { mutableStateOf("") }
    var alamat by remember { mutableStateOf("") }
    var dusun by remember { mutableStateOf("") }
    var nomorMeter by remember { mutableStateOf("") }
    var jenis by remember { mutableStateOf("rumah_tangga") }
    var phone by remember { mutableStateOf("") }
    val pelanggan by vm.pelanggan.collectAsState()
    val autoFill by vm.customerAutoFill.collectAsState()
    val coordinates by vm.customerCoordinates.collectAsState()

    LaunchedEffect(Unit) { vm.loadPelanggan() }

    Column(
        Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(10.dp)
    ) {
        MenuStatusBanner(vm)
        OutlinedTextField(
            value = query,
            onValueChange = {
                query = it
                vm.loadPelanggan(query = it)
            },
            label = { Text("Cari pelanggan") },
            modifier = Modifier.fillMaxWidth()
        )

        Card(colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceVariant)) {
            Column(Modifier.padding(12.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
                Text("Data otomatis", fontWeight = FontWeight.SemiBold)
                Text("Petugas: ${autoFill.petugasName}")
                Text("Desa: ${autoFill.desaName} (ID: ${autoFill.desaId ?: "-"})")
                Text("Kecamatan: ${autoFill.kecamatanName} (ID: ${autoFill.kecamatanId ?: "-"})")
                Text("Koordinat: ${coordinates.first ?: "-"}, ${coordinates.second ?: "-"}")
                Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    Button(onClick = { vm.captureCustomerLocation() }) { Text("Gunakan lokasi saya") }
                }
            }
        }

        Text("Tambah Pelanggan", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.Bold)
        OutlinedTextField(nama, { nama = it }, label = { Text("Nama") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(alamat, { alamat = it }, label = { Text("Alamat") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(dusun, { dusun = it }, label = { Text("Dusun") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(nomorMeter, { nomorMeter = it }, label = { Text("Nomor meter") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(jenis, { jenis = it }, label = { Text("Jenis pelanggan") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(phone, { phone = it }, label = { Text("No HP") }, modifier = Modifier.fillMaxWidth())
        Button(
            onClick = {
                if (nama.isBlank() || alamat.isBlank() || dusun.isBlank() || nomorMeter.isBlank()) {
                    vm.showMessage("Nama, alamat, dusun, dan nomor meter wajib diisi.")
                } else {
                    vm.createPelanggan(
                        PelangganCreateRequest(
                            name = nama,
                            address = alamat,
                            dusun = dusun,
                            nomorMeter = nomorMeter,
                            jenisPelanggan = jenis,
                            phone = phone.ifBlank { null },
                            desaId = autoFill.desaId,
                            kecamatanId = autoFill.kecamatanId,
                            assignedPetugasId = autoFill.petugasId,
                            latitude = coordinates.first,
                            longitude = coordinates.second
                        )
                    )
                }
            },
            modifier = Modifier.fillMaxWidth()
        ) { Text("Tambah Pelanggan") }
        LazyColumn(verticalArrangement = Arrangement.spacedBy(8.dp)) {
            items(items = pelanggan, key = { it.id ?: it.kodePelanggan.orEmpty() }) { item -> PelangganItem(item) }
        }
    }
}

@Composable
private fun PelangganItem(item: Pelanggan) {
    Card(
        Modifier
            .fillMaxWidth()
            .clickable { },
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)
    ) {
        Column(Modifier.padding(12.dp), verticalArrangement = Arrangement.spacedBy(2.dp)) {
            Text("${item.kodePelanggan ?: "-"} - ${item.nama ?: "Tanpa nama"}", fontWeight = FontWeight.SemiBold)
            Text(item.alamat ?: "Alamat belum tersedia")
            Text("Desa: ${item.desaName ?: "-"} (ID ${item.desaId ?: "-"})")
            Text("Kecamatan: ${item.kecamatanName ?: "-"} (ID ${item.kecamatanId ?: "-"})")
            Text("Petugas: ${item.assignedPetugasName ?: "-"}")
            Text("Status ${item.status ?: "-"}")
            Text("Koordinat: ${item.latitude ?: "-"}, ${item.longitude ?: "-"}")
        }
    }
}
