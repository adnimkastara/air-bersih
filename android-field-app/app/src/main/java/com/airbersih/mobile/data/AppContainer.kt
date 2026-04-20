package com.airbersih.mobile.data

import android.content.Context
import com.airbersih.mobile.network.NetworkModule
import com.airbersih.mobile.repository.MainRepository

class AppContainer(context: Context) {
    private val tokenManager = TokenManager(context)
    @Volatile
    private var tokenCache: String? = null

    suspend fun init() {
        tokenManager.tokenFlow.collect { token ->
            tokenCache = token
        }
    }

    val repository: MainRepository by lazy {
        MainRepository(
            api = NetworkModule.apiService { tokenCache },
            tokenManager = tokenManager
        )
    }
}
