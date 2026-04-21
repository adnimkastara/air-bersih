package com.airbersih.mobile.utils

import android.util.Log

object MenuLogger {
    const val TAG_NAV = "MENU_NAV"
    const val TAG_API = "MENU_API"
    const val TAG_ERROR = "MENU_ERROR"

    fun nav(message: String) = Log.d(TAG_NAV, message)
    fun api(message: String) = Log.d(TAG_API, message)
    fun error(message: String, throwable: Throwable? = null) = Log.e(TAG_ERROR, message, throwable)
}
