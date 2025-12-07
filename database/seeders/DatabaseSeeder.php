<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed ról (admin, editor)
        $this->call(RoleSeeder::class);

        // Tworzenie użytkownika administratora z danych z .env
        $adminEmail = env('ADMIN_EMAIL', 'admin@zdroweherbaty.com.pl');
        $adminPassword = env('ADMIN_PASSWORD', 'password');
        $adminName = env('ADMIN_NAME', 'Administrator');

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => \Illuminate\Support\Facades\Hash::make($adminPassword),
                'email_verified_at' => now(),
            ]
        );

        // Przypisanie roli admin do administratora (jeśli jeszcze nie ma)
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Seed treści SEO (regulamin, treści dla grup, strona główna)
        $this->call(ContentSeeder::class);

        // Seed promocji (darmowa dostawa, kody rabatowe)
        $this->call(PromotionSeeder::class);
    }
}
