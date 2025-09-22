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
        Schema::create('inventory_reports', function (Blueprint $table) {
            $table->id();
            $table->string('am_sku_id')->nullable();
            $table->string('shopify_sku_id')->nullable();
            $table->string('product_name')->nullable();
            $table->string("am_quantity_available")->nullable();
            $table->string('shopify_quantity_available')->nullable();
            $table->string('shopify_barcode')->nullable();
            $table->string('am_upc_display')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_reports');
    }
};
