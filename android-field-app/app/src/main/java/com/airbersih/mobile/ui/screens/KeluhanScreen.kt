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

@Composable
fun KeluhanScreen(vm: MainViewModel) {
    var judul by remember { mutableStateOf("") }
    var deskripsi by remember { mutableStateOf("") }
    var kategori by remember { mutableStateOf("gangguan") }
    var prioritas by remember { mutableStateOf("sedang") }
    val items by vm.keluhan.collectAsState()

    LaunchedEffect(Unit) { vm.loadKeluhan() }

    LazyColumn(
        modifier = Modifier.fillMaxSize().padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        item {
            MenuStatusBanner(vm)
            OutlinedTextField(judul, { judul = it }, label = { Text("Judul") }, modifier = Modifier.fillMaxWidth())
            OutlinedTextField(deskripsi, { deskripsi = it }, label = { Text("Deskripsi") }, modifier = Modifier.fillMaxWidth())
            OutlinedTextField(kategori, { kategori = it }, label = { Text("Jenis laporan") }, modifier = Modifier.fillMaxWidth())
            OutlinedTextField(prioritas, { prioritas = it }, label = { Text("Prioritas") }, modifier = Modifier.fillMaxWidth())
            Button(onClick = { vm.submitKeluhan(judul, deskripsi, kategori, prioritas) }, modifier = Modifier.fillMaxWidth()) {
                Text("Kirim Keluhan")
            }
        }
        items(items = items, key = { it.id ?: it.judul.orEmpty() }) { k ->
            Card {
                Column(Modifier.padding(12.dp)) {
                    Text(k.judul ?: "Tanpa judul")
                    Text("${k.jenisLaporan ?: "-"} | ${k.prioritas ?: "-"} | ${k.statusPenanganan ?: "-"}")
                    Text("Lokasi: ${k.latitude ?: "-"}, ${k.longitude ?: "-"}")
                }
            }
        }
    }
}
