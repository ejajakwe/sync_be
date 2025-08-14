<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('nominals', function (Blueprint $table) {
            $table->unsignedBigInteger('leveling_id')->nullable()->after('id');

            $table->foreign('leveling_id')
                ->references('id')
                ->on('levelings')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('nominals', function (Blueprint $table) {
            $table->dropForeign(['leveling_id']);
            $table->dropColumn('leveling_id');
        });
    }
};
