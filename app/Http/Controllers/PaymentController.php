<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Services\MidtransService;
use App\Services\DigiflazzService;
use App\Services\KirimiService;

class PaymentController extends Controller
{
    public function create(Request $r, MidtransService $mid)
    {
        // VALIDASI
        $r->validate([
            'ref_id' => 'required|string',
            'amount' => 'required|integer|min:1000',
            'customer' => 'array',
            'customer.first_name' => 'sometimes|string|max:60',
            'customer.email' => 'sometimes|email:strict,spoof,filter|regex:/@gmail\.com$/i',
            'customer.phone' => 'sometimes|regex:/^\d{11,14}$/',
            'items' => 'array',
            'items.*.id' => 'sometimes|string|max:100',
            'items.*.price' => 'sometimes|integer|min:0',
            'items.*.quantity' => 'sometimes|integer|min:1|max:9999',
            'items.*.name' => 'sometimes|string|max:200',
            'payments' => 'array',
        ]);

        $trx = Transaction::where('ref_id', $r->ref_id)->firstOrFail();

        // --- BUAT / SET INVOICE & SIMPAN KE DB---
        $invoice = $trx->invoice ?: 'INV-' . Str::upper(Str::random(12));
        $trx->invoice = $invoice;
        $trx->gross_amount = (int) $r->amount;
        $trx->payment_status = $trx->payment_status ?: 'UNPAID';
        $trx->payment_gateway = 'midtrans';

        // SIMPAN WA & NOMINAL ke transaksi
        $customerPhone = preg_replace('/\D+/', '', data_get($r->customer, 'phone', ''));
        if ($customerPhone) {
            $trx->customer_phone = $customerPhone;
        }
        $nominalLabel = data_get($r->items, '0.name');

        $trx->save(); 

        // --- Create Snap ---
        $snap = $mid->createSnap(
            $invoice,
            (int) $r->amount,
            [
                'first_name' => data_get($r->customer, 'first_name', 'User'),
                'email' => data_get($r->customer, 'email', 'user@example.com'),
                'phone' => $customerPhone ?: data_get($r->customer, 'phone'),
            ],
            $r->items ?? [
                [
                    'id' => $trx->sku,
                    'price' => (int) $r->amount,
                    'quantity' => 1,
                    'name' => $trx->nominal ?: ('Topup ' . $trx->sku),
                ]
            ],
            $r->payments ?? null
        );

        // Simpan token & redirect URL
        $trx->payment_token = $snap['token'] ?? null;
        $trx->payment_redirect_url = $snap['redirect_url'] ?? null;
        $trx->save();

        return response()->json([
            'invoice' => $invoice,
            'pay_url' => $trx->payment_redirect_url,
            'token' => $trx->payment_token,
            'customer_phone' => $trx->customer_phone,
            'nominal_label' => $trx->nominal,
        ]);
    }

