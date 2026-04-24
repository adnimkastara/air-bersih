package com.airbersih.mobile.ui.screens

import androidx.compose.foundation.background
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
import androidx.compose.material.icons.filled.Info
import androidx.compose.material.icons.filled.Warning
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.DropdownMenuItem
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.ExposedDropdownMenuBox
import androidx.compose.material3.ExposedDropdownMenuDefaults
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
    var pelangganLabel by remember { mutableStateOf("Opsional: pilih pelanggan") }
    val items by vm.keluhan.collectAsState()
    val pelanggan by vm.pelanggan.collectAsState()

    LaunchedEffect(Unit) { vm.loadKeluhan(); vm.loadPelanggan() }

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
            Text(
                "Laporan Keluhan & Gangguan",
                style = MaterialTheme.typography.headlineSmall,
                fontWeight = FontWeight.Bold,
                color = MaterialTheme.colorScheme.primary
            )
        }

        item {
            Card(
                shape = RoundedCornerShape(16.dp),
                elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
            ) {
                Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                    Text("Form Laporan Baru", fontWeight = FontWeight.Bold)
                    OutlinedTextField(judul, { judul = it }, label = { Text("Judul Laporan") }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp))
                    OutlinedTextField(deskripsi, { deskripsi = it }, label = { Text("Deskripsi Detail") }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp), minLines = 3)

                    ExposedDropdownMenuBox(expanded = expanded, onExpandedChange = { expanded = !expanded }) {
                        OutlinedTextField(
                            value = pelangganLabel,
                            onValueChange = {},
                            readOnly = true,
                            trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expanded) },
                            modifier = Modifier.menuAnchor().fillMaxWidth(),
                            label = { Text("Pelanggan Terkait") },
                            shape = RoundedCornerShape(12.dp)
                        )
                        ExposedDropdownMenu(expanded = expanded, onDismissRequest = { expanded = false }) {
                            DropdownMenuItem(text = { Text("Tanpa pelanggan / Umum") }, onClick = {
                                selectedPelangganId = null
                                pelangganLabel = "Tanpa pelanggan / Umum"
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
                    OutlinedTextField(kategori, { kategori = it }, label = { Text("Kategori (gangguan/layanan)") }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp))
                    OutlinedTextField(prioritas, { prioritas = it }, label = { Text("Prioritas (rendah/sedang/tinggi)") }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp))
                    Button(
                        onClick = {
                            vm.submitKeluhan(judul, deskripsi, kategori, prioritas, selectedPelangganId)
                            judul = ""; deskripsi = ""
                        },
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp)
                    ) {
                        Text("Kirim Laporan")
                    }
                }
            }
        }

        item {
            Text("Daftar Keluhan Aktif", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.Bold, modifier = Modifier.padding(top = 8.dp))
        }

        items(items = items, key = { it.id ?: (it.judul.orEmpty() + it.latitude) }) { k ->
            Card(
                modifier = Modifier.fillMaxWidth(),
                shape = RoundedCornerShape(12.dp),
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                elevation = CardDefaults.cardElevation(defaultElevation = 1.dp)
            ) {
                Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(4.dp)) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Icon(
                            if (k.prioritas == "tinggi") Icons.Default.Warning else Icons.Default.Info,
                            contentDescription = null,
                            tint = if (k.prioritas == "tinggi") MaterialTheme.colorScheme.error else MaterialTheme.colorScheme.primary,
                            modifier = Modifier.padding(end = 8.dp)
                        )
                        Text(k.judul ?: "Tanpa judul", fontWeight = FontWeight.Bold, style = MaterialTheme.typography.bodyLarge)
                    }
                    Text(k.deskripsi ?: "-", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.secondary)
                    HorizontalDivider(modifier = Modifier.padding(vertical = 4.dp), thickness = 0.5.dp)
                    Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Text("Status: ${k.statusPenanganan?.uppercase() ?: "PENDING"}", style = MaterialTheme.typography.labelSmall, fontWeight = FontWeight.SemiBold)
                        Text("Kategori: ${k.jenisLaporan ?: "-"}", style = MaterialTheme.typography.labelSmall)
                    }
                }
            }
        }

        item {
            Spacer(modifier = Modifier.height(24.dp))
        }
    }
}
