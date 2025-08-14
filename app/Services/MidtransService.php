<?php
namespace App\Services;

use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class MidtransService
{
  public function __construct()
  {
    MidtransConfig::$serverKey = config('services.midtrans.server_key');
    MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production', false);
    MidtransConfig::$isSanitized = true;
    MidtransConfig::$is3ds = true;
  }

  public function createSnap(string $orderId, int $amount, array $customer, array $items, ?array $enabledPayments = null): array
  {
    $params = [
      'transaction_details' => ['order_id' => $orderId, 'gross_amount' => $amount],
      'customer_details' => $customer,     // ['first_name','email','phone']
      'item_details' => $items,        // [['id','price','quantity','name']]
    ];
    if ($enabledPayments)
      $params['enabled_payments'] = $enabledPayments;

    $snap = Snap::createTransaction($params);
    return ['token' => $snap->token, 'redirect_url' => $snap->redirect_url];
  }

  public function validSignature(string $orderId, string $statusCode, string $grossAmount, string $sig): bool
  {
    $key = config('services.midtrans.server_key');
    $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $key);
    return hash_equals($expected, $sig);
  }

  // app/Services/MidtransService.php
  public function verifySignature(array $payload): bool
  {
    $orderId = $payload['order_id'] ?? '';
    $statusCode = $payload['status_code'] ?? '';
    $grossAmount = $payload['gross_amount'] ?? '';
    $signature = $payload['signature_key'] ?? '';
    return $this->validSignature($orderId, $statusCode, $grossAmount, $signature);
  }
}