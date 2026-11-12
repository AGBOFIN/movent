<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventFlowTest extends TestCase
{
    use RefreshDatabase;

    private function category(): Category
    {
        return Category::firstOrCreate(['name' => 'Concert']);
    }

    private function makeEvent(string $status = 'APPROVED', array $over = []): Event
    {
        $e = new Event(array_merge([
            'category_id' => $this->category()->id,
            'title' => 'Soirée Test',
            'description' => 'Description',
            'location' => 'Lomé',
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(5)->addHours(3),
            'capacity' => 100,
            'price' => 5000,
        ], $over));
        $e->organizer_id = $over['organizer_id'] ?? '00000000-0000-4000-8000-000000000011';
        $e->available_places = $e->capacity;
        $e->status = $status;
        $e->save();
        return $e;
    }

    private function payload(Category $c, array $over = []): array
    {
        return array_merge([
            'title' => 'Mon Événement',
            'description' => 'Une description',
            'category_id' => $c->id,
            'location' => 'Kara',
            'start_at' => now()->addDays(10)->toIso8601String(),
            'end_at' => now()->addDays(10)->addHours(2)->toIso8601String(),
            'capacity' => 50,
            'price' => 2500,
        ], $over);
    }

    public function test_le_catalogue_ne_montre_que_les_evenements_approuves(): void
    {
        $this->makeEvent('APPROVED', ['title' => 'Visible']);
        $this->makeEvent('DRAFT', ['title' => 'Cache']);
        $this->makeEvent('PENDING', ['title' => 'En attente']);
        $this->makeEvent('REJECTED', ['title' => 'Rejete']);

        $r = $this->getJson('/api/events');
        $r->assertOk();
        $titles = collect($r->json('data'))->pluck('title');
        $this->assertContains('Visible', $titles);
        $this->assertNotContains('Cache', $titles);
        $this->assertNotContains('En attente', $titles);
        $this->assertNotContains('Rejete', $titles);
    }

    public function test_un_participant_ne_peut_pas_creer_ni_soumettre(): void
    {
        $c = $this->category();
        $h = $this->auth(['role' => 'PARTICIPANT']);
        $this->postJson('/api/events', $this->payload($c), $h)->assertStatus(403);
    }

    public function test_cycle_complet_brouillon_soumission_validation(): void
    {
        $c = $this->category();
        $org = $this->auth(['role' => 'ORGANIZER', 'sub' => (string) Str::uuid()]);
        $admin = $this->auth(['role' => 'ADMIN', 'sub' => (string) Str::uuid()]);

        $r = $this->postJson('/api/events', $this->payload($c), $org)->assertCreated();
        $id = $r->json('id');
        $this->assertSame('DRAFT', $r->json('status'));
        $this->assertSame(50, $r->json('available_places'));

        // invisible publiquement tant que non approuvé
        $this->getJson('/api/events/'.$id)->assertStatus(404);

        $this->postJson("/api/events/{$id}/submit", [], $org)->assertOk()->assertJsonPath('status', 'PENDING');
        // un DRAFT/PENDING ne peut pas être resoumis
        $this->postJson("/api/events/{$id}/submit", [], $org)->assertStatus(409);

        // rejet sans motif refusé
        $this->postJson("/api/events/{$id}/reject", [], $admin)->assertStatus(422);
        $this->postJson("/api/events/{$id}/reject", ['reason' => 'Lieu imprécis'], $admin)
            ->assertOk()->assertJsonPath('status', 'REJECTED')
            ->assertJsonPath('rejection_reason', 'Lieu imprécis');

        // l'organisateur corrige et resoumet
        $this->putJson("/api/events/{$id}", $this->payload($c, ['location' => 'Lomé']), $org)->assertOk();
        $this->postJson("/api/events/{$id}/submit", [], $org)->assertOk()->assertJsonPath('status', 'PENDING');

        $this->postJson("/api/events/{$id}/approve", [], $admin)->assertOk()->assertJsonPath('status', 'APPROVED');
        $this->getJson('/api/events/'.$id)->assertOk()->assertJsonPath('status', 'APPROVED');
    }

    public function test_un_organisateur_ne_modifie_pas_l_evenement_d_un_autre(): void
    {
        $e = $this->makeEvent('DRAFT', ['organizer_id' => (string) Str::uuid()]);
        $other = $this->auth(['role' => 'ORGANIZER', 'sub' => (string) Str::uuid()]);
        $c = Category::first();
        $this->putJson("/api/events/{$e->id}", $this->payload($c), $other)->assertStatus(403);
        $this->deleteJson("/api/events/{$e->id}", [], $other)->assertStatus(403);
        $this->postJson("/api/events/{$e->id}/submit", [], $other)->assertStatus(403);
    }

    public function test_seuls_les_brouillons_et_rejetes_sont_modifiables(): void
    {
        $e = $this->makeEvent('APPROVED');
        $org = $this->auth(['role' => 'ORGANIZER', 'sub' => $e->organizer_id]);
        $c = Category::first();
        $this->putJson("/api/events/{$e->id}", $this->payload($c), $org)->assertStatus(409);
    }

    public function test_organisateur_peut_annuler_son_evenement(): void
    {
        $e = $this->makeEvent('APPROVED');
        $org = $this->auth(['role' => 'ORGANIZER', 'sub' => $e->organizer_id]);
        $this->postJson("/api/events/{$e->id}/cancel", [], $org)->assertOk()->assertJsonPath('status', 'CANCELLED');
        $this->getJson('/api/events/'.$e->id)->assertStatus(404); // ne plus public
    }

    public function test_organizer_kind_a_la_creation_et_filtre_catalogue(): void
    {
        $this->makeEvent('APPROVED', ['title' => 'Institution Publique', 'organizer_kind' => 'INSTITUTION']);
        $this->makeEvent('APPROVED', ['title' => 'Privé', 'organizer_kind' => 'PRIVATE']);
        $this->makeEvent('APPROVED', ['title' => 'Sans type']); // défaut PRIVATE

        $r = $this->getJson('/api/events?organizer_kind=INSTITUTION');
        $titles = collect($r->json('data'))->pluck('title');
        $this->assertContains('Institution Publique', $titles);
        $this->assertNotContains('Privé', $titles);
        $this->assertNotContains('Sans type', $titles);

        // valeur invalide refusée
        $this->getJson('/api/events?organizer_kind=FOO')->assertStatus(422);
    }

    public function test_l_evenement_cree_via_apiporte_son_type(): void
    {
        $c = $this->category();
        $org = $this->auth(['role' => 'ORGANIZER', 'sub' => (string) Str::uuid()]);
        $r = $this->postJson('/api/events', $this->payload($c, ['organizer_kind' => 'INSTITUTION']), $org)->assertCreated();
        $this->assertSame('INSTITUTION', $r->json('organizer_kind'));
    }

    public function test_autorite_nationale_supervision_lecture_seule(): void
    {
        $e = $this->makeEvent('PENDING');
        $h = $this->auth(['role' => 'NATIONAL_AUTHORITY', 'sub' => (string) Str::uuid()]);

        // lecture globale autorisée
        $this->getJson('/api/admin/events', $h)->assertOk();
        $this->getJson('/api/event-stats', $h)->assertOk()->assertJsonStructure(['events_total', 'events_by_status', 'capacity_total', 'places_sold']);

        // aucune modération possible
        $this->postJson("/api/events/{$e->id}/approve", [], $h)->assertStatus(403);
        $this->postJson("/api/events/{$e->id}/reject", ['reason' => 'test'], $h)->assertStatus(403);
        // ni création/édition/suppression
        $this->postJson('/api/events', $this->payload(Category::first()), $h)->assertStatus(403);
    }

    public function test_validation_des_champs_a_la_creation(): void
    {
        $org = $this->auth(['role' => 'ORGANIZER']);
        $this->postJson('/api/events', ['title' => ''], $org)->assertStatus(422);
        $this->postJson('/api/events', $this->payload($this->category(), ['price' => -5]), $org)->assertStatus(422);
        $this->postJson('/api/events', $this->payload($this->category(), ['capacity' => 0]), $org)->assertStatus(422);
    }
}
