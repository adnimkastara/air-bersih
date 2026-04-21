package com.airbersih.mobile.network

import com.airbersih.mobile.BuildConfig
import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import java.util.concurrent.TimeUnit
import retrofit2.Retrofit
import retrofit2.converter.moshi.MoshiConverterFactory

object NetworkModule {

    fun apiService(tokenProvider: () -> String?): ApiService {
        val logging = HttpLoggingInterceptor().apply {
            level = HttpLoggingInterceptor.Level.BASIC
            redactHeader("Authorization")
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
            .connectTimeout(20, TimeUnit.SECONDS)
            .readTimeout(20, TimeUnit.SECONDS)
            .writeTimeout(20, TimeUnit.SECONDS)
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
