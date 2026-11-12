<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Hold;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HoldTest extends TestCase
{
    use RefreshDatabase;

    private function approvedEvent(int $capacity = 10): Event
    {
        $c = Category::firstOrCreate(['name' => 'Concert']);
        $e = new Event([
            'category_id' => $c->id, 'title' => 'Event', 'location' => 'Lomé',
            'start_at' => now()->addDays(3), 'end_at' => now()->addDays(3)->addHour(),
            'capacity' => $capacity, 'price' => 1000,
        ]);
        $e->organizer_id = (string) Str::uuid();
        $e->available_places = $capacity;
        $e->status = 'APPROVED';
        $e->save();
        return $e;
    }

    private function holdHeaders(): array
    {
        return ['X-Internal-Key' => 'phpunit-internal-key', 'Accept' => 'application/json'];
    }

    public function test_routes_internes_exigent_la_cle(): void
    {
        $e = $this->approvedEvent();
        $body = ['booking_id' => (string) Str::uuid(), 'quantity' => 1];
        $this->postJson("/api/events/{$e->id}/hold", $body)->assertStatus(403);
        $this->postJson("/api/events/{$e->id}/hold", $body, ['X-Internal-Key' => 'mauvaise'])->assertStatus(403);
    }

    public function test_hold_decremente_les_places(): void
    {
        $e = $this->approvedEvent(10);
        $bid = (string) Str::uuid();
        $r = $this->postJson("/api/events/{$e->id}/hold", ['booking_id' => $bid, 'quantity' => 4], $this->holdHeaders());
        $r->assertCreated()->assertJsonPath('status', 'HELD')->assertJsonPath('quantity', 4);
        $this->assertSame(6, $e->fresh()->available_places);
    }

    public function test_hold_refuse_une_quantite_superieure_aux_places(): void
    {
        $e = $this->approvedEvent(5);
        $this->postJson("/api/events/{$e->id}/hold", ['booking_id' => (string) Str::uuid(), 'quantity' => 6], $this->holdHeaders())
            ->assertStatus(409);
        $this->assertSame(5, $e->fresh()->available_places);
    }

    public function test_deux_holds_simultanes_dernieres_places_un_seul_reussit(): void
    {
        $e = $this->approvedEvent(5);
        $ok = $this->postJson("/api/events/{$e->id}/hold", ['booking_id' => (string) Str::uuid(), 'quantity' => 5], $this->holdHeaders());
        $ko = $this->postJson("/api/events/{$e->id}/hold", ['booking_id' => (string) Str::uuid(), 'quantity' => 1], $this->holdHeaders());
        $this->assertTrue($ok->isCreated());
        $ko->assertStatus(409);
        $this->assertSame(0, $e->fresh()->available_places);
    }

    public function test_hold_est_idempotent_par_booking(): void
    {
        $e = $this->approvedEvent(10);
        $bid = (string) Str::uuid();
        $body = ['booking_id' => $bid, 'quantity' => 3];
        $this->postJson("/api/events/{$e->id}/hold", $body, $this->holdHeaders())->assertCreated();
        $this->postJson("/api/events/{$e->id}/hold", $body, $this->holdHeaders())->assertOk();
        $this->assertSame(7, $e->fresh()->available_places); // pas de double décrément
    }

    public function test_release_rend_les_places(): void
    {
        $e = $this->approvedEvent(10);
        $bid = (string) Str::uuid();
        $this->postJson("/api/events/{$e->id}/hold", ['booking_id' => $bid, 'quantity' => 4], $this->holdHeaders());
        $this->postJson("/api/events/{$e->id}/release-hold", ['booking_id' => $bid], $this->holdHeaders())
            ->assertOk()->assertJsonPath('status', 'RELEASED');
        $this->assertSame(10, $e->fresh()->available_places);
    }

    public function test_confirm_puis_release(): void
    {
        $e = $this->approvedEvent(10);
        $bid = (string) Str::uuid();
        $this->postJson("/api/events/{$e->id}/hold", ['booking_id' => $bid, 'quantity' => 2], $this->holdHeaders());
        $this->postJson("/api/events/{$e->id}/confirm-hold", ['booking_id' => $bid], $this->holdHeaders())
            ->assertOk()->assertJsonPath('status', 'CONFIRMED');
        // confirm est idempotent
        $this->postJson("/api/events/{$e->id}/confirm-hold", ['booking_id' => $bid], $this->holdHeaders())->assertOk();
        $this->postJson("/api/events/{$e->id}/release-hold", ['booking_id' => $bid], $this->holdHeaders())
            ->assertOk()->assertJsonPath('status', 'RELEASED');
        $this->assertSame(10, $e->fresh()->available_places);
    }

    public function test_un_hold_expire_libere_les_places(): void
    {
        $e = $this->approvedEvent(10);
        $bid = (string) Str::uuid();
        $this->postJson("/api/events/{$e->id}/hold", ['booking_id' => $bid, 'quantity' => 3], $this->holdHeaders());
        Hold::where('booking_id', $bid)->update(['expires_at' => now()->subMinute()]);

        Hold::expireStale();
        $this->assertSame(10, $e->fresh()->available_places);
        $this->assertSame('EXPIRED', Hold::where('booking_id', $bid)->value('status'));

        // confirm sur un hold expiré -> 410
        $this->postJson("/api/events/{$e->id}/confirm-hold", ['booking_id' => $bid], $this->holdHeaders())->assertStatus(410);
    }

    public function test_hold_refuse_un_evenement_non_approuve(): void
    {
        $c = Category::firstOrCreate(['name' => 'Sport']);
        $e = new Event([
            'category_id' => $c->id, 'title' => 'Brouillon', 'location' => 'Lomé',
            'start_at' => now()->addDays(3), 'end_at' => now()->addDays(3)->addHour(),
            'capacity' => 10, 'price' => 0,
        ]);
        $e->organizer_id = (string) Str::uuid();
        $e->available_places = 10;
        $e->status = 'DRAFT';
        $e->save();

        $this->postJson("/api/events/{$e->id}/hold", ['booking_id' => (string) Str::uuid(), 'quantity' => 1], $this->holdHeaders())
            ->assertStatus(409); // événement trouvé mais non APPROVED
    }
}
