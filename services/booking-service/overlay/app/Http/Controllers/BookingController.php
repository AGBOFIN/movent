<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    private function ev(string $path): string
    {
        return rtrim((string) env('EVENT_SERVICE_URL'), '/').$path;
    }

    private function internal(): PendingRequest
    {
        return Http::timeout(8)->acceptJson()->withHeaders(['X-Internal-Key' => (string) env('INTERNAL_API_KEY')]);
    }

    private function mine(Request $r, string $id): Booking
    {
        return Booking::where('participant_id', $r->attributes->get('auth')['id'])->with(['payment', 'tickets'])->findOrFail($id);
    }

    private function makeTickets(Booking $b): void
    {
        for ($i = 1; $i <= $b->quantity; $i++) {
            Ticket::create([
                'booking_id' => $b->id,
                'ticket_reference' => $b->reference.'-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'qr_token' => bin2hex(random_bytes(24)),
                'status' => 'VALID',
            ]);
        }
    }

    public function index(Request $r)
    {
        $uid = $r->attributes->get('auth')['id'];
        // Les réservations non payées après la durée de blocage sont expirées (le blocage est libéré côté Event Service)
        Booking::where('participant_id', $uid)->where('status', 'PENDING')
            ->where('created_at', '<', now()->subMinutes((int) env('HOLD_MINUTES', 10) + 1))->update(['status' => 'EXPIRED']);
        return Booking::where('participant_id', $uid)->with(['payment', 'tickets'])->orderByDesc('created_at')->get();
    }

    public function show(Request $r, string $id)
    {
        return $this->mine($r, $id);
    }

    public function store(Request $r)
    {
        $a = $r->attributes->get('auth');
        $d = $r->validate(['event_id' => 'required|uuid', 'quantity' => 'required|integer|min:1|max:10']);

        $res = Http::timeout(8)->acceptJson()->get($this->ev('/api/events/'.$d['event_id']));
        if ($res->status() === 404) {
            return response()->json(['message' => 'Événement introuvable ou non publié.'], 404);
        }
        if (! $res->ok()) {
            return response()->json(['message' => 'Service événements indisponible.'], 502);
        }
        $ev = $res->json();

        $id = (string) Str::uuid();
        $hold = $this->internal()->post($this->ev('/api/events/'.$d['event_id'].'/hold'), ['booking_id' => $id, 'quantity' => $d['quantity']]);
        if ($hold->status() === 409) {
            return response()->json(['message' => $hold->json('message') ?: 'Places insuffisantes.'], 409);
        }
        if (! $hold->successful()) {
            return response()->json(['message' => 'Impossible de bloquer les places.'], 502);
        }

        $unit = (float) $ev['price'];
        try {
            $b = new Booking();
            $b->id = $id;
            $b->fill([
                'participant_id' => $a['id'], 'participant_name' => $a['name'], 'event_id' => $d['event_id'],
                'organizer_id' => $ev['organizer_id'], 'event_title' => $ev['title'],
                'event_start_at' => $ev['start_at'], 'event_location' => $ev['location'],
                'reference' => 'MVT-'.strtoupper(Str::random(8)), 'quantity' => $d['quantity'],
                'unit_price' => $unit, 'total_amount' => round($unit * $d['quantity'], 2), 'status' => 'PENDING',
            ])->save();
        } catch (\Throwable $e) {
            $this->internal()->post($this->ev('/api/events/'.$d['event_id'].'/release-hold'), ['booking_id' => $id]);
            throw $e;
        }

        if ($b->total_amount <= 0) { // événement gratuit : confirmation immédiate
            $c = $this->internal()->post($this->ev('/api/events/'.$b->event_id.'/confirm-hold'), ['booking_id' => $b->id]);
            if (in_array($c->status(), [404, 410], true)) {
                $b->update(['status' => 'EXPIRED']);
                return response()->json(['message' => 'Le blocage des places a expiré.'], 410);
            }
            if (! $c->successful()) {
                return response()->json(['message' => 'Confirmation impossible, réessayez.'], 502);
            }
            $b->update(['status' => 'CONFIRMED']);
            $this->makeTickets($b);
        }
        return response()->json($b->fresh(['payment', 'tickets']), 201);
    }

    /** Paiement SIMULÉ : le client choisit « success » ou « failed ». */
    public function pay(Request $r, string $id)
    {
        $d = $r->validate(['outcome' => 'required|in:success,failed']);
        $b = $this->mine($r, $id);
        abort_unless($b->status === 'PENDING', 409, 'Cette réservation n\'est plus en attente de paiement.');
        $ref = 'SIM-'.strtoupper(Str::random(10));

        if ($d['outcome'] === 'success') {
            $c = $this->internal()->post($this->ev('/api/events/'.$b->event_id.'/confirm-hold'), ['booking_id' => $b->id]);
            if (in_array($c->status(), [404, 410], true)) {
                DB::transaction(function () use ($b, $ref) {
                    Payment::create(['booking_id' => $b->id, 'amount' => $b->total_amount, 'method' => 'SIMULATED', 'status' => 'FAILED', 'transaction_reference' => $ref]);
                    $b->update(['status' => 'EXPIRED']);
                });
                return response()->json(['message' => 'Le délai de paiement est dépassé : les places ont été libérées.'], 410);
            }
            if (! $c->successful()) {
                return response()->json(['message' => 'Confirmation impossible, réessayez.'], 502);
            }
            DB::transaction(function () use ($b, $ref) {
                Payment::create(['booking_id' => $b->id, 'amount' => $b->total_amount, 'method' => 'SIMULATED', 'status' => 'SUCCESS', 'transaction_reference' => $ref]);
                $b->update(['status' => 'CONFIRMED']);
                $this->makeTickets($b);
            });
        } else {
            $this->internal()->post($this->ev('/api/events/'.$b->event_id.'/release-hold'), ['booking_id' => $b->id]);
            DB::transaction(function () use ($b, $ref) {
                Payment::create(['booking_id' => $b->id, 'amount' => $b->total_amount, 'method' => 'SIMULATED', 'status' => 'FAILED', 'transaction_reference' => $ref]);
                $b->update(['status' => 'FAILED']);
            });
        }
        return $this->mine($r, $id);
    }

    public function cancel(Request $r, string $id)
    {
        $b = $this->mine($r, $id);
        abort_unless(in_array($b->status, ['PENDING', 'CONFIRMED'], true), 409, 'Cette réservation ne peut pas être annulée.');
        $this->internal()->post($this->ev('/api/events/'.$b->event_id.'/release-hold'), ['booking_id' => $b->id]);
        DB::transaction(function () use ($b) {
            $b->update(['status' => 'CANCELLED']);
            Ticket::where('booking_id', $b->id)->update(['status' => 'CANCELLED']);
        });
        return $this->mine($r, $id);
    }
}
