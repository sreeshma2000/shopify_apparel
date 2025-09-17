<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('am_returns', function (Blueprint $table) {
            $table->id();
            $table->string('shopify_order_id');
            $table->string('am_order_id');
            $table->string('return_authorisation_id');
            $table->string('credit_memo_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('am_returns');
    }
};
