<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DigiflazzService
{
    protected $username;
    protected $apiKey;
    protected $endpoint;

    public function __construct()
    {
        $this->username = config('services.digiflazz.username');
        $this->apiKey = config('services.digiflazz.api_key');
        $this->endpoint = config('services.digiflazz.endpoint');
    }

    protected function generateSignature($refId)
    {
        return md5($this->username . $this->apiKey . $refId);
    }

    public function createTransaction($sku, $customer, $refId)
    {
        $payload = [
            "username" => $this->username,
            "buyer_sku_code" => $sku,
            "customer_no" => $customer,
            "ref_id" => $refId,
            "sign" => $this->generateSignature($refId),
        ];

        $response = Http::post($this->endpoint, $payload);

        return $response->json();
    }

    public function checkStatus(string $refId): array
    {
        $payload = [
            'username' => $this->username,
            'ref_id' => $refId,
            'sign' => $this->generateSignature($refId), // md5(username+apiKey+ref_id)
        ];

        $response = Http::post($this->endpoint, $payload);
        return $response->json();
    }
}