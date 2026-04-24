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
import androidx.compose.material.icons.filled.Edit
import androidx.compose.material.icons.filled.Info
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

import androidx.compose.material.icons.filled.Info
import androidx.compose.material.icons.filled.Person

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
                "Pencatatan Meter Air",
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
                    Text("Input Angka Meter", fontWeight = FontWeight.Bold)

                    ExposedDropdownMenuBox(expanded = expanded, onExpandedChange = { expanded = !expanded }) {
                        OutlinedTextField(
                            value = selectedName,
                            onValueChange = {},
                            readOnly = true,
                            trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expanded) },
                            leadingIcon = { Icon(Icons.Default.Person, contentDescription = null) },
                            modifier = Modifier.menuAnchor().fillMaxWidth(),
                            label = { Text("Pelanggan") },
                            shape = RoundedCornerShape(12.dp)
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
                        leadingIcon = { Icon(Icons.Default.Info, contentDescription = null) },
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp),
                        prefix = { Text("m³ ", fontWeight = FontWeight.Bold) }
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
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp)
                    ) {
                        Icon(Icons.Default.Edit, contentDescription = null)
                        Spacer(Modifier.padding(4.dp))
                        Text("Simpan Pencatatan")
                    }
                }
            }
        }

        item {
            Row(verticalAlignment = Alignment.CenterVertically, modifier = Modifier.padding(top = 8.dp)) {
                Icon(Icons.Default.Info, contentDescription = null, tint = MaterialTheme.colorScheme.primary)
                Spacer(Modifier.padding(4.dp))
                Text("Riwayat Pencatatan", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.Bold)
            }
        }

        items(records.take(20), key = { it.id ?: (it.recordedAt.orEmpty() + it.pelanggan?.id) }) { item ->
            Card(
                modifier = Modifier.fillMaxWidth(),
                shape = RoundedCornerShape(12.dp),
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                elevation = CardDefaults.cardElevation(defaultElevation = 1.dp)
            ) {
                Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(4.dp)) {
                    Text(item.pelanggan?.nama ?: "Tanpa Nama", fontWeight = FontWeight.Bold, style = MaterialTheme.typography.bodyLarge)
                    Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Text("Bulan Lalu: ${item.meterPreviousMonth ?: 0} m³", style = MaterialTheme.typography.bodySmall)
                        Text("Bulan Ini: ${item.meterCurrentMonth ?: 0} m³", style = MaterialTheme.typography.bodySmall, fontWeight = FontWeight.SemiBold, color = MaterialTheme.colorScheme.primary)
                    }
                    HorizontalDivider(modifier = Modifier.padding(vertical = 4.dp), thickness = 0.5.dp)
                    Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Text(item.recordedAt ?: "-", style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.secondary)
                        Text(item.verificationStatus?.uppercase() ?: "PENDING", style = MaterialTheme.typography.labelSmall, fontWeight = FontWeight.Bold)
                    }
                }
            }
        }

        item {
            Spacer(modifier = Modifier.height(24.dp))
        }
    }
}
