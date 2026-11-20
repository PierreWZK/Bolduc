<?php

namespace Database\Seeders;

use App\Models\Idee;
use Illuminate\Database\Seeder;

class IdeeSeeder extends Seeder
{
    public function run(): void
    {
        // Rédigées par nous ; les prix sont des ordres de grandeur, pas des tarifs vérifiés.
        // Photos et crédits : scripts/idees/telecharger_images.py.
        $idees = json_decode(file_get_contents(database_path('seeders/data/idees.json')), true, flags: JSON_THROW_ON_ERROR);

        foreach ($idees as $idee) {
            Idee::updateOrCreate(['title' => $idee['title']], [
                'description' => $idee['description'],
                'category' => $idee['category'],
                'icon' => $idee['icon'],
                'image' => $idee['image'] ?? null,
                'credit' => $idee['credit'] ?? null,
                'price' => $idee['price'],
                'events' => $idee['events'],
            ]);
        }
    }
}
