package com.airbersih.mobile.navigation

import android.app.Application
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.platform.LocalContext
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import com.airbersih.mobile.ui.screens.DashboardScreen
import com.airbersih.mobile.ui.screens.KeluhanScreen
import com.airbersih.mobile.ui.screens.LoginScreen
import com.airbersih.mobile.ui.screens.MeterScreen
import com.airbersih.mobile.ui.screens.MonitoringScreen
import com.airbersih.mobile.ui.screens.PelangganScreen
import com.airbersih.mobile.ui.screens.PembayaranScreen
import com.airbersih.mobile.ui.screens.ProfileScreen
import com.airbersih.mobile.ui.screens.TagihanScreen
import com.airbersih.mobile.utils.MenuLogger
import com.airbersih.mobile.viewmodel.MainViewModel

object Routes {
    const val Loading = "loading"
    const val Login = "login"
    const val Dashboard = "dashboard"
    const val Pelanggan = "pelanggan"
    const val Meter = "meter"
    const val Tagihan = "tagihan"
    const val Pembayaran = "pembayaran"
    const val Keluhan = "keluhan"
    const val Monitoring = "monitoring"
    const val Profile = "profile"
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

    fun navigateMenu(menuName: String, route: String) {
        MenuLogger.nav("menu_clicked=$menuName target_route=$route")
        nav.navigate(route)
    }

    LaunchedEffect(loggedIn) {
        val target = if (loggedIn) Routes.Dashboard else Routes.Login
        val currentRoute = nav.currentBackStackEntry?.destination?.route
        if (currentRoute == target) return@LaunchedEffect

        MenuLogger.nav("auth_route_change from=${currentRoute ?: "none"} to=$target")
        nav.navigate(target) {
            popUpTo(nav.graph.startDestinationId) { inclusive = true }
            launchSingleTop = true
        }
    }

    NavHost(
        navController = nav,
        startDestination = Routes.Loading
    ) {
        composable(Routes.Loading) { }
        composable(Routes.Login) { LoginScreen(vm) }
        composable(Routes.Dashboard) {
            MenuLogger.nav("screen_opened=dashboard")
            DashboardScreen(
                vm,
                onOpenPelanggan = { navigateMenu("pelanggan", Routes.Pelanggan) },
                onOpenMeter = { navigateMenu("meter", Routes.Meter) },
                onOpenTagihan = { navigateMenu("tagihan", Routes.Tagihan) },
                onOpenPembayaran = { navigateMenu("pembayaran", Routes.Pembayaran) },
                onOpenKeluhan = { navigateMenu("keluhan", Routes.Keluhan) },
                onOpenMonitoring = { navigateMenu("monitoring", Routes.Monitoring) },
                onOpenProfile = { navigateMenu("profile", Routes.Profile) },
                onLogout = { vm.logout() }
            )
        }
        composable(Routes.Pelanggan) { MenuLogger.nav("screen_opened=pelanggan"); PelangganScreen(vm) }
        composable(Routes.Meter) { MenuLogger.nav("screen_opened=meter"); MeterScreen(vm) }
        composable(Routes.Tagihan) { MenuLogger.nav("screen_opened=tagihan"); TagihanScreen(vm) }
        composable(Routes.Pembayaran) { MenuLogger.nav("screen_opened=pembayaran"); PembayaranScreen(vm) }
        composable(Routes.Keluhan) { MenuLogger.nav("screen_opened=keluhan"); KeluhanScreen(vm) }
        composable(Routes.Monitoring) { MenuLogger.nav("screen_opened=monitoring"); MonitoringScreen(vm) }
        composable(Routes.Profile) { MenuLogger.nav("screen_opened=profile"); ProfileScreen(vm) }
    }
}
