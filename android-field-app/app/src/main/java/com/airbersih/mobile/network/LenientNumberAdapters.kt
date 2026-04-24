package com.airbersih.mobile.network

import com.squareup.moshi.FromJson
import com.squareup.moshi.JsonDataException
import com.squareup.moshi.JsonReader
import com.squareup.moshi.ToJson

class LenientDoubleAdapter {
    @FromJson
    fun fromJson(reader: JsonReader): Double? {
        return when (reader.peek()) {
            JsonReader.Token.NULL -> {
                reader.nextNull<Unit>()
                null
            }
            JsonReader.Token.NUMBER -> reader.nextDouble()
            JsonReader.Token.STRING -> reader.nextString().toDoubleOrNull()
            else -> throw JsonDataException("Expected double but was ${reader.peek()}")
        }
    }

    @ToJson
    fun toJson(value: Double?): Double? = value
}

class LenientLongAdapter {
    @FromJson
    fun fromJson(reader: JsonReader): Long? {
        return when (reader.peek()) {
            JsonReader.Token.NULL -> {
                reader.nextNull<Unit>()
                null
            }
            JsonReader.Token.NUMBER -> reader.nextLong()
            JsonReader.Token.STRING -> reader.nextString().toLongOrNull()
            else -> throw JsonDataException("Expected long but was ${reader.peek()}")
        }
    }

    @ToJson
    fun toJson(value: Long?): Long? = value
}
