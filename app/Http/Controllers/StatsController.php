<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;

class StatsController extends Controller
{
    // GET /api/admin/transactions/recent?limit=10
    public function recent(Request $r)
    {
        $limit = (int) $r->query('limit', 10);

        $rows = Transaction::orderByDesc('created_at')
            ->limit($limit)
            ->get([
                'invoice',
                'customer_no',     // no HP
                'gross_amount',    // harga
                'payment_status',  // status
                'created_at',
            ]);

        return response()->json($rows);
    }
}
