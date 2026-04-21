package com.airbersih.mobile.ui.screens

import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import com.airbersih.mobile.viewmodel.MainViewModel

@Composable
fun MenuStatusBanner(vm: MainViewModel) {
    val message by vm.statusMessage.collectAsState()
    val loading by vm.loadingMenu.collectAsState()

    Column(modifier = Modifier.fillMaxWidth()) {
        if (loading != null) {
            Text(
                text = "Loading menu: $loading ...",
                style = MaterialTheme.typography.bodySmall
            )
        }
        message?.let {
            Text(
                text = it,
                style = MaterialTheme.typography.bodyMedium,
                color = MaterialTheme.colorScheme.error
            )
        }
    }
}
