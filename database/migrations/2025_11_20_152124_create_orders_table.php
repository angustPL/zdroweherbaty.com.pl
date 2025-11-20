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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // ===== IDENTYFIKATORY =====
            // GUID zamówienia (ext_order_id dla PayU, Guid w Enova)
            $table->string('ext_order_id', 36)->unique()->nullable();

            // ===== STATUS =====
            $table->string('status')->default('pending'); // pending, processing, completed, cancelled

            // ===== DANE KLIENTA =====
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();

            // ===== ADRES DOSTAWY =====
            $table->string('delivery_street');
            $table->string('delivery_street_number', 10);
            $table->string('delivery_apartment', 10)->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_postal_code', 10);
            $table->string('delivery_post_office');
            $table->string('delivery_country')->default('Polska');

            // ===== DANE DO FAKTURY =====
            $table->boolean('invoice_required')->default(false);
            $table->string('invoice_company_name')->nullable();
            $table->string('invoice_nip', 20)->nullable();
            $table->string('invoice_street')->nullable();
            $table->string('invoice_street_number', 10)->nullable();
            $table->string('invoice_apartment', 10)->nullable();
            $table->string('invoice_city')->nullable();
            $table->string('invoice_postal_code', 10)->nullable();
            $table->string('invoice_post_office')->nullable();

            // ===== DOSTAWA =====
            $table->unsignedBigInteger('delivery_id'); // ID z Enova (Towary.ID)
            $table->string('delivery_name');
            $table->decimal('delivery_price', 10, 2)->default(0);

            // ===== PRODUKTY =====
            $table->json('items'); // Array produktów z koszyka

            // ===== KWOTY =====
            $table->decimal('subtotal', 10, 2); // Suma produktów
            $table->decimal('delivery_cost', 10, 2)->default(0);
            $table->decimal('total', 10, 2); // Całkowita kwota
            $table->string('currency', 3)->default('PLN');

            // ===== DODATKOWE INFORMACJE =====
            $table->text('notes')->nullable();
            $table->json('parcel_locker_data')->nullable(); // Dane paczkomatu (EasyPack)

            $table->timestamps();
            $table->softDeletes();

            // ===== INDEKSY =====
            $table->index('status');
            $table->index('customer_email');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
