<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'ref_id',
        'sku',
        'customer_no',
        'status',
        'response',
        'invoice',
        'payment_status',
        'payment_gateway',
        'payment_type',
        'payment_token',
        'payment_redirect_url',
        'gross_amount',
        'paid_at',
        'expired_at',
        'midtrans_payload',
        'sn',
        'message',
        'customer_phone',
    ];

    protected $casts = [
        'response' => 'array',
    ];
}