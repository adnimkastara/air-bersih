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
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.airbersih.mobile.viewmodel.MainViewModel

@Composable
fun TagihanScreen(vm: MainViewModel) {
    val items by vm.tagihan.collectAsState()
    val detail by vm.tagihanDetail.collectAsState()
    LaunchedEffect(Unit) { vm.loadTagihan(null) }

    Column(modifier = Modifier.fillMaxSize().padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
        MenuStatusBanner(vm)
        LazyColumn(verticalArrangement = Arrangement.spacedBy(8.dp)) {
            items(items = items, key = { it.id ?: it.periode.orEmpty() }) { item ->
                Card {
                    Column(Modifier.padding(12.dp)) {
                        Text("Periode ${item.periode ?: "-"}")
                        Text("Pelanggan ${item.pelanggan?.nama ?: "-"}")
                        Text("Nominal Rp${item.nominal ?: 0.0}")
                        Text("Status ${item.status ?: "-"}")
                        Button(onClick = { item.id?.let(vm::loadTagihanDetail) }, modifier = Modifier.fillMaxWidth()) {
                            Text("Lihat Detail")
                        }
                        if (item.status == "draft") {
                            Button(onClick = { item.id?.let(vm::publishTagihan) }, modifier = Modifier.fillMaxWidth()) {
                                Text("Terbitkan Tagihan")
                            }
                        }
                    }
                }
            }
            item {
                detail?.let {
                    Card {
                        Column(Modifier.padding(12.dp), verticalArrangement = Arrangement.spacedBy(4.dp)) {
                            Text("Detail Tagihan #${it.tagihan?.id ?: "-"}")
                            Text("Total dibayar: Rp${it.totalPaid ?: 0.0}")
                            Text("Sisa: Rp${it.remaining ?: 0.0}")
                            Text("Lanjutkan pembayaran dari menu Pembayaran.")
                        }
                    }
                }
            }
        }
    }
}
