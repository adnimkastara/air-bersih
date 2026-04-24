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
fun KeluhanScreen(vm: MainViewModel) {
    var judul by remember { mutableStateOf("") }
    var deskripsi by remember { mutableStateOf("") }
    var kategori by remember { mutableStateOf("gangguan") }
    var prioritas by remember { mutableStateOf("sedang") }
    var expanded by remember { mutableStateOf(false) }
    var selectedPelangganId by remember { mutableStateOf<Long?>(null) }
    var pelangganLabel by remember { mutableStateOf("Umum / Tanpa Pelanggan") }

    val items by vm.keluhan.collectAsState()
    val pelanggan by vm.pelanggan.collectAsState()
    val loading by vm.loadingMenu.collectAsState()

    LaunchedEffect(Unit) { vm.loadKeluhan(); vm.loadPelanggan() }

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
                "Laporan & Keluhan",
                style = MaterialTheme.typography.headlineMedium,
                fontWeight = FontWeight.ExtraBold,
                color = MaterialTheme.colorScheme.primary
            )
            Text(
                "Sampaikan kendala teknis atau laporan gangguan di lapangan",
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
                    Text("Buat Laporan Baru", fontWeight = FontWeight.Bold, style = MaterialTheme.typography.titleMedium)

                    OutlinedTextField(
                        value = judul,
                        onValueChange = { judul = it },
                        label = { Text("Judul Laporan") },
                        leadingIcon = { Icon(Icons.Default.Info, contentDescription = null, tint = MaterialTheme.colorScheme.primary) },
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp),
                        colors = OutlinedTextFieldDefaults.colors(unfocusedBorderColor = MaterialTheme.colorScheme.outline.copy(alpha = 0.3f))
                    )

                    OutlinedTextField(
                        value = deskripsi,
                        onValueChange = { deskripsi = it },
                        label = { Text("Deskripsi Detail") },
                        modifier = Modifier.fillMaxWidth(),
                        minLines = 3,
                        shape = RoundedCornerShape(12.dp),
                        colors = OutlinedTextFieldDefaults.colors(unfocusedBorderColor = MaterialTheme.colorScheme.outline.copy(alpha = 0.3f))
                    )

                    ExposedDropdownMenuBox(expanded = expanded, onExpandedChange = { expanded = !expanded }) {
                        OutlinedTextField(
                            value = pelangganLabel,
                            onValueChange = {},
                            readOnly = true,
                            trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expanded) },
                            leadingIcon = { Icon(Icons.Default.Person, contentDescription = null, tint = MaterialTheme.colorScheme.primary) },
                            modifier = Modifier.menuAnchor().fillMaxWidth(),
                            label = { Text("Pelanggan Terkait") },
                            shape = RoundedCornerShape(12.dp),
                            colors = OutlinedTextFieldDefaults.colors(unfocusedBorderColor = MaterialTheme.colorScheme.outline.copy(alpha = 0.3f))
                        )
                        ExposedDropdownMenu(expanded = expanded, onDismissRequest = { expanded = false }) {
                            DropdownMenuItem(text = { Text("Umum / Tanpa Pelanggan") }, onClick = {
                                selectedPelangganId = null
                                pelangganLabel = "Umum / Tanpa Pelanggan"
                                expanded = false
                            })
                            pelanggan.forEach {
                                DropdownMenuItem(text = { Text("${it.kodePelanggan ?: "-"} - ${it.nama ?: "Tanpa nama"}") }, onClick = {
                                    selectedPelangganId = it.id
                                    pelangganLabel = "${it.kodePelanggan ?: "-"} - ${it.nama ?: "Tanpa nama"}"
                                    expanded = false
                                })
                            }
                        }
                    }

                    Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                        OutlinedTextField(
                            value = kategori,
                            onValueChange = { kategori = it },
                            label = { Text("Kategori") },
                            modifier = Modifier.weight(1f),
                            shape = RoundedCornerShape(12.dp),
                            colors = OutlinedTextFieldDefaults.colors(unfocusedBorderColor = MaterialTheme.colorScheme.outline.copy(alpha = 0.3f))
                        )
                        OutlinedTextField(
                            value = prioritas,
                            onValueChange = { prioritas = it },
                            label = { Text("Prioritas") },
                            modifier = Modifier.weight(1f),
                            shape = RoundedCornerShape(12.dp),
                            colors = OutlinedTextFieldDefaults.colors(unfocusedBorderColor = MaterialTheme.colorScheme.outline.copy(alpha = 0.3f))
                        )
                    }

                    Button(
                        onClick = {
                            vm.submitKeluhan(judul, deskripsi, kategori, prioritas, selectedPelangganId)
                            judul = ""; deskripsi = ""
                        },
                        modifier = Modifier.fillMaxWidth().height(56.dp),
                        shape = RoundedCornerShape(16.dp)
                    ) {
                        Text("Kirim Laporan Sekarang", fontWeight = FontWeight.Bold)
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
                Text("Daftar Keluhan", style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.Bold)
                if (loading == "keluhan") {
                    CircularProgressIndicator(modifier = Modifier.size(20.dp), strokeWidth = 2.dp)
                }
            }
        }

        if (items.isEmpty() && loading != "keluhan") {
            item {
                Box(Modifier.fillMaxWidth().padding(40.dp), contentAlignment = Alignment.Center) {
                    Text("Tidak ada keluhan aktif", color = MaterialTheme.colorScheme.secondary)
                }
            }
        }

        items(items = items, key = { it.id ?: (it.judul.orEmpty() + it.latitude + Math.random()) }) { k ->
            KeluhanItemCard(k)
        }

        item {
            Spacer(modifier = Modifier.height(32.dp))
        }
    }
}

