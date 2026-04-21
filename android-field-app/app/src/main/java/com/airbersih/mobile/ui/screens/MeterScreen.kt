package com.airbersih.mobile.ui.screens

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Button
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
fun MeterScreen(vm: MainViewModel) {
    val pelanggan by vm.pelanggan.collectAsState()
    var expanded by remember { mutableStateOf(false) }
    var selectedId by remember { mutableStateOf<Long?>(null) }
    var selectedName by remember { mutableStateOf("Pilih pelanggan") }
    var angka by remember { mutableStateOf("") }

    LaunchedEffect(Unit) { vm.loadPelanggan() }

    Column(Modifier.fillMaxSize().padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
        MenuStatusBanner(vm)
        ExposedDropdownMenuBox(expanded = expanded, onExpandedChange = { expanded = !expanded }) {
            OutlinedTextField(
                value = selectedName,
                onValueChange = {},
                readOnly = true,
                trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expanded) },
                modifier = Modifier.menuAnchor().fillMaxWidth(),
                label = { Text("Pelanggan") }
            )
            ExposedDropdownMenu(expanded = expanded, onDismissRequest = { expanded = false }) {
                pelanggan.forEach {
                    DropdownMenuItem(
                        text = { Text("${it.kodePelanggan} - ${it.nama}") },
                        onClick = {
                            selectedId = it.id
                            selectedName = "${it.kodePelanggan} - ${it.nama}"
                            expanded = false
                        }
                    )
                }
            }
        }

        OutlinedTextField(
            value = angka,
            onValueChange = { angka = it.filter { c -> c.isDigit() } },
            label = { Text("Angka meter") },
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
    }
}