    public function callback(Request $r, MidtransService $mid, DigiflazzService $digi)
    {
        $orderId = $r->input('order_id');
        $statusCode = $r->input('status_code');
        $grossAmount = $r->input('gross_amount');

        // a) Ambil transaksi
        $trx = Transaction::where('invoice', $orderId)->first();
        if (!$trx) {
            Log::warning('Callback for unknown invoice', ['invoice' => $orderId]);
            return response()->json(['ok' => true]);
        }

        // b) Verifikasi signature dll (diserahkan ke MidtransService)
        if (!$mid->verifySignature($r->all())) {
            Log::warning('Invalid Midtrans signature', ['invoice' => $orderId]);
            return response()->json(['ok' => false], 403);
        }

        $trxStatus = $r->input('transaction_status'); // settlement|capture|pending|deny|cancel|expire|refund
        $paymentType = $r->input('payment_type');

        $map = [
            'capture' => 'PAID',
            'settlement' => 'PAID',
            'pending' => 'UNPAID',
            'deny' => 'FAILED',
            'cancel' => 'FAILED',
            'expire' => 'EXPIRED',
            'refund' => 'REFUNDED',
            'partial_refund' => 'REFUNDED',
        ];
        $newStatus = $map[$trxStatus] ?? 'UNPAID';

        // Idempotent: jangan menurunkan status dari PAID
        if ($trx->payment_status !== 'PAID' || $newStatus === 'PAID') {
            $trx->payment_status = $newStatus;
        }
        $trx->payment_type = $paymentType ?: $trx->payment_type;
        $trx->midtrans_payload = $r->all();
        if ($newStatus === 'PAID' && !$trx->paid_at) {
            $trx->paid_at = now();
        }
        $trx->save();

        // c) Setelah PAID dan topup Digiflazz masih Pending → eksekusi order Digiflazz
        if ($newStatus === 'PAID' && $trx->status === 'Pending') {
            try {
                $result = $digi->createTransaction($trx->sku, $trx->customer_no, $trx->ref_id);
                $trx->status = data_get($result, 'data.status', 'Pending'); // 'Sukses'|'Gagal'|'Pending'
                $trx->response = $result;
                $trx->sn = data_get($result, 'data.sn');
                $trx->message = data_get($result, 'data.message');
                $trx->save();
            } catch (\Throwable $e) {
                Log::error('Digiflazz order error after PAID', ['ref_id' => $trx->ref_id, 'e' => $e->getMessage()]);
            }
        }

        return response()->json(['ok' => true]);
    }

    public function showByInvoice(string $invoice, DigiflazzService $digiflazz)
    {
        $trx = Transaction::where('invoice', $invoice)->firstOrFail();
        $oldStatus = $trx->status;

        // HANYA cek ke Digiflazz jika pembayaran sudah masuk tapi topup belum final
        if ($trx->payment_status === 'PAID' && !in_array($trx->status, ['Sukses', 'Gagal'])) {
            try {
                $res = $digiflazz->checkStatus($trx->ref_id); // <-- WAJIB: cek status, JANGAN re-order
                $data = $res['data'] ?? null;

                if ($data) {
                    $newStatus = $data['status'] ?? null; // 'Sukses' | 'Pending' | 'Gagal'
                    if (in_array($newStatus, ['Sukses', 'Pending', 'Gagal'])) {
                        $trx->status = $newStatus;
                    }
                    if (!empty($data['sn']))
                        $trx->sn = $data['sn'];
                    if (!empty($data['message']))
                        $trx->message = $data['message'];
                    $trx->save();
                    // [+] ADD THIS: kirim WA hanya saat status BARU menjadi "Sukses"
                    if ($oldStatus !== 'Sukses' && $trx->status === 'Sukses') {
                        try {
                            /** @var \App\Services\KirimiService $kirimi */
                            $kirimi = app(KirimiService::class);

                            // normalisasi nomor; ganti jika kamu sudah punya helper sendiri
                            $to = KirimiService::toE164ID($trx->customer_phone ?? '');
                            if ($to) {
                                $text = "Top-up *{$trx->sku}* untuk INV *{$trx->invoice}* berhasil.\n"
                                    . "SN: *" . ($trx->sn ?: '-') . "*.\n"
                                    . "Terima kasih telah bertransaksi di Syncpedia.";
                                $resp = $kirimi->sendText($to, $text);
                                \Log::info('kirimi.sent', ['invoice' => $trx->invoice, 'resp' => $resp]);
                            }
                        } catch (\Throwable $e) {
                            \Log::warning('kirimi.failed', [
                                'invoice' => $trx->invoice,
                                'err' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Digiflazz check failed', ['invoice' => $invoice, 'ref' => $trx->ref_id, 'err' => $e->getMessage()]);
            }
        }

        // Kembalikan payload yang dipakai Invoice.jsx
        return response()->json([
            'invoice' => $trx->invoice,
            'payment_status' => $trx->payment_status,   // UNPAID/PAID/EXPIRED/FAILED
            'payment_type' => $trx->payment_type,
            'gross_amount' => $trx->gross_amount,
            'topup_status' => $trx->status,           // FE baca ini
            'sn' => $trx->sn,
            'message' => $trx->message,
            'pay_url' => $trx->payment_redirect_url,
        ]);
    }
}
