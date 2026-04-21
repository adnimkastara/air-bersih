package com.airbersih.mobile.utils

import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import java.util.TimeZone

object DateTimeUtils {
    private const val ISO_DATE_PATTERN = "yyyy-MM-dd"

    fun todayIsoDate(): String {
        val formatter = SimpleDateFormat(ISO_DATE_PATTERN, Locale.US).apply {
            timeZone = TimeZone.getDefault()
        }
        return formatter.format(Date())
    }
}
