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
import com.airbersih.mobile.utils.DateTimeUtils
import com.airbersih.mobile.viewmodel.MainViewModel

import androidx.compose.material.icons.filled.Info
import androidx.compose.material.icons.filled.Edit

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun PembayaranScreen(vm: MainViewModel) {
    val tagihan by vm.tagihan.collectAsState()
    val pembayaranList by vm.pembayaranList.collectAsState()
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
            .padding(horizontal = 16.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        item {
            Spacer(modifier = Modifier.height(16.dp))
            MenuStatusBanner(vm)
            Text(
                "Input Pembayaran",
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
                    Text("Formulir Pembayaran", fontWeight = FontWeight.Bold)

                    ExposedDropdownMenuBox(expanded = expanded, onExpandedChange = { expanded = !expanded }) {
                        OutlinedTextField(
                            value = selectedTagihan,
                            onValueChange = {},
                            readOnly = true,
                            trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expanded) },
                            leadingIcon = { Icon(Icons.Default.Edit, contentDescription = null) },
                            modifier = Modifier.menuAnchor().fillMaxWidth(),
                            label = { Text("Pilih Tagihan") },
                            shape = RoundedCornerShape(12.dp)
                        )
                        ExposedDropdownMenu(expanded = expanded, onDismissRequest = { expanded = false }) {
                            tagihan.filter { it.status != "lunas" }.forEach { item ->
                                DropdownMenuItem(text = { Text("#${item.id} ${item.pelanggan?.nama ?: ""} (${item.status})") }, onClick = {
                                    tagihanId = item.id
                                    selectedTagihan = "#${item.id} ${item.periode} - Rp${item.nominal ?: 0.0}"
                                    nominal = item.nominal?.toLong()?.toString().orEmpty()
                                    expanded = false
                                })
                            }
                        }
                    }

                    OutlinedTextField(
                        value = nominal,
                        onValueChange = { nominal = it.filter(Char::isDigit) },
                        label = { Text("Nominal Bayar (Rp)") },
                        leadingIcon = { Icon(Icons.Default.Info, contentDescription = null) },
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp),
                        prefix = { Text("Rp ", fontWeight = FontWeight.Bold) }
                    )

                    ExposedDropdownMenuBox(expanded = methodExpanded, onExpandedChange = { methodExpanded = !methodExpanded }) {
                        OutlinedTextField(
                            value = metode,
                            onValueChange = {},
                            readOnly = true,
                            trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = methodExpanded) },
                            modifier = Modifier.menuAnchor().fillMaxWidth(),
                            label = { Text("Metode Pembayaran") },
                            shape = RoundedCornerShape(12.dp)
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

                    OutlinedTextField(catatan, { catatan = it }, label = { Text("Catatan / No. Referensi") }, modifier = Modifier.fillMaxWidth(), shape = RoundedCornerShape(12.dp))

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
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp)
                    ) {
                        Text("Konfirmasi Pembayaran")
                    }
                }
            }
        }

        item {
            Text("Riwayat Pembayaran Petugas", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.Bold, modifier = Modifier.padding(top = 8.dp))
        }

        items(pembayaranList, key = { it.id ?: (it.paidAt.orEmpty() + it.tagihanId) }) { item ->
            Card(
                modifier = Modifier.fillMaxWidth(),
                shape = RoundedCornerShape(12.dp),
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                elevation = CardDefaults.cardElevation(defaultElevation = 1.dp)
            ) {
                Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(4.dp)) {
                    Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Text("Pembayaran #${item.id ?: "-"}", fontWeight = FontWeight.Bold)
                        Text(item.paymentMethod?.uppercase() ?: "-", style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.primary)
                    }
                    Text("Tagihan #${item.tagihanId ?: "-"}", style = MaterialTheme.typography.bodySmall)
                    Text("Nominal: Rp${item.amount ?: 0.0}", fontWeight = FontWeight.SemiBold, color = MaterialTheme.colorScheme.primary)
                    HorizontalDivider(modifier = Modifier.padding(vertical = 4.dp), thickness = 0.5.dp)
                    Text("Dibayar pada: ${item.paidAt ?: "-"}", style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.secondary)
                }
            }
        }

        item {
            Spacer(modifier = Modifier.height(24.dp))
        }
    }
}
