<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private string $pid;
    private string $eid;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pid = (string) Str::uuid();
        $this->eid = (string) Str::uuid();
    }

    private function participant(): array
    {
        return $this->auth(['sub' => $this->pid, 'role' => 'PARTICIPANT', 'name' => 'Participant Test']);
    }

    private function eventJson(float $price = 5000): array
    {
        return [
            'id' => $this->eid, 'title' => 'Concert Test', 'price' => $price,
            'organizer_id' => (string) Str::uuid(), 'status' => 'APPROVED',
            'start_at' => now()->addDays(5)->toIso8601String(), 'location' => 'Lomé',
        ];
    }

    /** Simule l'Event Service : GET event + hold/confirm/release. */
    private function fakeEventService(?array $event = null, array $opts = []): void
    {
        Http::fake(function ($request) use ($event, $opts) {
            $url = $request->url();
            if (str_ends_with($url, '/hold')) {
                return isset($opts['hold_status'])
                    ? Http::response(['message' => 'Places insuffisantes.'], $opts['hold_status'])
                    : Http::response(['id' => (string) Str::uuid(), 'status' => 'HELD'], 201);
            }
            if (str_ends_with($url, '/confirm-hold')) {
                return isset($opts['confirm_status'])
                    ? Http::response(['message' => 'expiré'], $opts['confirm_status'])
                    : Http::response(['id' => (string) Str::uuid(), 'status' => 'CONFIRMED'], 200);
            }
            if (str_ends_with($url, '/release-hold')) {
                return Http::response(['id' => (string) Str::uuid(), 'status' => 'RELEASED'], 200);
            }
            return $event === null
                ? Http::response(['message' => 'Not found'], 404)
                : Http::response($event, 200);
        });
    }

    public function test_reservation_payante_puis_paiement_reussi(): void
    {
        $this->fakeEventService($this->eventJson(5000));
        $r = $this->postJson('/api/bookings', ['event_id' => $this->eid, 'quantity' => 2], $this->participant());
        $r->assertCreated()->assertJsonPath('status', 'PENDING')->assertJsonPath('total_amount', '10000.00');
        $bid = $r->json('id');

        $p = $this->postJson("/api/bookings/{$bid}/pay", ['outcome' => 'success'], $this->participant());
        $p->assertOk()->assertJsonPath('status', 'CONFIRMED');

        $this->assertDatabaseHas('payments', ['booking_id' => $bid, 'status' => 'SUCCESS', 'method' => 'SIMULATED']);
        $tickets = Ticket::where('booking_id', $bid)->get();
        $this->assertCount(2, $tickets);
        $this->assertCount(2, $tickets->pluck('qr_token')->unique()); // QR uniques
    }

    public function test_evenement_gratuit_confirme_immediatement(): void
    {
        $this->fakeEventService($this->eventJson(0));
        $r = $this->postJson('/api/bookings', ['event_id' => $this->eid, 'quantity' => 1], $this->participant());
        $r->assertCreated()->assertJsonPath('status', 'CONFIRMED');
        $this->assertSame(1, Ticket::where('booking_id', $r->json('id'))->count());
    }

    public function test_paiement_echoue_et_places_liberees(): void
    {
        $this->fakeEventService($this->eventJson(5000));
        $bid = $this->postJson('/api/bookings', ['event_id' => $this->eid, 'quantity' => 1], $this->participant())->json('id');

        $p = $this->postJson("/api/bookings/{$bid}/pay", ['outcome' => 'failed'], $this->participant());
        $p->assertOk()->assertJsonPath('status', 'FAILED');
        $this->assertDatabaseHas('payments', ['booking_id' => $bid, 'status' => 'FAILED']);
        $this->assertSame(0, Ticket::where('booking_id', $bid)->count());
        Http::assertSent(fn ($req) => str_ends_with($req->url(), '/release-hold'));
    }

    public function test_quantite_superieure_aux_places_refusee(): void
    {
        $this->fakeEventService($this->eventJson(5000), ['hold_status' => 409]);
        $this->postJson('/api/bookings', ['event_id' => $this->eid, 'quantity' => 500], $this->participant())
            ->assertStatus(422); // quantity max:10 côté booking
        $this->postJson('/api/bookings', ['event_id' => $this->eid, 'quantity' => 5], $this->participant())
            ->assertStatus(409);
        $this->assertSame(0, Booking::count());
    }

    public function test_evenement_inexistant_ou_non_publie(): void
    {
        $this->fakeEventService(null);
        $this->postJson('/api/bookings', ['event_id' => $this->eid, 'quantity' => 1], $this->participant())
            ->assertStatus(404);
    }

    public function test_double_paiement_refuse(): void
    {
        $this->fakeEventService($this->eventJson(5000));
        $bid = $this->postJson('/api/bookings', ['event_id' => $this->eid, 'quantity' => 1], $this->participant())->json('id');
        $this->postJson("/api/bookings/{$bid}/pay", ['outcome' => 'success'], $this->participant())->assertOk();
        $this->postJson("/api/bookings/{$bid}/pay", ['outcome' => 'success'], $this->participant())->assertStatus(409);
    }

    public function test_paiement_apres_expiration_du_hold(): void
    {
        $this->fakeEventService($this->eventJson(5000), ['confirm_status' => 410]);
        $bid = $this->postJson('/api/bookings', ['event_id' => $this->eid, 'quantity' => 1], $this->participant())->json('id');
        $this->postJson("/api/bookings/{$bid}/pay", ['outcome' => 'success'], $this->participant())
            ->assertStatus(410);
        $this->assertSame('EXPIRED', Booking::find($bid)->status);
    }

    public function test_reservation_sans_token_refusee(): void
    {
        $this->postJson('/api/bookings', ['event_id' => $this->eid, 'quantity' => 1])->assertStatus(401);
    }
}
