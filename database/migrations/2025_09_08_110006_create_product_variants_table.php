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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->nullable();
            $table->string('sku_id')->nullable();
            $table->string('sku_concat')->nullable();
            $table->string('sku_alt')->nullable();
            $table->string('style_number')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('upc_display')->nullable();
            $table->string('price')->nullable();
            $table->string('shopify_product_id')->nullable();
            $table->string('shopify_variant_id')->nullable();
            $table->string('shopify_inventory_item_id')->nullable();
            $table->string('inventory_item_gid')->nullable();
            $table->string('inventory_level_gid')->nullable();
            $table->string('shopify_barcode')->nullable();
            $table->string('shopify_sku')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
