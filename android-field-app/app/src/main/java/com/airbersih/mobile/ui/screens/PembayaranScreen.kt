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
import com.airbersih.mobile.utils.DateTimeUtils
import com.airbersih.mobile.viewmodel.MainViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun PembayaranScreen(vm: MainViewModel) {
    val tagihan by vm.tagihan.collectAsState()
    val pembayaranList by vm.pembayaranList.collectAsState()
    val loading by vm.loadingMenu.collectAsState()

    val paymentMethods = listOf("tunai", "transfer_bank", "e_wallet")
    var tagihanId by remember { mutableStateOf<Long?>(null) }
    var selectedTagihan by remember { mutableStateOf("Pilih tagihan") }
    var nominal by remember { mutableStateOf("") }
    var metode by remember { mutableStateOf("tunai") }
    var catatan by remember { mutableStateOf("") }
    var expanded by remember { mutableStateOf(false) }
    var methodExpanded by remember { mutableStateOf(false) }

    LaunchedEffect(Unit) {
        vm.loadTagihan(null)
        vm.loadPembayaranList()
    }

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
                "Input Pembayaran",
                style = MaterialTheme.typography.headlineMedium,
                fontWeight = FontWeight.ExtraBold,
                color = MaterialTheme.colorScheme.primary
            )
            Text(
                "Catat transaksi pembayaran tagihan air pelanggan",
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
                    Text("Formulir Pembayaran", fontWeight = FontWeight.Bold, style = MaterialTheme.typography.titleMedium)

                    ExposedDropdownMenuBox(expanded = expanded, onExpandedChange = { expanded = !expanded }) {
                        OutlinedTextField(
                            value = selectedTagihan,
                            onValueChange = {},
                            readOnly = true,
                            trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expanded) },
                            leadingIcon = { Icon(Icons.Default.Edit, contentDescription = null, tint = MaterialTheme.colorScheme.primary) },
                            modifier = Modifier.menuAnchor().fillMaxWidth(),
                            label = { Text("Pilih Tagihan Terbit") },
                            shape = RoundedCornerShape(12.dp),
                            colors = OutlinedTextFieldDefaults.colors(
                                unfocusedBorderColor = MaterialTheme.colorScheme.outline.copy(alpha = 0.3f)
                            )
                        )
                        ExposedDropdownMenu(expanded = expanded, onDismissRequest = { expanded = false }) {
                            val activeTagihan = tagihan.filter { it.status != "lunas" }
                            if (activeTagihan.isEmpty()) {
                                DropdownMenuItem(text = { Text("Tidak ada tagihan aktif") }, onClick = { expanded = false })
                            }
                            activeTagihan.forEach { item ->
                                DropdownMenuItem(
                                    text = {
                                        Column {
                                            Text("${item.pelanggan?.nama ?: "Tanpa Nama"} (${item.periode})", fontWeight = FontWeight.Bold)
                                            Text("Sisa: Rp${item.nominal ?: 0.0}", style = MaterialTheme.typography.labelSmall)
                                        }
                                    },
                                    onClick = {
                                        tagihanId = item.id
                                        selectedTagihan = "${item.pelanggan?.nama} - ${item.periode}"
                                        nominal = item.nominal?.toLong()?.toString().orEmpty()
                                        expanded = false
                                    }
                                )
                            }
                        }
                    }

                    OutlinedTextField(
                        value = nominal,
                        onValueChange = { nominal = it.filter(Char::isDigit) },
                        label = { Text("Nominal Bayar (Rp)") },
                        leadingIcon = { Icon(Icons.Default.Add, contentDescription = null, tint = MaterialTheme.colorScheme.primary) },
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp),
                        prefix = { Text("Rp ", fontWeight = FontWeight.Bold) },
                        colors = OutlinedTextFieldDefaults.colors(
                            unfocusedBorderColor = MaterialTheme.colorScheme.outline.copy(alpha = 0.3f)
                        )
                    )

                    ExposedDropdownMenuBox(expanded = methodExpanded, onExpandedChange = { methodExpanded = !methodExpanded }) {
                        OutlinedTextField(
                            value = metode.replace("_", " ").uppercase(),
                            onValueChange = {},
                            readOnly = true,
                            trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = methodExpanded) },
                            leadingIcon = { Icon(Icons.Default.Info, contentDescription = null, tint = MaterialTheme.colorScheme.primary) },
                            modifier = Modifier.menuAnchor().fillMaxWidth(),
                            label = { Text("Metode Pembayaran") },
                            shape = RoundedCornerShape(12.dp),
                            colors = OutlinedTextFieldDefaults.colors(
                                unfocusedBorderColor = MaterialTheme.colorScheme.outline.copy(alpha = 0.3f)
                            )
                        )
                        ExposedDropdownMenu(expanded = methodExpanded, onDismissRequest = { methodExpanded = false }) {
                            paymentMethods.forEach { method ->
                                DropdownMenuItem(text = { Text(method.replace("_", " ").uppercase()) }, onClick = {
                                    metode = method
                                    methodExpanded = false
                                })
                            }
                        }
                    }

                    OutlinedTextField(
                        value = catatan,
                        onValueChange = { catatan = it },
                        label = { Text("Catatan / No. Referensi") },
                        leadingIcon = { Icon(Icons.Default.Info, contentDescription = null, tint = MaterialTheme.colorScheme.primary) },
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp),
                        colors = OutlinedTextFieldDefaults.colors(
                            unfocusedBorderColor = MaterialTheme.colorScheme.outline.copy(alpha = 0.3f)
                        )
                    )

                    Button(
                        onClick = {
                            val n = nominal.toLongOrNull()
                            if (tagihanId != null && n != null) {
                                vm.submitPembayaran(tagihanId!!, n, metode, DateTimeUtils.todayIsoDate(), catatan)
                                tagihanId = null; selectedTagihan = "Pilih tagihan"; nominal = ""; catatan = ""
                            } else {
                                vm.showMessage("Pilih tagihan dan isi nominal valid.")
                            }
                        },
                        modifier = Modifier.fillMaxWidth().height(56.dp),
                        shape = RoundedCornerShape(16.dp)
                    ) {
                        Icon(Icons.Default.CheckCircle, contentDescription = null)
                        Spacer(Modifier.width(8.dp))
                        Text("Konfirmasi Pembayaran", fontWeight = FontWeight.Bold)
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
                Text("Riwayat Pembayaran", style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.Bold)
                if (loading == "pembayaran") {
                    CircularProgressIndicator(modifier = Modifier.size(20.dp), strokeWidth = 2.dp)
                }
            }
        }

        if (pembayaranList.isEmpty() && loading != "pembayaran") {
            item {
                Box(Modifier.fillMaxWidth().padding(40.dp), contentAlignment = Alignment.Center) {
                    Text("Belum ada riwayat pembayaran", color = MaterialTheme.colorScheme.secondary)
                }
            }
        }

        items(pembayaranList, key = { it.id ?: (it.paidAt.orEmpty() + it.tagihanId + Math.random()) }) { item ->
            PaymentItemCard(item)
        }

        item {
            Spacer(modifier = Modifier.height(32.dp))
        }
    }
}

