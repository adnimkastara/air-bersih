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
import com.airbersih.mobile.viewmodel.MainViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MeterScreen(vm: MainViewModel) {
    val pelanggan by vm.pelanggan.collectAsState()
    val records by vm.meterRecords.collectAsState()
    var expanded by remember { mutableStateOf(false) }
    var selectedId by remember { mutableStateOf<Long?>(null) }
    var selectedName by remember { mutableStateOf("Pilih pelanggan") }
    var angka by remember { mutableStateOf("") }

    LaunchedEffect(Unit) { vm.loadPelanggan(); vm.loadMeterRecords() }
    val previous = records.firstOrNull { it.pelanggan?.id == selectedId }?.meterCurrentMonth ?: 0

    LazyColumn(Modifier.fillMaxSize().padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
        item {
            MenuStatusBanner(vm)
            ExposedDropdownMenuBox(expanded = expanded, onExpandedChange = { expanded = !expanded }) {
                OutlinedTextField(
                    value = selectedName,
                    onValueChange = {},
                    readOnly = true,
                    trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expanded) },
                    modifier = Modifier.menuAnchor().fillMaxWidth(),
                    label = { Text("Pilih Pelanggan") }
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
                label = { Text("Angka meter (sebelumnya: $previous)") },
                modifier = Modifier.fillMaxWidth()
            )
            Button(onClick = {
                val id = selectedId
                val valAngka = angka.toIntOrNull()
                if (id != null && valAngka != null) vm.submitMeter(id, valAngka)
                else vm.showMessage("Pilih pelanggan dan isi angka meter yang valid.")
            }, modifier = Modifier.fillMaxWidth()) {
                Text("Kirim Meter Record")
            }
            Text("Riwayat Meter", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold)
        }
        items(records.take(20), key = { it.id ?: 0 }) { item ->
            Card(colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceVariant)) {
                Column(Modifier.padding(12.dp)) {
                    Text(item.pelanggan?.nama ?: "-")
                    Text("${item.meterPreviousMonth ?: 0} -> ${item.meterCurrentMonth ?: 0}")
                    Text("${item.recordedAt ?: "-"} | ${item.verificationStatus ?: "-"}")
                }
            }
        }
    }
}
