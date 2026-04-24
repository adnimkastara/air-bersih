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
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
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
import com.airbersih.mobile.utils.DateTimeUtils
import com.airbersih.mobile.viewmodel.MainViewModel

@Composable
fun TagihanScreen(vm: MainViewModel) {
    val items by vm.tagihan.collectAsState()
    val detail by vm.tagihanDetail.collectAsState()
    var period by remember { mutableStateOf(DateTimeUtils.todayIsoDate().take(7)) }
    LaunchedEffect(Unit) { vm.loadTagihan(null) }

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
                "Manajemen Tagihan",
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
                    Text("Buat Tagihan Massal", fontWeight = FontWeight.Bold)
                    OutlinedTextField(
                        value = period,
                        onValueChange = { period = it },
                        label = { Text("Periode (Contoh: 2026-04)") },
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp)
                    )
                    Button(
                        onClick = { vm.generateTagihan(period) },
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp)
                    ) {
                        Text("Proses Generate Tagihan")
                    }
                }
            }
        }

        item {
            detail?.let {
                Card(
                    modifier = Modifier.fillMaxWidth(),
                    shape = RoundedCornerShape(16.dp),
                    colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.primaryContainer)
                ) {
                    Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(4.dp)) {
                        Text("Ringkasan Detail Tagihan", fontWeight = FontWeight.Bold)
                        Text("Total Dibayar: Rp${it.totalPaid ?: 0.0}")
                        Text("Sisa Tagihan: Rp${it.remaining ?: 0.0}", color = MaterialTheme.colorScheme.error, fontWeight = FontWeight.Bold)
                        Text("Gunakan menu Pembayaran untuk melunasi.", style = MaterialTheme.typography.bodySmall)
                    }
                }
            }
        }

        item {
            Text("Daftar Tagihan", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.Bold, modifier = Modifier.padding(top = 8.dp))
        }

        items(items = items, key = { it.id ?: it.periode.orEmpty() }) { item ->
            Card(
                modifier = Modifier.fillMaxWidth(),
                shape = RoundedCornerShape(12.dp),
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                elevation = CardDefaults.cardElevation(defaultElevation = 1.dp)
            ) {
                Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                    Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                        Text("Periode: ${item.periode ?: "-"}", fontWeight = FontWeight.SemiBold)
                        Text(
                            item.status?.uppercase() ?: "-",
                            style = MaterialTheme.typography.labelSmall,
                            color = if (item.status == "lunas") MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.error,
                            fontWeight = FontWeight.Bold
                        )
                    }
                    Text(item.pelanggan?.nama ?: "Tanpa Nama", style = MaterialTheme.typography.bodyLarge, fontWeight = FontWeight.Bold)
                    Text("Nominal: Rp${item.nominal ?: 0.0}", color = MaterialTheme.colorScheme.primary, fontWeight = FontWeight.SemiBold)

                    HorizontalDivider(thickness = 0.5.dp)

                    Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        Button(
                            onClick = { item.id?.let(vm::loadTagihanDetail) },
                            modifier = Modifier.weight(1f),
                            shape = RoundedCornerShape(8.dp),
                            colors = ButtonDefaults.buttonColors(containerColor = MaterialTheme.colorScheme.secondaryContainer, contentColor = MaterialTheme.colorScheme.onSecondaryContainer)
                        ) {
                            Text("Cek Sisa", style = MaterialTheme.typography.bodySmall)
                        }

                        if (item.status == "draft") {
                            Button(
                                onClick = { item.id?.let(vm::publishTagihan) },
                                modifier = Modifier.weight(1f),
                                shape = RoundedCornerShape(8.dp)
                            ) {
                                Text("Terbitkan", style = MaterialTheme.typography.bodySmall)
                            }
                        }
                    }
                }
            }
        }

        item {
            Spacer(modifier = Modifier.height(24.dp))
        }
    }
}
