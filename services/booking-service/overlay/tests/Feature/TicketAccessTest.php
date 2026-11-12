<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TicketAccessTest extends TestCase
{
    use RefreshDatabase;

    private function bookingFor(string $pid, string $status = 'CONFIRMED'): Booking
    {
        $b = Booking::create([
            'participant_id' => $pid,
            'participant_name' => 'Participant',
            'event_id' => (string) Str::uuid(),
            'organizer_id' => (string) Str::uuid(),
            'event_title' => 'Concert',
            'reference' => 'MVT-'.strtoupper(Str::random(8)),
            'quantity' => 1,
            'unit_price' => 1000,
            'total_amount' => 1000,
            'status' => $status,
        ]);
        return $b;
    }

    private function ticketFor(Booking $b, string $status = 'VALID'): Ticket
    {
        return Ticket::create([
            'booking_id' => $b->id,
            'ticket_reference' => $b->reference.'-01',
            'qr_token' => bin2hex(random_bytes(24)),
            'status' => $status,
        ]);
    }

    private function asUser(string $pid): array
    {
        return $this->auth(['sub' => $pid, 'role' => 'PARTICIPANT']);
    }

    public function test_un_participant_ne_voit_pas_le_billet_d_un_autre(): void
    {
        $owner = (string) Str::uuid();
        $other = (string) Str::uuid();
        $t = $this->ticketFor($this->bookingFor($owner));

        $this->getJson('/api/tickets/'.$t->id, $this->asUser($other))->assertStatus(404);
        $this->getJson('/api/tickets/'.$t->id.'/download', $this->asUser($other))->assertStatus(404);
        $this->getJson('/api/tickets', $this->asUser($other))->assertOk()->assertExactJson([]);
    }

    public function test_le_proprietaire_voit_et_telecharge_son_billet(): void
    {
        $pid = (string) Str::uuid();
        $t = $this->ticketFor($this->bookingFor($pid));

        $this->getJson('/api/tickets/'.$t->id, $this->asUser($pid))->assertOk()
            ->assertJsonPath('ticket_reference', $t->ticket_reference);

        $r = $this->get('/api/tickets/'.$t->id.'/download', $this->asUser($pid));
        $r->assertOk();
        $this->assertStringContainsString('pdf', (string) $r->headers->get('content-type'));
    }

    public function test_pas_de_billet_pour_une_reservation_echouee(): void
    {
        $pid = (string) Str::uuid();
        $this->bookingFor($pid, 'FAILED'); // aucune ticket généré
        $this->getJson('/api/tickets', $this->asUser($pid))->assertOk()->assertExactJson([]);
    }

    public function test_les_qr_sont_uniques(): void
    {
        $pid = (string) Str::uuid();
        $b = $this->bookingFor($pid);
        $t1 = $this->ticketFor($b);
        $t2 = Ticket::create([
            'booking_id' => $b->id, 'ticket_reference' => $b->reference.'-02',
            'qr_token' => bin2hex(random_bytes(24)), 'status' => 'VALID',
        ]);
        $this->assertNotSame($t1->qr_token, $t2->qr_token);
        $this->assertSame(1, Ticket::where('qr_token', $t1->qr_token)->count()); // token unique en base
    }

    public function test_billet_annule_non_telechargeable(): void
    {
        $pid = (string) Str::uuid();
        $t = $this->ticketFor($this->bookingFor($pid), 'CANCELLED');
        $this->get('/api/tickets/'.$t->id.'/download', $this->asUser($pid))->assertStatus(409);
    }

    public function test_organisateur_ne_voit_que_ses_reservations(): void
    {
        $org = (string) Str::uuid();
        $b1 = $this->bookingFor((string) Str::uuid());
        $b1->update(['organizer_id' => $org]);
        $b2 = $this->bookingFor((string) Str::uuid());
        $b2->update(['organizer_id' => (string) Str::uuid()]);

        $h = $this->auth(['sub' => $org, 'role' => 'ORGANIZER']);
        $r = $this->getJson('/api/organizer/bookings', $h)->assertOk();
        $refs = collect($r->json('data'))->pluck('id');
        $this->assertContains($b1->id, $refs);
        $this->assertNotContains($b2->id, $refs);
    }

    public function test_participant_acces_refuse_aux_stats_organisateur(): void
    {
        $this->getJson('/api/booking-stats', $this->asUser((string) Str::uuid()))->assertStatus(403);
        $this->getJson('/api/organizer/bookings', $this->asUser((string) Str::uuid()))->assertStatus(403);
    }

    public function test_autorite_nationale_voit_toutes_les_reservations(): void
    {
        $b1 = $this->bookingFor((string) Str::uuid());
        $b1->update(['organizer_id' => (string) Str::uuid()]);
        $b2 = $this->bookingFor((string) Str::uuid());
        $b2->update(['organizer_id' => (string) Str::uuid()]);

        $h = $this->auth(['sub' => (string) Str::uuid(), 'role' => 'NATIONAL_AUTHORITY']);
        $r = $this->getJson('/api/organizer/bookings', $h)->assertOk();
        $ids = collect($r->json('data'))->pluck('id');
        $this->assertContains($b1->id, $ids);
        $this->assertContains($b2->id, $ids);

        $this->getJson('/api/booking-stats', $h)->assertOk()->assertJsonStructure(['bookings_total', 'tickets_sold']);
    }
}
