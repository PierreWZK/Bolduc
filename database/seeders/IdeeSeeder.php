<?php

namespace Database\Seeders;

use App\Models\Idee;
use Illuminate\Database\Seeder;

class IdeeSeeder extends Seeder
{
    public function run(): void
    {
        // Rédigées par nous ; les prix sont des ordres de grandeur, pas des tarifs vérifiés.
        $idees = [
            ['Vase en grès émaillé', 'Une pièce qui vit longtemps sur une étagère, à remplir de fleurs ou de branches.', 'maison', 'lampe', 35, ['noel', 'cremaillere', 'anniversaire']],
            ['Plaid en laine', 'Doux, chaud, à poser sur le canapé pour les soirées froides.', 'maison', 'plaid', 59, ['noel', 'cremaillere', 'naissance']],
            ['Bougies parfumées', 'Un lot de trois, en cire de soja, pour les hivers longs.', 'maison', 'lampe', 24, ['noel', 'cremaillere', 'mariage']],
            ['Plante verte', 'Une monstera ou un pothos : facile à garder, joli partout.', 'maison', 'plante', 32, ['cremaillere', 'anniversaire', 'mariage']],
            ['Service de verres à vin', 'Six verres en cristal, pour les dîners qui s\'allongent.', 'maison', 'verre', 64, ['mariage', 'noel', 'cremaillere']],
            ['Lampe de chevet', 'Une lumière douce pour lire au lit, sans éblouir.', 'maison', 'lampe', 49, ['cremaillere', 'mariage', 'anniversaire']],
            ['Coffret de thés', 'Une sélection de thés en vrac, de quoi découvrir sans se tromper.', 'gourmandises', 'tasse', 28, ['noel', 'anniversaire', 'autre']],
            ['Chocolats artisanaux', 'Une boîte de chocolats fabriqués en petite série.', 'gourmandises', 'panier', 18, ['noel', 'anniversaire', 'naissance']],
            ['Panier de produits locaux', 'Confitures, miel et biscuits d\'un producteur de la région.', 'gourmandises', 'panier', 45, ['noel', 'cremaillere', 'autre']],
            ['Carnet de recettes', 'Un carnet à remplir à quatre mains, pour garder les plats de famille.', 'loisirs', 'carnet', 24, ['noel', 'mariage', 'anniversaire']],
            ['Livre illustré', 'Un beau livre à offrir, à lire à voix haute ou à garder sur la table basse.', 'loisirs', 'carnet', 22, ['noel', 'naissance', 'anniversaire']],
            ['Puzzle 1 000 pièces', 'Une image à assembler sur plusieurs soirées, à deux ou seul.', 'loisirs', 'puzzle', 19, ['noel', 'anniversaire', 'autre']],
            ['Vinyle de jazz', 'Un disque d\'un artiste aimé, ou à découvrir ensemble.', 'loisirs', 'musique', 28, ['anniversaire', 'mariage', 'autre']],
            ['Séance d\'escalade', 'Un pass pour une salle, à essayer à deux.', 'experiences', 'montagne', 45, ['anniversaire', 'autre']],
            ['Atelier poterie', 'Une après-midi pour tourner l\'argile, avec un cours pour débutants.', 'experiences', 'tasse', 65, ['anniversaire', 'mariage', 'autre']],
            ['Nuit en cabane', 'Une nuit dans un lieu isolé, pour partir sans aller loin.', 'experiences', 'maison', 150, ['mariage', 'anniversaire', 'autre']],
            ['Casque audio', 'Un modèle à réduction de bruit, pour les trajets et le télétravail.', 'high-tech', 'casque', 129, ['anniversaire', 'noel', 'autre']],
            ['Enceinte nomade', 'Petite, étanche, pour la plage comme pour la cuisine.', 'high-tech', 'enceinte', 89, ['anniversaire', 'noel', 'autre']],
            ['Tapis d\'éveil', 'Un espace de jeu pour les premiers mois, avec des arches à attraper.', 'bebe', 'bebe', 59, ['naissance']],
            ['Gigoteuse', 'Un sac de couchage pour bébé, pour dormir sans couverture.', 'bebe', 'bebe', 39, ['naissance']],
            ['Livres en tissu', 'Des livres pour bébé à toucher, à mordiller, à découvrir.', 'bebe', 'carnet', 18, ['naissance']],
        ];

        foreach ($idees as [$titre, $description, $categorie, $icone, $prix, $evenements]) {
            Idee::updateOrCreate(['title' => $titre], [
                'description' => $description,
                'category' => $categorie,
                'icon' => $icone,
                'price' => $prix,
                'events' => $evenements,
            ]);
        }
    }
}
