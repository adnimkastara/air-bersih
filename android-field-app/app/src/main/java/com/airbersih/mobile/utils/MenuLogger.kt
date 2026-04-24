package com.airbersih.mobile.utils

import android.util.Log

object MenuLogger {
    const val TAG_NAV = "MENU_NAV"
    const val TAG_API = "MENU_API"
    const val TAG_ERROR = "MENU_ERROR"
    const val TAG_FEATURE_FLOW = "FEATURE_FLOW"
    const val TAG_FORM_STATE = "FORM_STATE"
    const val TAG_API_PAYLOAD = "API_PAYLOAD"
    const val TAG_UI_STATE = "UI_STATE"
    const val TAG_SESSION_STATE = "SESSION_STATE"
    const val TAG_MAP_FLOW = "MAP_FLOW"
    const val TAG_MAP_MARKERS = "MAP_MARKERS"
    const val TAG_CUSTOMER_FORM = "CUSTOMER_FORM"
    const val TAG_KELUHAN_PAYLOAD = "KELUHAN_PAYLOAD"
    const val TAG_KELUHAN_ERROR = "KELUHAN_ERROR"

    fun nav(message: String) = Log.d(TAG_NAV, message)
    fun api(message: String) = Log.d(TAG_API, message)
    fun error(message: String, throwable: Throwable? = null) = Log.e(TAG_ERROR, message, throwable)
    fun feature(message: String) = Log.d(TAG_FEATURE_FLOW, message)
    fun form(message: String) = Log.d(TAG_FORM_STATE, message)
    fun payload(message: String) = Log.d(TAG_API_PAYLOAD, message)
    fun ui(message: String) = Log.d(TAG_UI_STATE, message)
    fun session(message: String) = Log.d(TAG_SESSION_STATE, message)
    fun mapFlow(message: String) = Log.d(TAG_MAP_FLOW, message)
    fun mapMarkers(message: String) = Log.d(TAG_MAP_MARKERS, message)
    fun customerForm(message: String) = Log.d(TAG_CUSTOMER_FORM, message)
    fun keluhanPayload(message: String) = Log.d(TAG_KELUHAN_PAYLOAD, message)
    fun keluhanError(message: String, throwable: Throwable? = null) = Log.e(TAG_KELUHAN_ERROR, message, throwable)
}
