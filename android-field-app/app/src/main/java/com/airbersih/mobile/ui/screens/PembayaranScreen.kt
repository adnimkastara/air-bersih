package com.airbersih.mobile.ui.screens

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Button
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.airbersih.mobile.utils.DateTimeUtils
import com.airbersih.mobile.viewmodel.MainViewModel

@Composable
fun PembayaranScreen(vm: MainViewModel) {
    var tagihanId by remember { mutableStateOf("") }
    var nominal by remember { mutableStateOf("") }
    var metode by remember { mutableStateOf("tunai") }
    var catatan by remember { mutableStateOf("") }

    Column(Modifier.fillMaxSize().padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
        MenuStatusBanner(vm)
        OutlinedTextField(tagihanId, { tagihanId = it }, label = { Text("Tagihan ID") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(nominal, { nominal = it.filter(Char::isDigit) }, label = { Text("Nominal") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(metode, { metode = it }, label = { Text("Metode") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(catatan, { catatan = it }, label = { Text("Catatan") }, modifier = Modifier.fillMaxWidth())
        Button(onClick = {
            val t = tagihanId.toLongOrNull()
            val n = nominal.toLongOrNull()
            if (t != null && n != null) {
                vm.submitPembayaran(t, n, metode, DateTimeUtils.todayIsoDate(), catatan)
            } else {
                vm.showMessage("Tagihan ID dan nominal harus berupa angka valid.")
            }
        }, modifier = Modifier.fillMaxWidth()) {
            Text("Simpan Pembayaran")
        }
    }
}
