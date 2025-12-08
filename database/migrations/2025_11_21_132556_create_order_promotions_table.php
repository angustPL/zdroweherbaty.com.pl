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
        if (!Schema::hasTable('order_promotions')) {
            Schema::create('order_promotions', function (Blueprint $table) {
                $table->id();

                // ===== POWIĄZANIA =====
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->foreignId('promotion_id')->constrained()->onDelete('cascade');

                $table->timestamps();

                // ===== INDEKSY =====
                $table->index('order_id');
                $table->index('promotion_id');
                $table->unique(['order_id', 'promotion_id']); // Unikalna kombinacja
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_promotions');
    }
};

