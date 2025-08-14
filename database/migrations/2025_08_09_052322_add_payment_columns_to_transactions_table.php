<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $t) {
            $t->string('payment_token')->nullable();
            $t->string('payment_redirect_url')->nullable();
            $t->unsignedInteger('gross_amount')->default(0);
            $t->timestamp('paid_at')->nullable();
            $t->timestamp('expired_at')->nullable();
            $t->json('midtrans_payload')->nullable();
            $t->string('sn')->nullable();         // isi ulang dari Digiflazz (opsional)
            $t->string('message')->nullable();    // keterangan dari Digiflazz (opsional)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $t) {
            $t->dropColumn([
                'payment_token',
                'payment_redirect_url',
                'gross_amount',
                'paid_at',
                'expired_at',
                'midtrans_payload',
                'sn',
                'message'
            ]);
        });
    }
};
