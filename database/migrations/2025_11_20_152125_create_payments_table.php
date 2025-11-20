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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // ===== POWIĄZANIE Z ZAMÓWIENIEM =====
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            // ===== METODA PŁATNOŚCI =====
            $table->string('payment_method'); // payu_blik, payu_card, cash, etc.
            $table->string('payment_method_guid')->nullable(); // GUID metody płatności z Enova

            // ===== PAYU =====
            $table->string('payu_order_id')->nullable()->unique(); // ID zamówienia w PayU
            $table->string('payu_option')->nullable(); // Opcja PayU (blik, c, ap, jp, przelew)
            $table->json('payu_data')->nullable(); // Pełna odpowiedź z PayU

            // ===== IDENTYFIKATORY =====
            $table->string('ext_order_id', 36)->nullable(); // GUID zamówienia (dla szybkiego wyszukiwania)

            // ===== STATUS I KWOTY =====
            $table->string('status')->default('pending'); // pending, completed, failed, waiting_for_confirmation
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('PLN');

            // ===== DODATKOWE INFORMACJE =====
            $table->integer('termin_dni')->default(0); // Termin płatności w dniach
            $table->timestamp('paid_at')->nullable(); // Data i czas opłacenia
            $table->text('failure_reason')->nullable(); // Przyczyna niepowodzenia płatności

            $table->timestamps();

            // ===== INDEKSY =====
            $table->index('order_id');
            $table->index('status');
            $table->index('payu_order_id');
            $table->index('ext_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
