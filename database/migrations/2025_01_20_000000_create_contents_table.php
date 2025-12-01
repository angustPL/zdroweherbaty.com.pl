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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();

            // Typ treści: 'terms', 'product_group', 'custom'
            $table->string('type', 50)->index();

            // Identyfikator treści (nazwa, slug, ID grupy)
            // Dla 'terms': 'regulamin', 'polityka-prywatnosci'
            // Dla 'product_group': ID grupy produktów
            // Dla 'custom': dowolny identyfikator
            $table->string('identifier', 255)->index();

            // Unikalna kombinacja type + identifier
            $table->unique(['type', 'identifier']);

            // Tytuł treści
            $table->string('title')->nullable();

            // Treść (HTML/text)
            $table->longText('content');

            // Czy treść jest aktywna
            $table->boolean('is_active')->default(true)->index();

            // Meta dane (opcjonalne, JSON)
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
