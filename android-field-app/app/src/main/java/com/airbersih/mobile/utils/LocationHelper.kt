package com.airbersih.mobile.utils

import android.annotation.SuppressLint
import android.content.Context
import android.location.Location
import android.util.Log
import com.google.android.gms.location.FusedLocationProviderClient
import com.google.android.gms.location.LocationServices
import kotlinx.coroutines.suspendCancellableCoroutine
import kotlin.coroutines.resume

class LocationHelper(context: Context) {
    private val fusedClient: FusedLocationProviderClient =
        LocationServices.getFusedLocationProviderClient(context)

    @SuppressLint("MissingPermission")
    suspend fun getCurrentLocationOrNull(): Location? = suspendCancellableCoroutine { cont ->
        try {
            fusedClient.lastLocation
                .addOnSuccessListener { location -> cont.resume(location) }
                .addOnFailureListener {
                    Log.w("LocationHelper", "Cannot fetch last location", it)
                    cont.resume(null)
                }
        } catch (e: SecurityException) {
            Log.w("LocationHelper", "Location permission unavailable", e)
            cont.resume(null)
        } catch (e: Exception) {
            Log.w("LocationHelper", "Unexpected location error", e)
            cont.resume(null)
        }
    }
}
