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
import com.airbersih.mobile.viewmodel.MainViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MeterScreen(vm: MainViewModel) {
    val pelanggan by vm.pelanggan.collectAsState()
    val records by vm.meterRecords.collectAsState()
    val loading by vm.loadingMenu.collectAsState()

    var expanded by remember { mutableStateOf(false) }
    var selectedId by remember { mutableStateOf<Long?>(null) }
    var selectedName by remember { mutableStateOf("Pilih pelanggan") }
    var angka by remember { mutableStateOf("") }

    LaunchedEffect(Unit) { vm.loadPelanggan(); vm.loadMeterRecords() }
    val previous = records.firstOrNull { it.pelanggan?.id == selectedId }?.meterCurrentMonth ?: 0

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
                "Pencatatan Meter",
                style = MaterialTheme.typography.headlineMedium,
                fontWeight = FontWeight.ExtraBold,
                color = MaterialTheme.colorScheme.primary
            )
            Text(
                "Input penggunaan air bulanan pelanggan",
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.secondary
            )
        }

        item {
            Card(
                shape = RoundedCornerShape(24.dp),
                elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)
            ) {
                Column(Modifier.padding(20.dp), verticalArrangement = Arrangement.spacedBy(16.dp)) {
                    Text("Form Catat Meter", fontWeight = FontWeight.Bold, style = MaterialTheme.typography.titleMedium)

                    ExposedDropdownMenuBox(expanded = expanded, onExpandedChange = { expanded = !expanded }) {
                        OutlinedTextField(
                            value = selectedName,
                            onValueChange = {},
                            readOnly = true,
                            trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expanded) },
                            leadingIcon = { Icon(Icons.Default.Person, contentDescription = null, tint = MaterialTheme.colorScheme.primary) },
                            modifier = Modifier.menuAnchor().fillMaxWidth(),
                            label = { Text("Pelanggan") },
                            shape = RoundedCornerShape(12.dp),
                            colors = OutlinedTextFieldDefaults.colors(
                                unfocusedBorderColor = MaterialTheme.colorScheme.outline.copy(alpha = 0.3f)
                            )
                        )
                        ExposedDropdownMenu(expanded = expanded, onDismissRequest = { expanded = false }) {
                            if (pelanggan.isEmpty()) {
                                DropdownMenuItem(text = { Text("Tidak ada data pelanggan") }, onClick = { expanded = false })
                            }
                            pelanggan.forEach {
                                DropdownMenuItem(
                                    text = { Text("${it.kodePelanggan ?: "-"} - ${it.nama ?: "Tanpa nama"}") },
                                    onClick = {
                                        selectedId = it.id
                                        selectedName = "${it.kodePelanggan ?: "-"} - ${it.nama ?: "Tanpa nama"}"
                                        expanded = false
                                    }
                                )
                            }
                        }
                    }

                    OutlinedTextField(
                        value = angka,
                        onValueChange = { angka = it.filter { c -> c.isDigit() } },
                        label = { Text("Angka Meter Baru") },
                        placeholder = { Text("Sebelumnya: $previous") },
                        leadingIcon = { Icon(Icons.Default.Edit, contentDescription = null, tint = MaterialTheme.colorScheme.primary) },
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp),
                        prefix = { Text("m³ ", fontWeight = FontWeight.Bold) },
                        colors = OutlinedTextFieldDefaults.colors(
                            unfocusedBorderColor = MaterialTheme.colorScheme.outline.copy(alpha = 0.3f)
                        )
                    )

                    Button(
                        onClick = {
                            val id = selectedId
                            val valAngka = angka.toIntOrNull()
                            if (id != null && valAngka != null) {
                                vm.submitMeter(id, valAngka)
                                angka = ""
                            } else {
                                vm.showMessage("Pilih pelanggan dan isi angka meter yang valid.")
                            }
                        },
                        modifier = Modifier.fillMaxWidth().height(56.dp),
                        shape = RoundedCornerShape(16.dp)
                    ) {
                        Icon(Icons.Default.Check, contentDescription = null)
                        Spacer(Modifier.width(8.dp))
                        Text("Simpan Pencatatan", fontWeight = FontWeight.Bold)
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
                Text("Riwayat Pencatatan", style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.Bold)
                if (loading == "meter") {
                    CircularProgressIndicator(modifier = Modifier.size(20.dp), strokeWidth = 2.dp)
                }
            }
        }

        if (records.isEmpty() && loading != "meter") {
            item {
                Box(Modifier.fillMaxWidth().padding(40.dp), contentAlignment = Alignment.Center) {
                    Text("Belum ada riwayat pencatatan", color = MaterialTheme.colorScheme.secondary)
                }
            }
        }

        items(records.take(20), key = { it.id ?: (it.recordedAt.orEmpty() + it.pelanggan?.id) }) { item ->
            MeterRecordCard(item)
        }

        item {
            Spacer(modifier = Modifier.height(32.dp))
        }
    }
}

