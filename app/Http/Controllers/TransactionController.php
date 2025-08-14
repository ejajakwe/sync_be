<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Services\DigiflazzService;

class TransactionController extends Controller
{
    public function store(Request $request, DigiflazzService $digiflazz)
    {
        // $request->validate([
        //     'sku' => 'required|string',
        //     'customer_no' => 'required|string',
        // ]);

        // $refId = uniqid('trx_');

        // $result = $digiflazz->createTransaction(
        //     $request->sku,
        //     $request->customer_no,
        //     $refId
        // );

        // $transaction = Transaction::create([
        //     'ref_id' => $refId,
        //     'sku' => $request->sku,
        //     'customer_no' => $request->customer_no,
        //     'status' => $result['data']['status'] ?? 'Pending',
        //     'response' => $result,
        // ]);

        // return response()->json($transaction, 201);
    }

    public function check(Request $request, $ref_id, DigiflazzService $digiflazz)
    {
        $trx = Transaction::where('ref_id', $ref_id)->firstOrFail();

        $result = $digiflazz->checkStatus($ref_id);
        $trx->update([
            'status' => $result['data']['status'] ?? 'Pending',
            'response' => $result
        ]);

        return response()->json($trx);
    }

    public function draft(Request $r)
    {
        $r->validate([
            'sku' => 'required|string',
            'customer_no' => 'required|string',
        ]);

        // ref_id unik untuk Digiflazz & korelasi internal
        $refId = 'trx_' . bin2hex(random_bytes(6));

        $trx = Transaction::create([
            'ref_id' => $refId,
            'sku' => $r->sku,
            'customer_no' => $r->customer_no,
            'status' => 'Pending',   // Digiflazz status
            'payment_status' => 'UNPAID',    // status pembayaran (Midtrans)
        ]);

        return response()->json([
            'ref_id' => $trx->ref_id,
            'message' => 'Draft transaksi dibuat. Lanjutkan ke pembayaran.',
        ], 201);
    }

}