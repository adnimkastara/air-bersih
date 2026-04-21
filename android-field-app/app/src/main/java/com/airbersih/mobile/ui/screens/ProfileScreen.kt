package com.airbersih.mobile.ui.screens

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.airbersih.mobile.viewmodel.MainViewModel

@Composable
fun ProfileScreen(vm: MainViewModel) {
    val me by vm.me.collectAsState()
    var currentPassword by remember { mutableStateOf("") }
    var newPassword by remember { mutableStateOf("") }
    var confirmPassword by remember { mutableStateOf("") }

    Column(
        modifier = Modifier.fillMaxSize().padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        MenuStatusBanner(vm)
        Card(modifier = Modifier.fillMaxWidth()) {
            Column(Modifier.padding(12.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
                Text("Profil Petugas")
                Text("Nama: ${me?.name ?: "-"}")
                Text("Email: ${me?.email ?: "-"}")
                Text("Role: ${me?.role?.name ?: "-"}")
                Text("Desa: ${me?.desa?.name ?: "-"}")
            }
        }
        Card(modifier = Modifier.fillMaxWidth()) {
            Column(Modifier.padding(12.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
                Text("Ubah Password")
                OutlinedTextField(currentPassword, { currentPassword = it }, label = { Text("Password saat ini") }, modifier = Modifier.fillMaxWidth())
                OutlinedTextField(newPassword, { newPassword = it }, label = { Text("Password baru") }, modifier = Modifier.fillMaxWidth())
                OutlinedTextField(confirmPassword, { confirmPassword = it }, label = { Text("Konfirmasi password") }, modifier = Modifier.fillMaxWidth())
                Button(onClick = { vm.updatePassword(currentPassword, newPassword, confirmPassword) }, modifier = Modifier.fillMaxWidth()) {
                    Text("Simpan Password")
                }
            }
        }
    }
}
