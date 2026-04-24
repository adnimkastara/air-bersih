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

@Composable
fun TagihanScreen(vm: MainViewModel) {
    val items by vm.tagihan.collectAsState()
    val detail by vm.tagihanDetail.collectAsState()
    val loading by vm.loadingMenu.collectAsState()
    var period by remember { mutableStateOf(DateTimeUtils.todayIsoDate().take(7)) }

    LaunchedEffect(Unit) { vm.loadTagihan(null) }

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
                "Manajemen Tagihan",
                style = MaterialTheme.typography.headlineMedium,
                fontWeight = FontWeight.ExtraBold,
                color = MaterialTheme.colorScheme.primary
            )
            Text(
                "Generate dan pantau status tagihan pelanggan",
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
                Column(Modifier.padding(20.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                    Text("Generate Tagihan Massal", fontWeight = FontWeight.Bold, style = MaterialTheme.typography.titleMedium)

                    OutlinedTextField(
                        value = period,
                        onValueChange = { period = it },
                        label = { Text("Periode (YYYY-MM)") },
                        placeholder = { Text("Contoh: 2026-04") },
                        leadingIcon = { Icon(Icons.Default.DateRange, contentDescription = null, tint = MaterialTheme.colorScheme.primary) },
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp),
                        colors = OutlinedTextFieldDefaults.colors(
                            unfocusedBorderColor = MaterialTheme.colorScheme.outline.copy(alpha = 0.3f)
                        )
                    )

                    Button(
                        onClick = { vm.generateTagihan(period) },
                        modifier = Modifier.fillMaxWidth().height(52.dp),
                        shape = RoundedCornerShape(12.dp)
                    ) {
                        Icon(Icons.Default.PlayArrow, contentDescription = null)
                        Spacer(Modifier.width(8.dp))
                        Text("Proses Sekarang", fontWeight = FontWeight.Bold)
                    }
                }
            }
        }

        item {
            detail?.let {
                Card(
                    modifier = Modifier.fillMaxWidth(),
                    shape = RoundedCornerShape(20.dp),
                    colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.primaryContainer.copy(alpha = 0.4f))
                ) {
                    Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Icon(Icons.Default.Info, contentDescription = null, tint = MaterialTheme.colorScheme.primary)
                            Spacer(Modifier.width(8.dp))
                            Text("Ringkasan Detail Tagihan", fontWeight = FontWeight.Bold)
                        }
                        HorizontalDivider(thickness = 0.5.dp, color = MaterialTheme.colorScheme.primary.copy(alpha = 0.1f))

                        DetailRow("Total Tagihan", "Rp${it.tagihan?.nominal ?: 0.0}")
                        DetailRow("Sudah Dibayar", "Rp${it.totalPaid ?: 0.0}")
                        DetailRow("Sisa Piutang", "Rp${it.remaining ?: 0.0}", color = MaterialTheme.colorScheme.error)

                        Text("Gunakan menu Pembayaran untuk pelunasan.", style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.secondary)
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
                Text("Daftar Tagihan", style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.Bold)
                if (loading == "tagihan") {
                    CircularProgressIndicator(modifier = Modifier.size(20.dp), strokeWidth = 2.dp)
                }
            }
        }

        if (items.isEmpty() && loading != "tagihan") {
            item {
                Box(Modifier.fillMaxWidth().padding(40.dp), contentAlignment = Alignment.Center) {
                    Text("Tidak ada tagihan yang ditemukan", color = MaterialTheme.colorScheme.secondary)
                }
            }
        }

        items(items = items, key = { it.id ?: (it.periode.orEmpty() + it.pelangganId) }) { item ->
            TagihanItemCard(item, onDetail = { item.id?.let(vm::loadTagihanDetail) }, onPublish = { item.id?.let(vm::publishTagihan) })
        }

        item {
            Spacer(modifier = Modifier.height(32.dp))
        }
    }
}

@Composable
private fun DetailRow(label: String, value: String, color: Color = Color.Unspecified) {
    Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
        Text(label, style = MaterialTheme.typography.bodySmall)
        Text(value, style = MaterialTheme.typography.bodySmall, fontWeight = FontWeight.Bold, color = color)
    }
}

@Composable
private fun TagihanItemCard(item: com.airbersih.mobile.model.Tagihan, onDetail: () -> Unit, onPublish: () -> Unit) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(20.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(defaultElevation = 1.dp)
    ) {
        Column(Modifier.padding(20.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                Text(
                    "Periode: ${item.periode ?: "-"}",
                    style = MaterialTheme.typography.labelMedium,
                    fontWeight = FontWeight.Bold,
                    color = MaterialTheme.colorScheme.secondary
                )
                TagihanStatusBadge(item.status ?: "draft")
            }

            Text(
                item.pelanggan?.nama ?: "Tanpa Nama",
                style = MaterialTheme.typography.titleMedium,
                fontWeight = FontWeight.Bold
            )

            Text(
                "Rp${item.nominal ?: 0.0}",
                style = MaterialTheme.typography.headlineSmall,
                fontWeight = FontWeight.ExtraBold,
                color = MaterialTheme.colorScheme.primary
            )

            HorizontalDivider(thickness = 0.5.dp, color = MaterialTheme.colorScheme.outlineVariant)

            Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                OutlinedButton(
                    onClick = onDetail,
                    modifier = Modifier.weight(1f),
                    shape = RoundedCornerShape(12.dp),
                    contentPadding = PaddingValues(0.dp)
                ) {
                    Text("Cek Sisa", style = MaterialTheme.typography.labelMedium)
                }

                if (item.status == "draft") {
                    Button(
                        onClick = onPublish,
                        modifier = Modifier.weight(1f),
                        shape = RoundedCornerShape(12.dp),
                        contentPadding = PaddingValues(0.dp)
                    ) {
                        Text("Terbitkan", style = MaterialTheme.typography.labelMedium)
                    }
                }
            }
        }
    }
}

@Composable
private fun TagihanStatusBadge(status: String) {
    val (bgColor, textColor) = when (status.lowercase()) {
        "lunas" -> Color(0xFFDCFCE7) to Color(0xFF166534)
        "terbit" -> Color(0xFFDBEAFE) to Color(0xFF1E40AF)
        "draft" -> Color(0xFFF3F4F6) to Color(0xFF374151)
        "menunggak" -> Color(0xFFFEE2E2) to Color(0xFF991B1B)
        else -> MaterialTheme.colorScheme.surfaceVariant to MaterialTheme.colorScheme.onSurfaceVariant
    }

    Box(
        modifier = Modifier
            .clip(RoundedCornerShape(8.dp))
            .background(bgColor)
            .padding(horizontal = 8.dp, vertical = 4.dp)
    ) {
        Text(status.uppercase(), style = MaterialTheme.typography.labelSmall, fontWeight = FontWeight.ExtraBold, color = textColor)
    }
}
