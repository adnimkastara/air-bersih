package com.airbersih.mobile.ui.screens

import android.annotation.SuppressLint
import android.webkit.WebChromeClient
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.compose.ui.viewinterop.AndroidView
import com.airbersih.mobile.model.Keluhan
import com.airbersih.mobile.model.MonitoringMapResponse
import com.airbersih.mobile.model.Pelanggan
import com.airbersih.mobile.viewmodel.MainViewModel
import java.util.Locale

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MonitoringScreen(vm: MainViewModel) {
    val monitoring by vm.monitoring.collectAsState()

    LaunchedEffect(Unit) { vm.loadMonitoring() }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Monitoring Peta SIPAM") }
            )
        }
    ) { paddingValues ->
        if (monitoring == null) {
            Column(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(paddingValues),
                verticalArrangement = Arrangement.Center,
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                CircularProgressIndicator()
                Text(
                    text = "Memuat data monitoring...",
                    modifier = Modifier.padding(top = 12.dp),
                    style = MaterialTheme.typography.bodyMedium
                )
            }
        } else {
            LeafletMonitoringWebView(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(paddingValues),
                monitoring = monitoring!!
            )
        }
    }
}

@SuppressLint("SetJavaScriptEnabled")
@Composable
private fun LeafletMonitoringWebView(
    modifier: Modifier = Modifier,
    monitoring: MonitoringMapResponse
) {
    var htmlContent by remember(monitoring) {
        mutableStateOf(buildLeafletHtml(monitoring))
    }

    AndroidView(
        modifier = modifier,
        factory = { context ->
            WebView(context).apply {
                settings.javaScriptEnabled = true
                settings.domStorageEnabled = true
                settings.loadsImagesAutomatically = true
                webChromeClient = WebChromeClient()
                webViewClient = WebViewClient()
                loadDataWithBaseURL(
                    "https://localhost/",
                    htmlContent,
                    "text/html",
                    "UTF-8",
                    null
                )
            }
        },
        update = { view ->
            htmlContent = buildLeafletHtml(monitoring)
            view.loadDataWithBaseURL("https://localhost/", htmlContent, "text/html", "UTF-8", null)
        }
    )
}

private fun buildLeafletHtml(monitoring: MonitoringMapResponse): String {
    val fallbackLat = monitoring.fallbackCenter?.latitude ?: -6.2
    val fallbackLng = monitoring.fallbackCenter?.longitude ?: 106.8

    val pelangganMarkers = monitoring.pelanggan.pelangganToMarkers("pelanggan")
    val keluhanMarkers = monitoring.keluhanAktif.keluhanToMarkers("keluhan")

    return """
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <style>
                html, body, #map { height: 100%; margin: 0; }
                body { font-family: sans-serif; background: #f8fafc; }
                .legend {
                    position: absolute;
                    z-index: 999;
                    right: 12px;
                    top: 12px;
                    background: rgba(255,255,255,0.95);
                    padding: 10px 12px;
                    border-radius: 10px;
                    font-size: 12px;
                    box-shadow: 0 4px 16px rgba(2,6,23,0.2);
                }
                .legend .dot {
                    width: 10px;
                    height: 10px;
                    border-radius: 50%;
                    display: inline-block;
                    margin-right: 6px;
                }
            </style>
        </head>
        <body>
            <div id="map"></div>
            <div class="legend">
                <div><span class="dot" style="background:#0ea5e9"></span>Pelanggan</div>
                <div><span class="dot" style="background:#ef4444"></span>Keluhan aktif</div>
            </div>
            <script>
                const map = L.map('map').setView([$fallbackLat, $fallbackLng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                const pelangganIcon = L.divIcon({
                    html: '<div style="width:14px;height:14px;border-radius:50%;background:#0ea5e9;border:2px solid white;box-shadow:0 0 0 1px #0284c7"></div>',
                    className: '',
                    iconSize: [14, 14]
                });

                const keluhanIcon = L.divIcon({
                    html: '<div style="width:14px;height:14px;border-radius:50%;background:#ef4444;border:2px solid white;box-shadow:0 0 0 1px #b91c1c"></div>',
                    className: '',
                    iconSize: [14, 14]
                });

                const pelangganData = [$pelangganMarkers];
                pelangganData.forEach((item) => {
                    L.marker([item.lat, item.lng], { icon: pelangganIcon })
                        .addTo(map)
                        .bindPopup(`<strong>${'$'}{item.title}</strong><br/>${'$'}{item.subtitle}`);
                });

                const keluhanData = [$keluhanMarkers];
                keluhanData.forEach((item) => {
                    L.marker([item.lat, item.lng], { icon: keluhanIcon })
                        .addTo(map)
                        .bindPopup(`<strong>${'$'}{item.title}</strong><br/>${'$'}{item.subtitle}`);
                });
            </script>
        </body>
        </html>
    """.trimIndent()
}

private fun List<Pelanggan>.pelangganToMarkers(type: String): String =
    mapNotNull { p ->
        val lat = p.latitude ?: return@mapNotNull null
        val lng = p.longitude ?: return@mapNotNull null
        val title = escapeJs(p.nama ?: "Pelanggan")
        val subtitle = escapeJs("${p.kodePelanggan ?: "-"} | ${p.status ?: "-"}")
        markerJson(type, lat, lng, title, subtitle)
    }.joinToString(",")

private fun List<Keluhan>.keluhanToMarkers(type: String): String =
    mapNotNull { k ->
        val lat = k.latitude ?: return@mapNotNull null
        val lng = k.longitude ?: return@mapNotNull null
        val title = escapeJs(k.judul ?: "Keluhan")
        val subtitle = escapeJs("${k.jenisLaporan ?: "-"} | ${k.prioritas ?: "-"}")
        markerJson(type, lat, lng, title, subtitle)
    }.joinToString(",")

private fun markerJson(type: String, lat: Double, lng: Double, title: String, subtitle: String): String {
    return "{type:'$type',lat:${lat.format()},lng:${lng.format()},title:'$title',subtitle:'$subtitle'}"
}

private fun escapeJs(input: String): String =
    input.replace("\\", "\\\\").replace("'", "\\'").replace("\n", " ")

private fun Double.format(): String = String.format(Locale.US, "%.6f", this)