@Composable
private fun MeterRecordCard(item: com.airbersih.mobile.model.MeterRecordItem) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(20.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(defaultElevation = 1.dp)
    ) {
        Column(Modifier.padding(20.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                Text(
                    item.pelanggan?.nama ?: "Tanpa Nama",
                    fontWeight = FontWeight.Bold,
                    style = MaterialTheme.typography.titleMedium
                )
                VerificationBadge(item.verificationStatus ?: "pending")
            }

            Row(
                modifier = Modifier.fillMaxWidth().background(MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.3f), RoundedCornerShape(12.dp)).padding(12.dp),
                horizontalArrangement = Arrangement.SpaceAround
            ) {
                MeterValueItem("Bulan Lalu", "${item.meterPreviousMonth ?: 0} m³")
                Icon(Icons.Default.ArrowForward, contentDescription = null, tint = MaterialTheme.colorScheme.primary, modifier = Modifier.padding(top = 8.dp))
                MeterValueItem("Bulan Ini", "${item.meterCurrentMonth ?: 0} m³", isPrimary = true)
            }

            HorizontalDivider(thickness = 0.5.dp, color = MaterialTheme.colorScheme.outlineVariant)

            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Icon(Icons.Default.DateRange, contentDescription = null, modifier = Modifier.size(14.dp), tint = MaterialTheme.colorScheme.secondary)
                    Spacer(Modifier.width(4.dp))
                    Text(item.recordedAt ?: "-", style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.secondary)
                }
                Text("Terhitung: ${(item.meterCurrentMonth ?: 0) - (item.meterPreviousMonth ?: 0)} m³", style = MaterialTheme.typography.labelSmall, fontWeight = FontWeight.Bold, color = MaterialTheme.colorScheme.primary)
            }
        }
    }
}

@Composable
private fun MeterValueItem(label: String, value: String, isPrimary: Boolean = false) {
    Column(horizontalAlignment = Alignment.CenterHorizontally) {
        Text(label, style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.secondary)
        Text(value, style = MaterialTheme.typography.titleMedium, fontWeight = if (isPrimary) FontWeight.ExtraBold else FontWeight.Bold, color = if (isPrimary) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.onSurface)
    }
}

@Composable
private fun VerificationBadge(status: String) {
    val (bgColor, textColor) = when (status.lowercase()) {
        "verified" -> Color(0xFFDCFCE7) to Color(0xFF166534)
        "pending" -> Color(0xFFFEF9C3) to Color(0xFF854D0E)
        "rejected" -> Color(0xFFFEE2E2) to Color(0xFF991B1B)
        else -> MaterialTheme.colorScheme.surfaceVariant to MaterialTheme.colorScheme.onSurfaceVariant
    }

    Box(
        modifier = Modifier
            .clip(RoundedCornerShape(8.dp))
            .background(bgColor)
            .padding(horizontal = 8.dp, vertical = 4.dp)
    ) {
        Text(status.uppercase(), style = MaterialTheme.typography.labelSmall, fontWeight = FontWeight.ExtraBold, color = textColor)
    }
}
