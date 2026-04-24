package com.airbersih.mobile.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
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
    val loading by vm.loadingMenu.collectAsState()

    LaunchedEffect(Unit) { vm.loadPelanggan() }

    LazyColumn(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .padding(horizontal = 20.dp),
        verticalArrangement = Arrangement.spacedBy(16.dp)
    ) {
        item {
            Spacer(modifier = Modifier.height(20.dp))
            MenuStatusBanner(vm)
        }

        item {
            Text(
                "Data Pelanggan",
                style = MaterialTheme.typography.headlineMedium,
                fontWeight = FontWeight.ExtraBold,
                color = MaterialTheme.colorScheme.primary
            )
            Text(
                "Kelola data dan lokasi pelanggan di lapangan",
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.secondary
            )
        }

        item {
            OutlinedTextField(
                value = query,
                onValueChange = {
                    query = it
                    vm.loadPelanggan(query = it)
                },
                label = { Text("Cari Nama / Kode / No. Meter") },
                modifier = Modifier.fillMaxWidth(),
                leadingIcon = { Icon(Icons.Default.Search, contentDescription = null, tint = MaterialTheme.colorScheme.primary) },
                shape = RoundedCornerShape(16.dp),
                colors = OutlinedTextFieldDefaults.colors(
                    focusedBorderColor = MaterialTheme.colorScheme.primary,
                    unfocusedContainerColor = MaterialTheme.colorScheme.surface
                ),
                singleLine = true
            )
        }

        item {
            Card(
                shape = RoundedCornerShape(24.dp),
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.5f))
            ) {
                Column(Modifier.padding(20.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Icon(Icons.Default.LocationOn, contentDescription = null, tint = MaterialTheme.colorScheme.primary)
                        Spacer(Modifier.width(8.dp))
                        Text("Area & GPS Otomatis", fontWeight = FontWeight.Bold, style = MaterialTheme.typography.titleMedium)
                    }

                    HorizontalDivider(thickness = 0.5.dp, color = MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.1f))

                    AreaInfoRow("Petugas", autoFill.petugasName)
                    AreaInfoRow("Unit Desa", autoFill.desaName)
                    AreaInfoRow("Koordinat", if (coordinates.first != null) "${coordinates.first}, ${coordinates.second}" else "Belum diambil")

                    Button(
                        onClick = { vm.captureCustomerLocation() },
                        modifier = Modifier.fillMaxWidth().height(48.dp),
                        shape = RoundedCornerShape(12.dp),
                        elevation = ButtonDefaults.buttonElevation(defaultElevation = 0.dp)
                    ) {
                        Icon(Icons.Default.Place, contentDescription = null, modifier = Modifier.size(18.dp))
                        Spacer(Modifier.width(8.dp))
                        Text("Ambil Lokasi Sekarang", fontWeight = FontWeight.Bold)
                    }
                }
            }
        }

        item {
            Text("Tambah Pelanggan Baru", style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.Bold)
        }

        item {
            Card(
                shape = RoundedCornerShape(24.dp),
                elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)
            ) {
                Column(Modifier.padding(20.dp), verticalArrangement = Arrangement.spacedBy(16.dp)) {
                    FormInputField(nama, { nama = it }, "Nama Lengkap", Icons.Default.Person)
                    FormInputField(alamat, { alamat = it }, "Alamat (RT/RW)", Icons.Default.Home)
                    FormInputField(dusun, { dusun = it }, "Dusun", Icons.Default.Info)
                    FormInputField(nomorMeter, { nomorMeter = it }, "Nomor Seri Meter", Icons.Default.Edit)
                    FormInputField(jenis, { jenis = it }, "Jenis (rumah_tangga/bisnis)", Icons.Default.Info)
                    FormInputField(phone, { phone = it }, "No. HP / WhatsApp", Icons.Default.Phone)

                    Button(
                        onClick = {
                            if (nama.isBlank() || alamat.isBlank() || dusun.isBlank() || nomorMeter.isBlank()) {
                                vm.showMessage("Mohon lengkapi semua field wajib.")
                            } else {
                                vm.createPelanggan(
                                    PelangganCreateRequest(
                                        name = nama, address = alamat, dusun = dusun, nomorMeter = nomorMeter,
                                        jenisPelanggan = jenis, phone = phone.ifBlank { null },
                                        desaId = autoFill.desaId, kecamatanId = autoFill.kecamatanId,
                                        assignedPetugasId = autoFill.petugasId,
                                        latitude = coordinates.first, longitude = coordinates.second
                                    )
                                )
                            }
                        },
                        modifier = Modifier.fillMaxWidth().height(56.dp),
                        shape = RoundedCornerShape(16.dp)
                    ) {
                        Icon(Icons.Default.Add, contentDescription = null)
                        Spacer(Modifier.width(8.dp))
                        Text("Simpan Pelanggan", fontWeight = FontWeight.Bold)
                    }
                }
            }
        }

        item {
            Row(
                modifier = Modifier.fillMaxWidth().padding(top = 8.dp),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text("Pelanggan Terdaftar", style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.Bold)
                if (loading == "pelanggan") {
                    CircularProgressIndicator(modifier = Modifier.size(20.dp), strokeWidth = 2.dp)
                }
            }
        }

        if (pelanggan.isEmpty() && loading != "pelanggan") {
            item {
                Box(Modifier.fillMaxWidth().padding(40.dp), contentAlignment = Alignment.Center) {
                    Text("Tidak ada data pelanggan", color = MaterialTheme.colorScheme.secondary)
                }
            }
        }

        items(items = pelanggan, key = { it.id ?: it.kodePelanggan.orEmpty() }) { item ->
            PelangganItemCard(item)
        }

        item {
            Spacer(modifier = Modifier.height(32.dp))
        }
    }
}

