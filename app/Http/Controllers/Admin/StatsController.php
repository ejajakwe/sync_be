<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    // GET /api/admin/transactions/recent?limit=10
    public function recent(Request $r)
    {
        $limit = (int) $r->query('limit', 10);

        $rows = DB::table('transactions as t')
            ->leftJoin('nominals as n', 'n.sku_code', '=', 't.sku')
            ->orderByDesc('t.created_at')
            ->limit($limit)
            ->get([
                't.invoice',
                't.customer_no',
                't.gross_amount',
                't.payment_status',
                't.created_at',
                't.customer_phone',
                DB::raw('COALESCE(n.label, "") as nominal'),
            ]);

        return response()->json($rows);
    }
}
