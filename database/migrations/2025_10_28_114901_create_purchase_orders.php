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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('sku_id');
            $table->date('date_start')->nullable();
            $table->date('date_due')->nullable();
            $table->date('date_ex_factory')->nullable();
            $table->decimal('qty', 12, 2);
            $table->decimal('qty_open', 12, 2);
            $table->decimal('qty_cxl', 12, 2)->default(0);
            $table->decimal('qty_in_transit', 12, 2)->default(0);
            $table->decimal('qty_received', 12, 2)->default(0);
            $table->string('style_number')->nullable();
            $table->string('description')->nullable();
            $table->string('size')->nullable();
            $table->string('upc')->nullable();
            $table->string('upc_display')->nullable();
            $table->string('sku_alt')->nullable();
            $table->decimal('unit_cost', 12, 4)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->boolean('is_taxable')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
