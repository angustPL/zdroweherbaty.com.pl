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
        Schema::create('promotion_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('product_id'); // ID produktu z Enova (Towary.ID)
            $table->timestamps();

            // ===== INDEKSY =====
            $table->index('promotion_id');
            $table->index('product_id');
            $table->unique(['promotion_id', 'product_id']); // Unikalna kombinacja
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_products');
    }
};
