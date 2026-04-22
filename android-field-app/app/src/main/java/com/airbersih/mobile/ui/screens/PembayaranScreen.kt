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
import com.airbersih.mobile.utils.DateTimeUtils
import com.airbersih.mobile.viewmodel.MainViewModel

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

    LazyColumn(Modifier.fillMaxSize().padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
        item {
            MenuStatusBanner(vm)
            ExposedDropdownMenuBox(expanded = expanded, onExpandedChange = { expanded = !expanded }) {
                OutlinedTextField(
                    value = selectedTagihan,
                    onValueChange = {},
                    readOnly = true,
                    trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expanded) },
                    modifier = Modifier.menuAnchor().fillMaxWidth(),
                    label = { Text("Tagihan aktif") }
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
            OutlinedTextField(nominal, { nominal = it.filter(Char::isDigit) }, label = { Text("Nominal") }, modifier = Modifier.fillMaxWidth())
            ExposedDropdownMenuBox(expanded = methodExpanded, onExpandedChange = { methodExpanded = !methodExpanded }) {
                OutlinedTextField(
                    value = metode,
                    onValueChange = {},
                    readOnly = true,
                    trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = methodExpanded) },
                    modifier = Modifier.menuAnchor().fillMaxWidth(),
                    label = { Text("Metode pembayaran") }
                )
                ExposedDropdownMenu(expanded = methodExpanded, onDismissRequest = { methodExpanded = false }) {
                    paymentMethods.forEach { method ->
                        DropdownMenuItem(text = { Text(method) }, onClick = {
                            metode = method
                            methodExpanded = false
                        })
                    }
                }
            }
            OutlinedTextField(catatan, { catatan = it }, label = { Text("Catatan") }, modifier = Modifier.fillMaxWidth())
            Button(onClick = {
                val n = nominal.toLongOrNull()
                if (tagihanId != null && n != null) {
                    vm.submitPembayaran(tagihanId!!, n, metode, DateTimeUtils.todayIsoDate(), catatan)
                } else {
                    vm.showMessage("Pilih tagihan dan isi nominal valid.")
                }
            }, modifier = Modifier.fillMaxWidth()) {
                Text("Simpan Pembayaran")
            }
            Text("Riwayat Pembayaran")
        }
        items(pembayaranList, key = { it.id ?: 0 }) { item ->
            Card {
                Column(Modifier.padding(12.dp)) {
                    Text("Pembayaran #${item.id ?: "-"}")
                    Text("Tagihan #${item.tagihanId ?: "-"}")
                    Text("Nominal Rp${item.amount ?: 0.0} | ${item.paymentMethod ?: "-"}")
                    Text("Tanggal ${item.paidAt ?: "-"}")
                }
            }
        }
    }
}
