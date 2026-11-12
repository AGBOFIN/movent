<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Concert', 'Festival', 'Conférence', 'Soirée', 'Sport', 'Théâtre', 'Exposition', 'Atelier'];
        foreach ($names as $n) {
            Category::firstOrCreate(['name' => $n], ['description' => 'Catégorie '.$n]);
        }
        if (Event::count() > 0) {
            return;
        }
        $cat = Category::pluck('id', 'name');
        $o1 = '00000000-0000-4000-8000-000000000011';
        $o2 = '00000000-0000-4000-8000-000000000012';
        // [titre, catégorie, organisateur, statut, prix FCFA, capacité, lieu, jours à venir, type (INSTITUTION|PRIVATE)]
        $rows = [
            ['Nuit Live Lomé', 'Concert', $o1, 'APPROVED', 5000, 300, 'Palais des Congrès, Lomé', 9, 'PRIVATE'],
            ['Festival Soleil', 'Festival', $o1, 'APPROVED', 10000, 800, 'Plage de Lomé', 14, 'PRIVATE'],
            ['Tech Summit Togo', 'Conférence', $o2, 'APPROVED', 0, 200, 'Hôtel 2 Février, Lomé', 7, 'INSTITUTION'],
            ['Gala d’Or', 'Soirée', $o2, 'APPROVED', 25000, 120, 'Salle Prestige, Lomé', 20, 'PRIVATE'],
            ['Finale du Stade', 'Sport', $o1, 'APPROVED', 2000, 1500, 'Stade de Kégué', 12, 'PRIVATE'],
            ['Soir de Rideau', 'Théâtre', $o2, 'APPROVED', 3000, 150, 'Institut Français, Lomé', 16, 'PRIVATE'],
            ['Journée portes ouvertes ministries', 'Conférence', $o2, 'APPROVED', 0, 500, 'Palais de la Marina, Lomé', 18, 'INSTITUTION'],
            ['Expo Couleurs d’Afrique', 'Exposition', $o2, 'PENDING', 1500, 100, 'Galerie Centrale, Lomé', 25, 'PRIVATE'],
            ['Atelier Photo Pro', 'Atelier', $o1, 'PENDING', 7500, 25, 'Studio Lumière, Lomé', 11, 'PRIVATE'],
            ['Brouillon : Soirée Jazz', 'Concert', $o1, 'DRAFT', 4000, 80, 'Bar Le Blue, Lomé', 30, 'PRIVATE'],
            ['Concert sans titre', 'Concert', $o2, 'REJECTED', 3500, 60, 'Lieu à préciser', 18, 'PRIVATE'],
        ];
        foreach ($rows as [$title, $c, $org, $status, $price, $cap, $loc, $days, $kind]) {
            $e = new Event([
                'category_id' => $cat[$c], 'title' => $title,
                'description' => 'Un événement MOVENT à ne pas manquer : ambiance, rencontres et moments partagés.',
                'location' => $loc, 'start_at' => now()->addDays($days)->setTime(19, 0),
                'end_at' => now()->addDays($days)->setTime(23, 0), 'capacity' => $cap, 'price' => $price,
                'organizer_kind' => $kind,
            ]);
            $e->organizer_id = $org;
            $e->available_places = $cap;
            $e->status = $status;
            $e->rejection_reason = $status === 'REJECTED' ? 'Informations insuffisantes : précisez le lieu et la description.' : null;
            $e->save();
        }
    }
}
