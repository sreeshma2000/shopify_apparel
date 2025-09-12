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
        Schema::create('am_order_items', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->nullable();
            $table->string('product_id')->nullable();
            $table->string('row_id')->nullable();
            $table->string('sku_id')->nullable();
            $table->string('sku_alt')->nullable();
            $table->string('upc')->nullable();
            $table->string('style_number')->nullable();
            $table->longText('description')->nullable();
            $table->string('size')->nullable();
            $table->string('qty')->nullable();
            $table->string('is_taxable')->nullable();
            $table->string('amount')->nullable();
            $table->string('unit_price')->nullable();
            $table->string('warehouse_id')->nullable();
            $table->string('shopify_order_id')->nullable();
            $table->string('shopify_order_name')->nullable();
            $table->string('shopify_sku')->nullable();
            $table->string('qty_picked')->nullable();
            $table->string('qty_cancelled')->nullable();
            $table->string('qty_shipped')->nullable();
            $table->string('date_due')->nullable();
            $table->string('shopify_order_gid')->nullable();
            $table->string('shopify_line_item_id')->nullable();
            $table->string('shopify_title')->nullable();
            $table->string('shopify_quantity')->nullable();
            $table->string('shopify_current_quantity')->nullable();
            $table->string('shopify_variant_id')->nullable();
            $table->string('shopify_variant_title')->nullable();
            $table->string('shopify_fulfillment_order_id')->nullable();
            $table->string('shopify_amount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('am_order_items');
    }
};
