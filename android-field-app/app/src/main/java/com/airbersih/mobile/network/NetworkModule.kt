package com.airbersih.mobile.network

import com.airbersih.mobile.BuildConfig
import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.moshi.MoshiConverterFactory

object NetworkModule {

    fun apiService(tokenProvider: () -> String?): ApiService {
        val logging = HttpLoggingInterceptor().apply {
            level = HttpLoggingInterceptor.Level.BODY
        }

        val auth = Interceptor { chain ->
            val reqBuilder = chain.request().newBuilder()
            tokenProvider()?.let { token ->
                reqBuilder.addHeader("Authorization", "Bearer $token")
            }
            reqBuilder.addHeader("Accept", "application/json")
            chain.proceed(reqBuilder.build())
        }

        val client = OkHttpClient.Builder()
            .addInterceptor(auth)
            .addInterceptor(logging)
            .build()

        val baseUrl = if (BuildConfig.USE_PROD) BuildConfig.BASE_URL_PROD else BuildConfig.BASE_URL_DEV

        return Retrofit.Builder()
            .baseUrl(baseUrl)
            .addConverterFactory(MoshiConverterFactory.create())
            .client(client)
            .build()
            .create(ApiService::class.java)
    }
}
