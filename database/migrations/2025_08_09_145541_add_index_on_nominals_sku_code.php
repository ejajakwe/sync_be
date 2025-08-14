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
        Schema::table('nominals', function (Blueprint $table) {
            Schema::table('nominals', function (Blueprint $table) {
                $table->index('sku_code');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nominals', function (Blueprint $table) {
            Schema::table('nominals', function (Blueprint $table) {
                $table->dropIndex(['sku_code']);
            });
        });
    }
};
