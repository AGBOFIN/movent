<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Hold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Routes INTERNES (appelées par le Booking Service, protégées par X-Internal-Key). */
class HoldController extends Controller
{
    public function hold(Request $r, string $id)
    {
        $d = $r->validate(['booking_id' => 'required|uuid', 'quantity' => 'required|integer|min:1|max:50']);

        return DB::transaction(function () use ($d, $id) {
            Hold::expireStale();
            $e = Event::lockForUpdate()->find($id);
            abort_if(! $e, 404, 'Événement introuvable.');
            abort_unless($e->status === 'APPROVED' && $e->end_at->isFuture(), 409, 'Événement non disponible à la réservation.');

            if ($existing = Hold::where('booking_id', $d['booking_id'])->first()) { // idempotence
                return response()->json($existing, 200);
            }
            abort_if($e->available_places < $d['quantity'], 409, 'Places insuffisantes (il reste '.$e->available_places.').');

            $e->decrement('available_places', $d['quantity']);
            $h = Hold::create([
                'event_id' => $e->id, 'booking_id' => $d['booking_id'], 'quantity' => $d['quantity'],
                'status' => 'HELD', 'expires_at' => now()->addMinutes((int) env('HOLD_MINUTES', 10)),
            ]);
            return response()->json($h, 201);
        });
    }

    public function confirm(Request $r, string $id)
    {
        $d = $r->validate(['booking_id' => 'required|uuid']);
        return DB::transaction(function () use ($d, $id) {
            Hold::expireStale();
            $h = Hold::where('event_id', $id)->where('booking_id', $d['booking_id'])->lockForUpdate()->first();
            abort_if(! $h, 404, 'Blocage introuvable.');
            if ($h->status === 'CONFIRMED') {
                return $h;
            }
            abort_unless($h->status === 'HELD', 410, 'Le blocage des places a expiré.');
            $h->update(['status' => 'CONFIRMED']);
            return $h;
        });
    }

    public function release(Request $r, string $id)
    {
        $d = $r->validate(['booking_id' => 'required|uuid']);
        return DB::transaction(function () use ($d, $id) {
            $h = Hold::where('event_id', $id)->where('booking_id', $d['booking_id'])->lockForUpdate()->first();
            abort_if(! $h, 404, 'Blocage introuvable.');
            if (in_array($h->status, ['HELD', 'CONFIRMED'], true)) {
                Event::where('id', $id)->increment('available_places', $h->quantity);
                $h->update(['status' => 'RELEASED']);
            }
            return $h;
        });
    }
}
