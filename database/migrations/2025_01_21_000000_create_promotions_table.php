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
        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->id();

                // ===== PODSTAWOWE INFORMACJE =====
                $table->string('name'); // Nazwa promocji
                $table->string('code')->nullable()->unique(); // Kod promocyjny (null dla promocji automatycznych)
                $table->text('description')->nullable(); // Opis promocji

                // ===== TYP PROMOCJI =====
                // 'code' - kod promocyjny (wpisywany przez użytkownika)
                // 'automatic' - automatyczna (np. darmowa wysyłka powyżej X)
                // 'seasonal' - sezonowa (np. świąteczna)
                $table->string('type')->default('code'); // code, automatic, seasonal

                // ===== TYP ZNIŻKI =====
                // 'percentage' - procentowa (np. 10%)
                // 'fixed' - kwotowa (np. -50 zł)
                // 'free_delivery' - darmowa dostawa
                $table->string('discount_type'); // percentage, fixed, free_delivery
                $table->decimal('discount_value', 10, 2); // Wartość zniżki (procent lub kwota)
                $table->decimal('max_discount_amount', 10, 2)->nullable(); // Maksymalna kwota zniżki (dla procentowych)

                // ===== WARUNKI =====
                $table->decimal('min_order_amount', 10, 2)->nullable(); // Minimalna kwota zamówienia
                // product_ids i group_ids przeniesione do tabel pivot (promotion_products, promotion_groups)
                // null w tych tabelach = wszystkie produkty/grupy
                $table->json('conditions')->nullable(); // Dodatkowe warunki (JSON)

                // ===== DATY I LIMITY =====
                $table->date('valid_from')->nullable(); // Data rozpoczęcia
                $table->date('valid_to')->nullable(); // Data zakończenia
                $table->integer('usage_limit')->nullable(); // Limit użyć (null = bez limitu)
                $table->integer('usage_count')->default(0); // Licznik użyć
                $table->integer('usage_limit_per_user')->nullable(); // Limit użyć na użytkownika (null = bez limitu)

                // ===== STATUS =====
                $table->boolean('is_active')->default(true); // Czy promocja jest aktywna
                $table->boolean('can_combine_with_others')->default(false); // Czy można łączyć z innymi promocjami
                $table->boolean('always_applicable')->default(false); // Czy promocja może być zawsze stosowana (np. darmowa dostawa)
                $table->integer('priority')->default(0); // Priorytet (wyższy = pierwszeństwo przy konfliktach)

                $table->timestamps();
                $table->softDeletes();

                // ===== INDEKSY =====
                $table->index('code');
                $table->index('type');
                $table->index('is_active');
                $table->index(['valid_from', 'valid_to']);
            });
        } else {
            // Jeśli tabela już istnieje, dodaj brakujące kolumny i usuń stare
            Schema::table('promotions', function (Blueprint $table) {
                if (!Schema::hasColumn('promotions', 'can_combine_with_others')) {
                    $table->boolean('can_combine_with_others')->default(false)->after('is_active');
                }
                if (!Schema::hasColumn('promotions', 'always_applicable')) {
                    $table->boolean('always_applicable')->default(false)->after('can_combine_with_others');
                }
                if (!Schema::hasColumn('promotions', 'priority')) {
                    $table->integer('priority')->default(0)->after('always_applicable');
                }
                // Usuń stare kolumny, jeśli istnieją
                if (Schema::hasColumn('promotions', 'product_ids')) {
                    $table->dropColumn('product_ids');
                }
                if (Schema::hasColumn('promotions', 'group_ids')) {
                    $table->dropColumn('group_ids');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
