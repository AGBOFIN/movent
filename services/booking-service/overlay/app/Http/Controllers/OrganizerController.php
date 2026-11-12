<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Http\Request;

class OrganizerController extends Controller
{
    private function scope(Request $r)
    {
        $a = $r->attributes->get('auth');
        $q = Booking::query();
        // ADMIN et NATIONAL_AUTHORITY voient toutes les réservations du pays ;
        // un ORGANIZER ne voit que celles de ses événements.
        if (! in_array($a['role'], ['ADMIN', 'NATIONAL_AUTHORITY'], true)) {
            $q->where('organizer_id', $a['id']);
        }
        return $q;
    }

    /** Réservations des événements de l'organisateur (ADMIN : toutes). */
    public function bookings(Request $r)
    {
        return $this->scope($r)->with('payment')->orderByDesc('created_at')->paginate(50);
    }

    public function stats(Request $r)
    {
        $q = $this->scope($r);
        $confirmed = (clone $q)->where('status', 'CONFIRMED');
        return [
            'bookings_total' => (clone $q)->count(),
            'bookings_by_status' => (clone $q)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'confirmed_bookings' => (clone $confirmed)->count(),
            'tickets_sold' => (int) (clone $confirmed)->sum('quantity'),
            'revenue' => (float) (clone $confirmed)->sum('total_amount'),
        ];
    }
}
