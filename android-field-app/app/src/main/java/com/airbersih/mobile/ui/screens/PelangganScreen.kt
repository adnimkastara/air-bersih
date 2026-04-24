package com.airbersih.mobile.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.LocationOn
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.Search
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
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
import androidx.compose.ui.Alignment
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

    LazyColumn(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .padding(horizontal = 16.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        item {
            Spacer(modifier = Modifier.height(16.dp))
            MenuStatusBanner(vm)
        }

        item {
            Text(
                "Daftar & Tambah Pelanggan",
                style = MaterialTheme.typography.headlineSmall,
                fontWeight = FontWeight.Bold,
                color = MaterialTheme.colorScheme.primary
            )
        }

        item {
            OutlinedTextField(
                value = query,
                onValueChange = {
                    query = it
                    vm.loadPelanggan(query = it)
                },
                label = { Text("Cari nama atau kode pelanggan") },
                modifier = Modifier.fillMaxWidth(),
                leadingIcon = { Icon(Icons.Default.Search, contentDescription = null) },
                shape = RoundedCornerShape(12.dp)
            )
        }

        item {
            Card(
                shape = RoundedCornerShape(16.dp),
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceVariant)
            ) {
                Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                    Text("Data Otomatis (GPS & Area)", fontWeight = FontWeight.Bold, style = MaterialTheme.typography.titleMedium)
                    HorizontalDivider(modifier = Modifier.padding(vertical = 4.dp))
                    Text("Petugas: ${autoFill.petugasName}")
                    Text("Desa: ${autoFill.desaName}")
                    Text("Kecamatan: ${autoFill.kecamatanName}")
                    Row(
                        modifier = Modifier.padding(top = 4.dp),
                        horizontalArrangement = Arrangement.spacedBy(8.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Icon(Icons.Default.LocationOn, contentDescription = null, tint = MaterialTheme.colorScheme.primary)
                        Text(
                            text = if (coordinates.first != null) "${coordinates.first}, ${coordinates.second}" else "Koordinat belum diambil",
                            style = MaterialTheme.typography.bodySmall
                        )
                    }
                    Button(
                        onClick = { vm.captureCustomerLocation() },
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(8.dp)
                    ) {
                        Icon(Icons.Default.LocationOn, contentDescription = null)
                        Spacer(Modifier.padding(4.dp))
                        Text("Ambil Lokasi GPS")
                    }
                }
            }
        }

        item {
            Text("Formulir Pelanggan Baru", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.Bold)
        }

        item {
            Card(
                shape = RoundedCornerShape(16.dp),
                elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
            ) {
                Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                    OutlinedTextField(nama, { nama = it }, label = { Text("Nama Lengkap") }, leadingIcon = { Icon(Icons.Default.Person, contentDescription = null) }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp))
                    OutlinedTextField(alamat, { alamat = it }, label = { Text("Alamat (RT/RW)") }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp))
                    OutlinedTextField(dusun, { dusun = it }, label = { Text("Dusun") }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp))
                    OutlinedTextField(nomorMeter, { nomorMeter = it }, label = { Text("Nomor Seri Meter") }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp))
                    OutlinedTextField(jenis, { jenis = it }, label = { Text("Jenis (rumah_tangga/bisnis)") }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp))
                    OutlinedTextField(phone, { phone = it }, label = { Text("No. HP / WhatsApp") }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp))
                    Button(
                        onClick = {
                            if (nama.isBlank() || alamat.isBlank() || dusun.isBlank() || nomorMeter.isBlank()) {
                                vm.showMessage("Mohon lengkapi semua field wajib.")
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
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp)
                    ) {
                        Icon(Icons.Default.Add, contentDescription = null)
                        Spacer(Modifier.padding(4.dp))
                        Text("Simpan Pelanggan")
                    }
                }
            }
        }

        item {
            Text("Data Pelanggan Terdaftar", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.Bold, modifier = Modifier.padding(top = 8.dp))
        }

        items(items = pelanggan, key = { it.id ?: it.kodePelanggan.orEmpty() }) { item ->
            PelangganItem(item)
        }

        item {
            Spacer(modifier = Modifier.height(24.dp))
        }
    }
}

@Composable
private fun PelangganItem(item: Pelanggan) {
    Card(
        Modifier
            .fillMaxWidth()
            .clickable { },
        shape = RoundedCornerShape(12.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(defaultElevation = 1.dp)
    ) {
        Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(4.dp)) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(Icons.Default.Person, contentDescription = null, tint = MaterialTheme.colorScheme.primary, modifier = Modifier.padding(end = 8.dp))
                Text(
                    "${item.kodePelanggan ?: "-"} | ${item.nama ?: "Tanpa nama"}",
                    fontWeight = FontWeight.Bold,
                    style = MaterialTheme.typography.bodyLarge
                )
            }
            Text(item.alamat ?: "Alamat belum tersedia", style = MaterialTheme.typography.bodyMedium)
            HorizontalDivider(modifier = Modifier.padding(vertical = 4.dp), thickness = 0.5.dp, color = MaterialTheme.colorScheme.outlineVariant)
            Row(horizontalArrangement = Arrangement.spacedBy(16.dp)) {
                Column(Modifier.weight(1f)) {
                    Text("Desa", style = MaterialTheme.typography.labelSmall)
                    Text(item.desaName ?: "-", style = MaterialTheme.typography.bodySmall)
                }
                Column(Modifier.weight(1f)) {
                    Text("Status", style = MaterialTheme.typography.labelSmall)
                    Text(item.status?.uppercase() ?: "-", style = MaterialTheme.typography.bodySmall, fontWeight = FontWeight.SemiBold, color = MaterialTheme.colorScheme.primary)
                }
            }
        }
    }
}
