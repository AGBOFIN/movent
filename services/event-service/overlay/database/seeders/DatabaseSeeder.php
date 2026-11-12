<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\Venue;
use Illuminate\Database\Seeder;

/**
 * DONNÉES DE DÉMONSTRATION : les événements et lieux ci-dessous sont des exemples
 * réalistes pour la présentation MOVENT. Ils ne constituent pas des événements
 * officiels vérifiés — les lieux, eux, sont réels et documentés (voir
 * documentation/credits-images.md et la base de données des lieux).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Concert', 'Festival', 'Conférence', 'Soirée', 'Sport', 'Théâtre', 'Exposition', 'Atelier', 'Forum', 'Salon'];
        foreach ($names as $n) {
            Category::firstOrCreate(['name' => $n], ['description' => 'Catégorie '.$n]);
        }

        $this->seedVenues();

        if (Event::count() > 0) {
            return;
        }
        $cat = Category::pluck('id', 'name');
        $venue = Venue::pluck('id', 'slug');
        $o1 = '00000000-0000-4000-8000-000000000011';
        $o2 = '00000000-0000-4000-8000-000000000012';

        // [titre, catégorie, organisateur, statut, prix FCFA, capacité, lieu (slug), jours, type, description]
        $rows = [
            ['Forum Économique Togo 2026', 'Forum', $o2, 'APPROVED', 0, 800, 'hotel-2-fevrier', 10, 'INSTITUTION',
                "Deux jours de discussions sur l'intégration régionale, l'entrepreneuriat et l'innovation.\n\nProgramme :\n- Jour 1 : plénière d'ouverture, panels économie numérique et agro-industrie\n- Jour 2 : ateliers B2B, session investisseurs, clôture officielle\n\nModalités de paiement : entrée libre sur inscription. Une attestation de participation est délivrée aux participants."],
            ['Nuit Live au Stade de Kégué', 'Concert', $o1, 'APPROVED', 7500, 15000, 'stade-de-kegue', 14, 'PRIVATE',
                "Un concert de musique live togolaise et africaine sous les projecteurs du plus grand stade du pays.\n\nProgramme :\n- 19h00 : ouverture des portes\n- 20h30 : premiers sets\n- 23h00 : tête d'affiche\n\nModalités de paiement : billet unique par personne, paiement en ligne sécurisé, billet QR à présenter à l'entrée."],
            ['Conférence IA pour l'Afrique de l'Ouest', 'Conférence', $o2, 'APPROVED', 0, 300, 'onomo-hotel-lome', 7, 'INSTITUTION',
                "Rencontre annuelle des chercheurs, entrepreneurs et institutions sur l'intelligence artificielle appliquée aux défis ouest-africains.\n\nProgramme :\n- Matin : conférences plénières\n- Après-midi : ateliers pratiques et tables rondes sectorielles\n\nModalités de paiement : participation gratuite sur inscription en ligne."],
            ['Finale du Championnat National', 'Sport', $o2, 'APPROVED', 1000, 15000, 'stade-de-kegue', 18, 'INSTITUTION',
                "La grande finale du championnat togolais de football, avec la réception du public dans les gradins.\n\nProgramme :\n- 15h00 : match des pensionnaires\n- 17h00 : finale masculine\n\nModalités de paiement : billet par personne, paiement à l'avance en ligne ou sur place selon disponibilité."],
            ['Gala de charité « Ensemble pour l'école »', 'Soirée', $o1, 'APPROVED', 15000, 300, 'hotel-sarakawa', 25, 'PRIVATE',
                "Soirée de gala au bord de l'océan pour financer la scolarité d'enfants défavorisés.\n\nProgramme :\n- 19h00 : accueil et cocktail\n- 20h30 : dîner de gala\n- 22h00 : enchères et surprises\n\nModalités de paiement : billet table ou individuel, paiement en ligne sécurisé."],
            ['Atelier d'entrepreneuriat pour jeunes', 'Atelier', $o2, 'APPROVED', 0, 100, 'institut-francais-lome', 9, 'INSTITUTION',
                "Une journée pratique pour apprendre à structurer une idée d'entreprise : business model, formalités et accès au financement.\n\nProgramme :\n- 09h00-12h30 : session matin\n- 14h00-17h00 : session après-midi avec mentors\n\nModalités de paiement : atelier gratuit, inscription obligatoire."],
            ['AGRO-TOGO : Salon de l'agriculture', 'Salon', $o2, 'APPROVED', 2000, 2000, 'palais-des-congres-lome', 30, 'INSTITUTION',
                "Le salon professionnel de référence de l'agriculture togolaise : produits locaux, machines, semences et services.\n\nProgramme :\n- 3 jours d'exposition et de conférences techniques\n- Journée professionnelle le jeudi\n\nModalités de paiement : billet journalier ou pass complet, paiement à l'entrée ou en ligne."],
            ['Festival des Arts Vivants', 'Festival', $o1, 'APPROVED', 3000, 5000, 'stade-municipal-kara', 45, 'PRIVATE',
                "Deux jours de musique, danse et théâtre en plein air à Kara, avec des troupes venues de tout le Togo.\n\nProgramme :\n- Jour 1 : défilé d'ouverture et concerts\n- Jour 2 : scène de rue, ateliers participatifs, grand concert final\n\nModalités de paiement : billet par jour ou pass 2 jours, paiement en ligne."],
            ['Gala de gala : légendes du football togolais', 'Sport', $o1, 'PENDING', 5000, 15000, 'stade-de-kegue', 12, 'PRIVATE',
                "Match de gala entre légendes du football togolais et africaines, organisé pour la réouverture d'une saison sportive.\n\nProgramme :\n- 17h00 : match de gala\n- 20h00 : cérémonie de clôture\n\nModalités de paiement : billet par personne, paiement en ligne."],
            ['Exposition « Savoirs du fleuve »', 'Exposition', $o1, 'PENDING', 1000, 200, 'palais-de-lome', 28, 'PRIVATE',
                "Exposition photographique et documentaire sur les savoirs et métiers liés au fleuve Mono.\n\nProgramme :\n- Vernissage le jour d'ouverture\n- Visites guidées chaque semaine\n\nModalités de paiement : entrée libre ou participation libre."],
            ['Brouillon : Soirée Jazz au bord de l'eau', 'Concert', $o1, 'DRAFT', 4000, 80, null, 33, 'PRIVATE',
                "Soirée jazz intimiste avec un quartet local.\n\nModalités de paiement : billet unique, paiement sur place."],
            ['Concert sans titre', 'Concert', $o2, 'REJECTED', 3500, 60, null, 18, 'PRIVATE',
                "Événement en préparation.\n\nModalités de paiement : à définir."],
        ];

        foreach ($rows as [$title, $c, $org, $status, $price, $cap, $venueSlug, $days, $kind, $desc]) {
            $e = new Event([
                'category_id' => $cat[$c],
                'venue_id' => $venueSlug ? $venue[$venueSlug] : null,
                'title' => $title,
                'description' => $desc,
                'image' => $this->eventImage($title),
                'image_credit' => $this->eventImageCredit($title),
                'location' => $this->venueLocation($venueSlug),
                'start_at' => now()->addDays($days)->setTime(19, 0),
                'end_at' => now()->addDays($days)->setTime(23, 0),
                'capacity' => $cap,
                'price' => $price,
                'organizer_kind' => $kind,
            ]);
            $e->organizer_id = $org;
            $e->available_places = $cap;
            $e->status = $status;
            $e->rejection_reason = $status === 'REJECTED' ? 'Informations insuffisantes : précisez le lieu et la description.' : null;
            $e->save();
        }
    }

    private function eventImage(string $title): ?string
    {
        return match (true) {
            str_contains($title, 'Forum') => '/img/events/forum-economique.jpg',
            str_contains($title, 'Nuit Live') => '/img/events/nuit-live.jpg',
            str_contains($title, 'IA pour') => '/img/events/conference-ia.jpg',
            str_contains($title, 'Championnat') => '/img/events/finale-foot.jpg',
            str_contains($title, 'charité') => '/img/events/gala-charite.jpg',
            str_contains($title, 'entrepreneuriat') => '/img/events/atelier-entrepreneuriat.jpg',
            str_contains($title, 'AGRO') => '/img/events/agro-togo.jpg',
            str_contains($title, 'Arts Vivants') => '/img/events/festival-arts.jpg',
            str_contains($title, 'légendes') => '/img/events/gala-legendes.jpg',
            str_contains($title, 'Savoirs') => '/img/events/expo-savoirs.jpg',
            str_contains($title, 'Jazz') => '/img/events/jazz-draft.jpg',
            default => null,
        };
    }

    private function eventImageCredit(string $title): ?string
    {
        $map = [
            'Forum' => 'forum-economique.jpg', 'Nuit Live' => 'nuit-live.jpg', 'IA pour' => 'conference-ia.jpg',
            'Championnat' => 'finale-foot.jpg', 'charité' => 'gala-charite.jpg', 'entrepreneuriat' => 'atelier-entrepreneuriat.jpg',
            'AGRO' => 'agro-togo.jpg', 'Arts Vivants' => 'festival-arts.jpg', 'légendes' => 'gala-legendes.jpg',
            'Savoirs' => 'expo-savoirs.jpg', 'Jazz' => 'jazz-draft.jpg',
        ];
        foreach ($map as $k => $f) {
            if (str_contains($title, $k)) {
                $c = [
                    'forum-economique.jpg' => 'Photo : World Economic Forum — CC BY 4.0 — Wikimedia Commons',
                    'nuit-live.jpg' => 'Photo : Shixart1985 — CC BY 2.0 — Wikimedia Commons',
                    'conference-ia.jpg' => 'Photo : Gilad Kavalerchik — CC BY-SA 4.0 — Wikimedia Commons',
                    'finale-foot.jpg' => 'Photo : feguifoot — domaine public — Wikimedia Commons',
                    'gala-charite.jpg' => 'Photo : Foundations World Economic Forum — CC BY 2.0 — Wikimedia Commons',
                    'atelier-entrepreneuriat.jpg' => 'Photo : Deror_avi — CC BY-SA 3.0 — Wikimedia Commons',
                    'agro-togo.jpg' => 'Photo : Uri Tours — CC BY-SA 2.0 — Wikimedia Commons',
                    'festival-arts.jpg' => 'Photo : LaShawnda Jones — CC BY-SA 4.0 — Wikimedia Commons',
                    'gala-legendes.jpg' => 'Photo : PinkBeachPlanet — CC BY-SA 4.0 — Wikimedia Commons',
                    'expo-savoirs.jpg' => 'Photo : Wiccapicca — CC BY-SA 4.0 — Wikimedia Commons',
                    'jazz-draft.jpg' => 'Photo : Shane Selig — CC BY-SA 4.0 — Wikimedia Commons',
                ];
                return $c[$f];
            }
        }
        return null;
    }

    private function venueLocation(?string $slug): string
    {
        return match ($slug) {
            'hotel-2-fevrier' => 'Hôtel 2 Février, Lomé',
            'hotel-sarakawa' => 'Hôtel Sarakawa, Lomé',
            'onomo-hotel-lome' => 'ONOMO Hotel Lomé, Lomé',
            'palais-des-congres-lome' => 'Palais des Congrès, Lomé',
            'palais-de-lome' => 'Palais de Lomé, Lomé',
            'stade-de-kegue' => 'Stade de Kégué, Lomé',
            'institut-francais-lome' => 'Institut français du Togo, Lomé',
            'palais-des-congres-kara' => 'Palais des Congrès, Kara',
            'stade-municipal-kara' => 'Stade municipal, Kara',
            default => 'Lieu à préciser',
        };
    }

    private function seedVenues(): void
    {
        // Lieux réels et publics — coordonnées vérifiées sur les sites officiels
        // et sources publiques (voir documentation/). Capacités = ordres de grandeur publics.
        $venues = [
            [
                'name' => 'Hôtel 2 Février', 'slug' => 'hotel-2-fevrier', 'type' => 'HOTEL', 'city' => 'Lomé',
                'address' => "Place de l'Indépendance, BP 131, Lomé",
                'description' => "Hôtel cinq étoiles au cœur du quartier administratif de Lomé, référence des congrès et réceptions d'État. Ses espaces événementiels couvrent plus de 3 000 m², de l'auditoire Concorde à la grande salle de bal Fazao.",
                'capacity' => 1000,
                'amenities' => "Auditoire Concorde (404 places), salle de bal Fazao (jusqu'à 1 000), 7 salles de réunion, parkings, restaurants, centre d'affaires",
                'website' => 'https://2fevrier.com',
                'image' => '/img/lieux/hotel-2-fevrier.jpg',
                'image_credit' => 'Photo : Edison McCullen — CC BY 4.0 — Wikimedia Commons',
            ],
            [
                'name' => 'Hôtel Sarakawa', 'slug' => 'hotel-sarakawa', 'type' => 'HOTEL', 'city' => 'Lomé',
                'address' => 'Boulevard du Mono, BP 2232, Lomé',
                'description' => "Hôtel balnéaire niché dans un parc de 40 hectares au bord de l'océan, mêlant architecture traditionnelle et confort moderne : piscines, restaurants et vastes espaces verts pour réceptions et événements.",
                'capacity' => null,
                'amenities' => 'Parc de 40 ha, piscine olympique, restaurants, salle de réception, accès plage',
                'website' => 'https://sarakawa-hotel.com',
                'image' => '/img/lieux/hotel-sarakawa.jpg',
                'image_credit' => 'Photo : Alfrednadjere — CC BY-SA 4.0 — Wikimedia Commons',
            ],
            [
                'name' => 'ONOMO Hotel Lomé', 'slug' => 'onomo-hotel-lome', 'type' => 'HOTEL', 'city' => 'Lomé',
                'address' => 'Boulevard du Mono, Bê Souzanétimé, 01 BP 2135, Lomé',
                'description' => "Hôtel du groupe panafricain ONOMO à 15 minutes de l'aéroport et à proximité du centre-ville : 127 chambres, quatre salles de réunion lumineuses et un jardin de 3 500 m² pouvant accueillir de grands événements en plein air.",
                'capacity' => 800,
                'amenities' => '4 salles de réunion (jusqu\'à 180), jardin 3 500 m² (jusqu\'à 800), piscine à débordement, restaurant O\'Kope, salle de sport',
                'website' => 'https://www.onomohotels.com/hotel/onomo-hotel-lome/',
                'image' => '/img/lieux/onomo-hotel-lome.jpg',
                'image_credit' => 'Photo : ONOMO Hotels — CC BY-SA 4.0 — Wikimedia Commons',
            ],
            [
                'name' => 'Palais des Congrès de Lomé', 'slug' => 'palais-des-congres-lome', 'type' => 'PALACE', 'city' => 'Lomé',
                'address' => 'Avenue du Golfe, quartier administratif, Lomé',
                'description' => "Centre de congrès et de spectacles inauguré en 1981, fruit de la coopération Togo–Chine. Grande salle pouvant accueillir environ 5 000 personnes, ainsi que des salles de conférence et une esplanade pour manifestations publiques.",
                'capacity' => 5000,
                'amenities' => 'Grande salle de spectacle (~5 000 places), salles de conférence, esplanade',
                'website' => null,
                'image' => '/img/lieux/palais-des-congres-lome.jpg',
                'image_credit' => 'Photo : Lome2023 — CC BY-SA 4.0 — Wikimedia Commons',
            ],
            [
                'name' => 'Palais de Lomé', 'slug' => 'palais-de-lome', 'type' => 'CULTURAL_CENTER', 'city' => 'Lomé',
                'address' => '382 avenue de la Marina, Lomé',
                'description' => "Ancien palais des gouverneurs allemands (1905), transformé en centre d'art et de culture en 2019. Architecture coloniale emblématique en bord de mer, exposant expositions, spectacles et événements culturels.",
                'capacity' => null,
                'amenities' => 'Salles d\'exposition, auditorium, jardin, billetterie sur place',
                'website' => 'https://www.palaisdelome.com',
                'image' => '/img/lieux/palais-de-lome.jpg',
                'image_credit' => 'Photo : Ak9visuelmind — CC BY-SA 4.0 — Wikimedia Commons',
            ],
            [
                'name' => 'Stade de Kégué', 'slug' => 'stade-de-kegue', 'type' => 'STADIUM', 'city' => 'Lomé',
                'address' => 'Avenue Jean-Paul II, Kégué, Lomé',
                'description' => "Plus grand stade du Togo, siège des Éperviers et théâtre de la qualification historique pour la Coupe du monde 2006. Réhabilité en 2019, il accueille football, athlétisme et grands concerts.",
                'capacity' => 25000,
                'amenities' => 'Pelouse naturelle, piste d\'athlétisme, gradins, vestiaires, écran géant',
                'website' => null,
                'image' => '/img/lieux/stade-de-kegue.jpg',
                'image_credit' => 'Photo : Ed Suter — CC BY-SA 4.0 — Wikimedia Commons (image de substitution, stade générique)',
            ],
            [
                'name' => 'Institut français du Togo', 'slug' => 'institut-francais-lome', 'type' => 'CULTURAL_CENTER', 'city' => 'Lomé',
                'address' => 'Avenue du Général de Gaulle, face au Consulat de France, Lomé',
                'description' => "Centre culturel dans le quartier administratif : médiathèque, cours de français, et une scène extérieure baptisée scène Jimi Hope — la plus grande de Lomé — pour concerts, théâtre et événements.",
                'capacity' => null,
                'amenities' => 'Scène Jimi Hope, auditorium, jardin, médiathèque, espace jeunesse',
                'website' => 'https://institutfrancais-togo.com',
                'image' => '/img/lieux/institut-francais-lome.jpg',
                'image_credit' => 'Photo : AKE Amazan — CC BY-SA 4.0 — Wikimedia Commons',
            ],
            [
                'name' => 'Palais des Congrès de Kara', 'slug' => 'palais-des-congres-kara', 'type' => 'PALACE', 'city' => 'Kara',
                'address' => 'Avenue du Général Gnassingbé Eyadéma, Kara',
                'description' => "Centre de congrès de la capitale régionale de la Kara, construit avec l'aide de la Chine dans les années 1980 : hall de 1 500 places, salle de banquet de 500 places, salles de réunion et esplanade.",
                'capacity' => 1500,
                'amenities' => 'Hall de 1 500 places, salle de banquet (500), salles de réunion, esplanade',
                'website' => null,
                'image' => '/img/lieux/palais-des-congres-kara.jpg',
                'image_credit' => 'Photo : Hermannkass — CC BY-SA 4.0 — Wikimedia Commons',
            ],
            [
                'name' => 'Stade municipal de Kara', 'slug' => 'stade-municipal-kara', 'type' => 'STADIUM', 'city' => 'Kara',
                'address' => 'Kara',
                'description' => "Stade municipal de Kara, domicile de l'ASKO Kara, championne du Togo en 2021. Pelouse synthétique refaite aux normes FIFA, tribunes et vestiaires pour compétitions et événements sportifs.",
                'capacity' => 10000,
                'amenities' => 'Pelouse synthétique, tribunes, vestiaires, aire de compétition',
                'website' => null,
                'image' => '/img/lieux/stade-municipal-kara.jpg',
                'image_credit' => 'Photo : Theolebaron — CC BY-SA 4.0 — Wikimedia Commons',
            ],
        ];

        foreach ($venues as $v) {
            Venue::updateOrCreate(['slug' => $v['slug']], $v);
        }
    }
}
