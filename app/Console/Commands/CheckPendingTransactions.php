<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Services\DigiflazzService;

class CheckPendingTransactions extends Command
{
    protected $signature = 'transactions:check';
    protected $description = 'Cek ulang status semua transaksi Pending dari Digiflazz';

    public function handle(DigiflazzService $digiflazz)
    {
        $pending = Transaction::where('status', 'Pending')->get();

        foreach ($pending as $trx) {
            $result = $digiflazz->checkStatus($trx->sku, $trx->customer_no, $trx->ref_id);

            $trx->update([
                'status' => $result['data']['status'] ?? 'Pending',
                'response' => $result,
            ]);

            $this->info("Updated trx {$trx->ref_id}: " . ($result['data']['status'] ?? 'Unknown'));
        }

        return Command::SUCCESS;
    }
}