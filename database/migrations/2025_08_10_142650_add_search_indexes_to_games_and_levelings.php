<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->index('name');
            $table->index('publisher');
            $table->index('slug');
        });
        Schema::table('levelings', function (Blueprint $table) {
            $table->index('name');
            $table->index('publisher');
            $table->index('slug');
        });
    }
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['publisher']);
            $table->dropIndex(['slug']);
        });
        Schema::table('levelings', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['publisher']);
            $table->dropIndex(['slug']);
        });
    }
};
