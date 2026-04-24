package com.airbersih.mobile.ui.screens

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.DropdownMenuItem
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.ExposedDropdownMenuBox
import androidx.compose.material3.ExposedDropdownMenuDefaults
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
        modifier = Modifier.fillMaxSize().padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        item {
            MenuStatusBanner(vm)
            OutlinedTextField(judul, { judul = it }, label = { Text("Judul") }, modifier = Modifier.fillMaxWidth())
            OutlinedTextField(deskripsi, { deskripsi = it }, label = { Text("Deskripsi") }, modifier = Modifier.fillMaxWidth())
            ExposedDropdownMenuBox(expanded = expanded, onExpandedChange = { expanded = !expanded }) {
                OutlinedTextField(
                    value = pelangganLabel,
                    onValueChange = {},
                    readOnly = true,
                    trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expanded) },
                    modifier = Modifier.menuAnchor().fillMaxWidth(),
                    label = { Text("Pelanggan") }
                )
                ExposedDropdownMenu(expanded = expanded, onDismissRequest = { expanded = false }) {
                    DropdownMenuItem(text = { Text("Tanpa pelanggan") }, onClick = {
                        selectedPelangganId = null
                        pelangganLabel = "Opsional: pilih pelanggan"
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
            OutlinedTextField(kategori, { kategori = it }, label = { Text("Jenis laporan") }, modifier = Modifier.fillMaxWidth())
            OutlinedTextField(prioritas, { prioritas = it }, label = { Text("Prioritas") }, modifier = Modifier.fillMaxWidth())
            Button(onClick = { vm.submitKeluhan(judul, deskripsi, kategori, prioritas, selectedPelangganId) }, modifier = Modifier.fillMaxWidth()) {
                Text("Kirim Keluhan")
            }
        }
        items(items = items, key = { it.id ?: it.judul.orEmpty() }) { k ->
            Card(colors = CardDefaults.cardColors(containerColor = androidx.compose.material3.MaterialTheme.colorScheme.surfaceVariant)) {
                Column(Modifier.padding(12.dp)) {
                    Text(k.judul ?: "Tanpa judul")
                    Text("${k.jenisLaporan ?: "-"} | ${k.prioritas ?: "-"} | ${k.statusPenanganan ?: "-"}")
                    Text("Lokasi: ${k.latitude ?: "-"}, ${k.longitude ?: "-"}")
                }
            }
        }
    }
}
