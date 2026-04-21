package com.airbersih.mobile.navigation

import android.app.Application
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.platform.LocalContext
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavGraph.Companion.findStartDestination
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import com.airbersih.mobile.ui.screens.*
import com.airbersih.mobile.viewmodel.MainViewModel

object Routes {
    const val Login = "login"
    const val Dashboard = "dashboard"
    const val Pelanggan = "pelanggan"
    const val Meter = "meter"
    const val Tagihan = "tagihan"
    const val Pembayaran = "pembayaran"
    const val Keluhan = "keluhan"
    const val Monitoring = "monitoring"
}

@Composable
fun AppNavGraph(
    vm: MainViewModel = viewModel(
        factory = ViewModelProvider.AndroidViewModelFactory(
            LocalContext.current.applicationContext as Application
        )
    )
) {
    val nav = rememberNavController()
    val loggedIn by vm.isLoggedIn.collectAsState()

    LaunchedEffect(loggedIn) {
        val target = if (loggedIn) Routes.Dashboard else Routes.Login
        if (nav.currentDestination?.route != target) {
            nav.navigate(target) {
                popUpTo(nav.graph.findStartDestination().id) { inclusive = true }
                launchSingleTop = true
            }
        }
    }

    NavHost(
        navController = nav,
        startDestination = if (loggedIn) Routes.Dashboard else Routes.Login
    ) {
        composable(Routes.Login) { LoginScreen(vm) }
        composable(Routes.Dashboard) {
            DashboardScreen(
                vm,
                onOpenPelanggan = { nav.navigate(Routes.Pelanggan) },
                onOpenMeter = { nav.navigate(Routes.Meter) },
                onOpenTagihan = { nav.navigate(Routes.Tagihan) },
                onOpenPembayaran = { nav.navigate(Routes.Pembayaran) },
                onOpenKeluhan = { nav.navigate(Routes.Keluhan) },
                onOpenMonitoring = { nav.navigate(Routes.Monitoring) },
                onLogout = { vm.logout() }
            )
        }
        composable(Routes.Pelanggan) { PelangganScreen(vm) }
        composable(Routes.Meter) { MeterScreen(vm) }
        composable(Routes.Tagihan) { TagihanScreen(vm) }
        composable(Routes.Pembayaran) { PembayaranScreen(vm) }
        composable(Routes.Keluhan) { KeluhanScreen(vm) }
        composable(Routes.Monitoring) { MonitoringScreen(vm) }
    }
}
