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
        Schema::create('size_ranges', function (Blueprint $table) {
            $table->id();
            $table->string('size_range_id');
            $table->string('name');
            $table->tinyInteger('is_product')->nullable();
            $table->tinyInteger('is_component')->nullable();
            $table->json('sizes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('size_ranges');
    }
};
