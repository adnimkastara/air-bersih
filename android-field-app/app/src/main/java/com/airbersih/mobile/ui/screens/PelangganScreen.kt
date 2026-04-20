package com.airbersih.mobile.ui.screens

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.Card
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.airbersih.mobile.model.Pelanggan
import com.airbersih.mobile.viewmodel.MainViewModel

@Composable
fun PelangganScreen(vm: MainViewModel) {
    var query by remember { mutableStateOf("") }
    val pelanggan by vm.pelanggan.collectAsState()

    LaunchedEffect(Unit) { vm.loadPelanggan() }

    Column(Modifier.fillMaxSize().padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
        OutlinedTextField(
            value = query,
            onValueChange = {
                query = it
                vm.loadPelanggan(query = it)
            },
            label = { Text("Cari pelanggan") },
            modifier = Modifier.fillMaxWidth()
        )
        LazyColumn(verticalArrangement = Arrangement.spacedBy(8.dp)) {
            items(pelanggan) { item -> PelangganItem(item) }
        }
    }
}

@Composable
private fun PelangganItem(item: Pelanggan) {
    Card(Modifier.fillMaxWidth().clickable { }) {
        Column(Modifier.padding(12.dp)) {
            Text("${item.kodePelanggan} - ${item.nama}")
            Text(item.alamat)
            Text("Desa ${item.desa} | Status ${item.status}")
            Text("Koordinat: ${item.latitude ?: "-"}, ${item.longitude ?: "-"}")
        }
    }
}
