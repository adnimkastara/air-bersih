<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function sendMessage(string $phone, string $message): bool
    {
        $endpoint = (string) config('services.whatsapp.endpoint');
        $token = (string) config('services.whatsapp.token');

        if ($endpoint === '' || $token === '') {
            Log::warning('WhatsApp gateway belum dikonfigurasi.', [
                'phone' => $phone,
            ]);

            return false;
        }

        try {
            $response = Http::timeout(15)
                ->withToken($token)
                ->post($endpoint, [
                    'phone' => $phone,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('Gagal mengirim WhatsApp.', [
                'phone' => $phone,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error saat mengirim WhatsApp.', [
                'phone' => $phone,
                'error' => $exception->getMessage(),
            ]);
        }

        return false;
    }
}
