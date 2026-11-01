<?php

namespace Database\Seeders;

use App\Models\DailyItem;
use Illuminate\Database\Seeder;

// Objets des jeux du jour : prix indicatifs, photos libres (Pexels, ou Wikimedia Commons en CC BY-SA).
class DailyItemSeeder extends Seeder
{
    public function run(): void
    {
        $pexels = 'Photo Pexels';

        $objets = [
            ['Bouilloire électrique', 35, 'bouilloire', 'Jacek Halicki · CC BY-SA 4.0 · Wikimedia Commons'],
            ['Grille-pain', 25, 'grille-pain', $pexels],
            ['Plaid en laine', 45, 'plaid', $pexels],
            ['Bougie parfumée', 18, 'bougie', $pexels],
            ['Livre de cuisine', 28, 'livre-cuisine', $pexels],
            ['Cafetière à piston', 30, 'cafetiere', 'Ö4anby0502 · CC BY-SA 4.0 · Wikimedia Commons'],
            ['Lampe de chevet', 40, 'lampe', 'PatongHarbor · CC BY-SA 4.0 · Wikimedia Commons'],
            ['Set de 6 verres à whisky', 22, 'verres-whisky', 'Petar Milošević · CC BY-SA 4.0 · Wikimedia Commons'],
            ['Tablier en lin', 32, 'tablier', $pexels],
            ['Coffret de thés', 24, 'coffret-the', $pexels],
            ['Casque audio sans fil', 79, 'casque', $pexels],
            ['Enceinte Bluetooth', 59, 'enceinte', $pexels],
            ['Montre connectée', 149, 'montre', $pexels],
            ['Sac à dos', 55, 'sac-a-dos', $pexels],
            ['Mug isotherme', 19, 'mug', $pexels],
            ['Monstera en pot', 35, 'monstera', $pexels],
            ['Jeu de société coopératif', 30, 'jeu-de-societe', $pexels],
            ["Abonnement magazine d'un an", 60, 'magazine', $pexels],
            ['Carnet de voyage en cuir', 26, 'carnet', $pexels],
            ["Diffuseur d'huiles essentielles", 29, 'diffuseur', $pexels],
        ];

        DailyItem::query()->delete();

        foreach ($objets as [$nom, $prix, $slug, $credit]) {
            DailyItem::create([
                'name' => $nom,
                'price' => $prix,
                'image' => "images/jeux/$slug.jpg",
                'credit' => $credit,
            ]);
        }
    }
}