@Composable
private fun PaymentItemCard(item: com.airbersih.mobile.model.Pembayaran) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(20.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(defaultElevation = 1.dp)
    ) {
        Column(Modifier.padding(20.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                Text(
                    "Pembayaran #${item.id ?: "-"}",
                    fontWeight = FontWeight.Bold,
                    style = MaterialTheme.typography.labelMedium,
                    color = MaterialTheme.colorScheme.primary
                )
                Box(
                    modifier = Modifier
                        .clip(RoundedCornerShape(8.dp))
                        .background(MaterialTheme.colorScheme.primaryContainer.copy(alpha = 0.5f))
                        .padding(horizontal = 8.dp, vertical = 4.dp)
                ) {
                    Text(item.paymentMethod?.uppercase() ?: "-", style = MaterialTheme.typography.labelSmall, fontWeight = FontWeight.Bold)
                }
            }

            Text(
                "Tagihan #${item.tagihanId ?: "-"}",
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.secondary
            )

            Text(
                "Rp${item.amount ?: 0.0}",
                style = MaterialTheme.typography.titleLarge,
                fontWeight = FontWeight.ExtraBold,
                color = MaterialTheme.colorScheme.onSurface
            )

            HorizontalDivider(thickness = 0.5.dp, color = MaterialTheme.colorScheme.outlineVariant)

            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(Icons.Default.DateRange, contentDescription = null, modifier = Modifier.size(14.dp), tint = MaterialTheme.colorScheme.secondary)
                Spacer(Modifier.width(4.dp))
                Text("Dibayar pada: ${item.paidAt ?: "-"}", style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.secondary)
            }
        }
    }
}
