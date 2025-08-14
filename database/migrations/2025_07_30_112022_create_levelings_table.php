<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLevelingsTable extends Migration
{
    public function up()
    {
        Schema::create('levelings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->nullable(); // optional, untuk URL friendly
            $table->string('publisher')->nullable();
            $table->string('image_url')->nullable();       // logo
            $table->string('header_image_url')->nullable(); // banner
            $table->longText('fields')->nullable();        // form akun (JSON)
            $table->longText('payment_methods')->nullable(); // payment (JSON)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('levelings');
    }
}