@Composable
private fun KeluhanItemCard(k: com.airbersih.mobile.model.Keluhan) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(20.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(defaultElevation = 1.dp)
    ) {
        Column(Modifier.padding(20.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                PrioritasBadge(k.prioritas ?: "sedang")
                StatusBadge(k.statusPenanganan ?: "baru")
            }

            Text(
                k.judul ?: "Tanpa Judul",
                style = MaterialTheme.typography.titleMedium,
                fontWeight = FontWeight.Bold
            )

            Text(
                k.deskripsi ?: "-",
                style = MaterialTheme.typography.bodyMedium,
                color = MaterialTheme.colorScheme.secondary,
                maxLines = 3
            )

            HorizontalDivider(thickness = 0.5.dp, color = MaterialTheme.colorScheme.outlineVariant)

            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Icon(Icons.Default.Info, contentDescription = null, modifier = Modifier.size(14.dp), tint = MaterialTheme.colorScheme.primary)
                    Spacer(Modifier.width(4.dp))
                    Text(k.jenisLaporan?.uppercase() ?: "-", style = MaterialTheme.typography.labelSmall, fontWeight = FontWeight.Bold)
                }

                if (k.latitude != null) {
                    Icon(Icons.Default.LocationOn, contentDescription = null, modifier = Modifier.size(14.dp), tint = MaterialTheme.colorScheme.primary)
                }
            }
        }
    }
}

@Composable
private fun StatusBadge(status: String) {
    val (bgColor, textColor) = when (status.lowercase()) {
        "baru" -> Color(0xFFDBEAFE) to Color(0xFF1E40AF)
        "diproses" -> Color(0xFFFEF9C3) to Color(0xFF854D0E)
        "selesai" -> Color(0xFFDCFCE7) to Color(0xFF166534)
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

@Composable
private fun PrioritasBadge(prioritas: String) {
    val color = when (prioritas.lowercase()) {
        "tinggi" -> Color(0xFFEF4444)
        "sedang" -> Color(0xFFF59E0B)
        "rendah" -> Color(0xFF10B981)
        else -> MaterialTheme.colorScheme.secondary
    }

    Row(verticalAlignment = Alignment.CenterVertically) {
        Box(modifier = Modifier.size(8.dp).clip(androidx.compose.foundation.shape.CircleShape).background(color))
        Spacer(Modifier.width(6.dp))
        Text(prioritas.uppercase(), style = MaterialTheme.typography.labelSmall, fontWeight = FontWeight.Bold, color = color)
    }
}
