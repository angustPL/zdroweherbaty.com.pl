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
        Schema::create('promotion_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->string('group_path'); // Ścieżka grupy (np. "\\kategoria\\Bi fix herbaty czarne\\")
            $table->timestamps();

            // ===== INDEKSY =====
            $table->index('promotion_id');
            $table->index('group_path');
            $table->unique(['promotion_id', 'group_path']); // Unikalna kombinacja
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_groups');
    }
};
