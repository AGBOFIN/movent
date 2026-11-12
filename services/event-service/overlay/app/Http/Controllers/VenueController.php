<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    /** Catalogue public des lieux (actifs). */
    public function index(Request $r)
    {
        $r->validate(['city' => 'nullable|string|max:80', 'q' => 'nullable|string|max:100']);
        $q = Venue::where('is_active', true)->orderBy('city')->orderBy('name');
        if ($r->filled('city')) {
            $q->where('city', $r->query('city'));
        }
        if ($r->filled('q')) {
            $like = '%'.mb_strtolower($r->query('q')).'%';
            $q->where(fn ($w) => $w->whereRaw('LOWER(name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(city) LIKE ?', [$like])
                ->orWhereRaw('LOWER(address) LIKE ?', [$like]));
        }
        return $q->get();
    }

    /** Villes disposant de lieux référencés (pour les filtres). */
    public function cities()
    {
        return Venue::where('is_active', true)->select('city')->distinct()->orderBy('city')->pluck('city');
    }

    /** Détail public d'un lieu + événements approuvés à venir. */
    public function show(string $slug)
    {
        $v = Venue::where('is_active', true)->where('slug', $slug)->firstOrFail();
        $events = Event::with('category')
            ->where('venue_id', $v->id)
            ->where('status', 'APPROVED')
            ->where('end_at', '>=', now())
            ->orderBy('start_at')
            ->get();
        return ['venue' => $v, 'events' => $events];
    }
}
