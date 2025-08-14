<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class KirimiService
{
    private string $base;
    private string $user;
    private string $device;
    private string $secret;

    public function __construct()
    {
        $cfg = config('services.kirimi');
        $this->base = rtrim($cfg['base'], '/');
        $this->user = $cfg['user_code'];
        $this->device = $cfg['device_id'];
        $this->secret = $cfg['secret'];
    }

    public function sendText(string $toE164, string $message): array
    {
        // Endpoint: POST /send-message
        // Body: { user_code, device_id, receiver, message, secret }
        $res = Http::asJson()->post($this->base . '/send-message', [
            'user_code' => $this->user,
            'device_id' => $this->device,
            'receiver' => $toE164,
            'message' => $message,
            'secret' => $this->secret,
        ]);

        return $res->json();
    }

    public function sendDocument(string $toE164, string $caption, string $fileUrl, string $filename): array
    {
        // Endpoint: POST /send-doc
        // Body: { user_code, device_id, receiver, message, media, filename, secret }
        $res = Http::asJson()->post($this->base . '/send-doc', [
            'user_code' => $this->user,
            'device_id' => $this->device,
            'receiver' => $toE164,
            'message' => $caption,
            'media' => $fileUrl,
            'filename' => $filename,
            'secret' => $this->secret,
        ]);

        return $res->json();
    }

    // kecil: normalisasi nomor ke E.164 (ID: 08xxxx -> 628xxxx)
    public static function toE164ID(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw);
        if (str_starts_with($digits, '0'))
            return '62' . substr($digits, 1);
        if (str_starts_with($digits, '62'))
            return $digits;
        return $digits; // fallback bila sudah internasional
    }
}
