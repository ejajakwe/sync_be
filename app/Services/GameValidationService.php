<?php

namespace App\Services;

use GuzzleHttp\Client;

class GameValidationService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'timeout' => 12,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0',
            ],
        ]);
    }

    /** --- DuniaGames khusus Mobile Legends --- */
    public function checkDuniaGamesML(string $userId, string $zoneId): array
    {
        $payload = [
            'productId' => '1',
            'itemId' => '2',
            'catalogId' => '57',
            'paymentId' => '352',
            'gameId' => $userId,
            'zoneId' => $zoneId,
            'product_ref' => 'REG',
            'product_ref_denom' => 'AE',
        ];

        try {
            $res = $this->http->post(
                'https://api.duniagames.co.id/api/transaction/v1/top-up/inquiry/store',
                [
                    'form_params' => $payload,
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded']
                ]
            );
            $json = json_decode((string) $res->getBody(), true);
            $name = $json['data']['gameDetail']['userName'] ?? null;

            return $name
                ? ['ok' => true, 'username' => $name]
                : ['ok' => false, 'message' => 'User tidak ditemukan'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Gagal memvalidasi (DuniaGames).'];
        }
    }

    /** --- Vocagame untuk game lain ---
     * $code: MOBILE_LEGENDS, FREEFIRE, GENSHIN_IMPACT, CODM, HONOR_OF_KINGS, PUBG_MOBILE, dll.
     * zoneId opsional, beberapa game memang tidak memakai zone.
     */
    public function checkVocagame(string $code, string $userId, ?string $zoneId = null): array
    {
        $url = "https://api.vocagame.com/v1/order/prepare/{$code}";
        $qs = http_build_query(['userId' => $userId, 'zoneId' => $zoneId ?? 'undefined']);

        try {
            $res = $this->http->get("{$url}?{$qs}");
            $raw = (string) $res->getBody();
            $json = json_decode($raw, true);

            \Log::info('[Vocagame] ' . $code . ' userId=' . $userId . ' zone=' . ($zoneId ?? 'undefined'), ['raw' => $raw]);

            $name = null;

            // CASE 1: data = "USERNAME" (string langsung)
            if (isset($json['data']) && is_string($json['data'])) {
                $name = $json['data'];
            }

            // CASE 2: data = {...} (object berisi berbagai kemungkinan key)
            if (!$name && is_array($json['data'] ?? null)) {
                $d = $json['data'];
                $name = $d['userName']
                    ?? $d['username']
                    ?? $d['nickName']
                    ?? $json['userName']
                    ?? $json['username']
                    ?? null;
            }

            // Rapikan whitespace aneh (ZWSP, dsb)
            if (is_string($name)) {
                // hapus karakter whitespace non-breaking/ZWSP, lalu normalisasi spasi
                $name = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $name);
                $name = trim(preg_replace('/\s+/u', ' ', $name));
            }

            return $name
                ? ['ok' => true, 'username' => $name]
                : ['ok' => false, 'message' => 'User tidak ditemukan (Vocagame).'];
        } catch (\Throwable $e) {
            \Log::warning('[Vocagame ERROR] ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Gagal memvalidasi (Vocagame).'];
        }
    }
}