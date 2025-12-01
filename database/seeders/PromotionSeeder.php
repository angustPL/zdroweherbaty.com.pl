<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Migracja darmowej wysyłki z config do tabeli promotions
        $freeDeliveryThreshold = (float) config('enova.delivery.free_delivery_threshold', 80);

        if ($freeDeliveryThreshold > 0) {
            // Sprawdź czy już istnieje promocja darmowej dostawy
            $existing = Promotion::where('type', 'automatic')
                ->where('discount_type', 'free_delivery')
                ->first();

            if ($existing) {
                // Aktualizuj istniejącą
                $existing->update([
                    'name' => 'Darmowa dostawa',
                    'description' => 'Darmowa dostawa dla zamówień powyżej ' . number_format($freeDeliveryThreshold, 0, ',', '.') . ' zł',
                    'min_order_amount' => $freeDeliveryThreshold,
                    'is_active' => true,
                ]);
                $this->command->info('✓ Promocja "Darmowa dostawa" została zaktualizowana.');
            } else {
                // Utwórz nową
                Promotion::create([
                    'name' => 'Darmowa dostawa',
                    'code' => null,
                    'description' => 'Darmowa dostawa dla zamówień powyżej ' . number_format($freeDeliveryThreshold, 0, ',', '.') . ' zł',
                    'type' => 'automatic',
                    'discount_type' => 'free_delivery',
                    'discount_value' => 0,
                    'max_discount_amount' => null,
                    'min_order_amount' => $freeDeliveryThreshold,
                    'product_ids' => null,
                    'group_ids' => null,
                    'conditions' => null,
                    'valid_from' => null,
                    'valid_to' => null,
                    'usage_limit' => null,
                    'usage_count' => 0,
                    'usage_limit_per_user' => null,
                    'is_active' => true,
                    'can_combine_with_others' => true,
                    'always_applicable' => true, // Darmowa dostawa zawsze stosowana
                    'priority' => 10,
                ]);
                $this->command->info('✓ Promocja "Darmowa dostawa" została dodana do bazy.');
            }
        } else {
            $this->command->warn('⚠ Próg darmowej dostawy nie jest ustawiony w konfiguracji.');
        }

        // Kod rabatowy BLACK15
        $existingBlack15 = Promotion::where('code', 'BLACK15')->first();

        if ($existingBlack15) {
            // Aktualizuj istniejący
            $existingBlack15->update([
                'name' => 'Black Friday -15%',
                'description' => '15% zniżki na wszystkie produkty',
                'type' => 'code',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'max_discount_amount' => null,
                'min_order_amount' => null,
                'valid_from' => '2025-11-28 00:00:00',
                'valid_to' => '2025-11-30 23:59:59',
                'usage_limit' => null,
                'usage_limit_per_user' => null,
                'is_active' => true,
                'can_combine_with_others' => false, // Kod BLACK15 nie może być łączony z innymi
                'priority' => 20,
            ]);
            $this->command->info('✓ Promocja "BLACK15" została zaktualizowana.');
        } else {
            // Utwórz nową
            Promotion::create([
                'name' => 'Black Friday -15%',
                'code' => 'BLACK15',
                'description' => '15% zniżki na wszystkie produkty',
                'type' => 'code',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'max_discount_amount' => null,
                'min_order_amount' => null,
                'conditions' => null,
                'valid_from' => '2025-11-28 00:00:00',
                'valid_to' => '2025-11-30 23:59:59',
                'usage_limit' => null,
                'usage_count' => 0,
                'usage_limit_per_user' => null,
                'is_active' => true,
                'can_combine_with_others' => false, // Kod BLACK15 nie może być łączony z innymi
                'priority' => 20,
            ]);
            $this->command->info('✓ Promocja "BLACK15" została dodana do bazy.');
        }
    }
}