@Composable
private fun AreaInfoRow(label: String, value: String) {
    Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
        Text(label, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.secondary)
        Text(value, style = MaterialTheme.typography.bodySmall, fontWeight = FontWeight.SemiBold)
    }
}

@Composable
private fun FormInputField(value: String, onValueChange: (String) -> Unit, label: String, icon: androidx.compose.ui.graphics.vector.ImageVector) {
    OutlinedTextField(
        value = value,
        onValueChange = onValueChange,
        label = { Text(label) },
        leadingIcon = { Icon(icon, contentDescription = null, modifier = Modifier.size(20.dp), tint = MaterialTheme.colorScheme.primary) },
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(14.dp),
        singleLine = true,
        colors = OutlinedTextFieldDefaults.colors(
            unfocusedBorderColor = MaterialTheme.colorScheme.outline.copy(alpha = 0.3f)
        )
    )
}

@Composable
private fun PelangganItemCard(item: Pelanggan) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(20.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(defaultElevation = 1.dp)
    ) {
        Column(Modifier.padding(20.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.Top) {
                Column(Modifier.weight(1f)) {
                    Text(
                        item.nama ?: "Tanpa Nama",
                        fontWeight = FontWeight.Bold,
                        style = MaterialTheme.typography.titleMedium,
                        color = MaterialTheme.colorScheme.onSurface
                    )
                    Text(
                        item.kodePelanggan ?: "-",
                        style = MaterialTheme.typography.labelMedium,
                        color = MaterialTheme.colorScheme.primary,
                        fontWeight = FontWeight.Bold
                    )
                }
                StatusBadge(item.status ?: "aktif")
            }

            Text(
                item.alamat ?: "Alamat belum tersedia",
                style = MaterialTheme.typography.bodyMedium,
                color = MaterialTheme.colorScheme.secondary
            )

            HorizontalDivider(modifier = Modifier.padding(vertical = 4.dp), thickness = 0.5.dp, color = MaterialTheme.colorScheme.outlineVariant)

            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                InfoIconLabel(Icons.Default.Info, item.desaName ?: "-")
                if (item.latitude != null) {
                    InfoIconLabel(Icons.Default.CheckCircle, "Lokasi Tersimpan")
                } else {
                    InfoIconLabel(Icons.Default.Warning, "Tanpa Koordinat", color = MaterialTheme.colorScheme.error)
                }
            }
        }
    }
}

@Composable
private fun StatusBadge(status: String) {
    val containerColor = if (status == "aktif") Color(0xFFDCFCE7) else Color(0xFFFEE2E2)
    val contentColor = if (status == "aktif") Color(0xFF166534) else Color(0xFF991B1B)

    Box(
        modifier = Modifier
            .clip(RoundedCornerShape(8.dp))
            .background(containerColor)
            .padding(horizontal = 8.dp, vertical = 4.dp)
    ) {
        Text(status.uppercase(), style = MaterialTheme.typography.labelSmall, fontWeight = FontWeight.ExtraBold, color = contentColor)
    }
}

@Composable
private fun InfoIconLabel(icon: androidx.compose.ui.graphics.vector.ImageVector, label: String, color: Color = MaterialTheme.colorScheme.secondary) {
    Row(verticalAlignment = Alignment.CenterVertically) {
        Icon(icon, contentDescription = null, modifier = Modifier.size(14.dp), tint = color)
        Spacer(Modifier.width(4.dp))
        Text(label, style = MaterialTheme.typography.labelSmall, color = color)
    }
}
