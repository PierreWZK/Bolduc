<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(IdeeSeeder::class);
        $this->call(DailyItemSeeder::class);

        // Compte de démonstration, en local seulement.
        User::factory()->create([
            'name' => 'Camille',
            'email' => 'camille@exemple.test',
            'password' => 'bolduc-test-2026',
        ]);
    }
}
