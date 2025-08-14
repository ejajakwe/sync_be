<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('payment_methods')->truncate();

        DB::table('payment_methods')->insert([
            [
                'name' => 'Dana',
                'type' => 'ewallet',
                'logo' => '/images/payments/dana.png',
                'fee_percent' => 0.5,
            ],
            [
                'name' => 'ShopeePay',
                'type' => 'ewallet',
                'logo' => '/images/payments/shopee.png',
                'fee_percent' => 0.5,
            ],
            [
                'name' => 'Gopay',
                'type' => 'ewallet',
                'logo' => '/images/payments/gopay.png',
                'fee_percent' => 0.5,
            ],
            // Tambah bank lain sesuai kebutuhan
        ]);
    }
}
