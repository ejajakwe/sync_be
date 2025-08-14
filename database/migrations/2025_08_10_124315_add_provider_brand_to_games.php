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
        Schema::table('games', function (Blueprint $t) {
            if (!Schema::hasColumn('games', 'provider'))
                $t->string('provider', 30)->nullable()->index();
            if (!Schema::hasColumn('games', 'provider_brand'))
                $t->string('provider_brand', 120)->nullable()->unique();
        });
        Schema::table('nominals', function (Blueprint $t) {
            if (!Schema::hasColumn('nominals', 'sku'))
                $t->string('sku', 120)->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            //
        });
    }
};
