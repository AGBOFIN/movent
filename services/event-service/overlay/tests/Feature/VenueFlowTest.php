<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VenueFlowTest extends TestCase
{
    use RefreshDatabase;

    private function venue(array $over = []): Venue
    {
        return Venue::create(array_merge([
            'name' => 'Salle Test', 'slug' => 'hotel-test-'.Str::lower(Str::random(6)),
            'type' => 'HOTEL', 'city' => 'Lomé', 'address' => 'Rue 1',
            'capacity' => 500, 'is_active' => true,
        ], $over));
    }

    private function eventFor(Venue $v, string $status = 'APPROVED'): Event
    {
        $e = new Event([
            'category_id' => \App\Models\Category::firstOrCreate(['name' => 'Concert'])->id,
            'venue_id' => $v->id,
            'title' => 'Soirée '.$v->name,
            'location' => $v->name.', '.$v->city,
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(5)->addHours(3),
            'capacity' => 100, 'price' => 2000,
        ]);
        $e->organizer_id = (string) Str::uuid();
        $e->available_places = 100;
        $e->status = $status;
        $e->save();
        return $e;
    }

    public function test_la_liste_des_lieux_est_publique(): void
    {
        $v = $this->venue();
        $this->venue(['is_active' => false, 'slug' => 'cache']);
        $r = $this->getJson('/api/venues')->assertOk();
        $slugs = collect($r->json())->pluck('slug');
        $this->assertContains($v->slug, $slugs);
        $this->assertNotContains('cache', $slugs);
    }

    public function test_filtre_par_ville_et_recherche(): void
    {
        $this->venue(['city' => 'Lomé']);
        $this->venue(['city' => 'Kara', 'slug' => 'kara-1']);
        $this->assertCount(1, $this->getJson('/api/venues?city=Lomé')->json());
        $this->assertCount(1, $this->getJson('/api/venues?city=Kara')->json());
        $this->assertCount(2, $this->getJson('/api/venues?q=salle')->json());
    }

    public function test_le_detail_d_un_lieu_liste_ses_evenements_a_venir(): void
    {
        $v = $this->venue();
        $this->eventFor($v, 'APPROVED');
        $this->eventFor($v, 'DRAFT');   // non public
        $autre = $this->venue(['slug' => 'autre-lieu']);
        $this->eventFor($autre); // autre lieu

        $r = $this->getJson('/api/venues/'.$v->slug)->assertOk();
        $this->assertSame($v->name, $r->json('venue.name'));
        $this->assertCount(1, $r->json('events'));
        $this->assertSame('Soirée '.$v->name, $r->json('events.0.title'));
    }

    public function test_un_lieu_inconnu_renvoie_404(): void
    {
        $this->getJson('/api/venues/inexistant')->assertStatus(404);
    }

    public function test_filtre_evenements_par_lieu_et_ville(): void
    {
        $v1 = $this->venue(['city' => 'Lomé']);
        $v2 = $this->venue(['city' => 'Kara', 'slug' => 'kara-x']);
        $this->eventFor($v1);
        $this->eventFor($v2);

        $r = $this->getJson('/api/events?venue_id='.$v1->id)->assertOk();
        $this->assertCount(1, $r->json('data'));
        $this->assertSame($v1->id, $r->json('data.0.venue_id'));
        $this->assertNotNull($r->json('data.0.venue'));

        $this->assertCount(1, $this->getJson('/api/events?city=Kara')->json('data'));
        $this->assertCount(1, $this->getJson('/api/events?city=Lomé')->json('data'));
        $this->getJson('/api/events?city=Inconnue')->assertOk()->assertJsonPath('total', 0);
    }

    public function test_venue_id_invalide_refuse_a_la_creation(): void
    {
        $org = $this->auth(['role' => 'ORGANIZER', 'sub' => (string) Str::uuid()]);
        $c = \App\Models\Category::firstOrCreate(['name' => 'Concert']);
        $this->postJson('/api/events', [
            'title' => 'Test', 'category_id' => $c->id, 'location' => 'Lomé',
            'start_at' => now()->addDays(3)->toIso8601String(),
            'end_at' => now()->addDays(3)->addHours(2)->toIso8601String(),
            'capacity' => 10, 'price' => 1000,
            'venue_id' => (string) Str::uuid(),
        ], $org)->assertStatus(422)->assertJsonValidationErrors('venue_id');
    }

    public function test_les_villes_sont_listées(): void
    {
        $this->venue(['city' => 'Lomé']);
        $this->venue(['city' => 'Kara', 'slug' => 'kara-y']);
        $r = $this->getJson('/api/venues/cities')->assertOk();
        $this->assertEqualsCanonicalizing(['Lomé', 'Kara'], $r->json());
    }
}
