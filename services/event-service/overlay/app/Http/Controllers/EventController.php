<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Hold;
use Illuminate\Http\Request;

class EventController extends Controller
{
    private function rules(): array
    {
        return [
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:5000',
            'category_id' => 'required|uuid|exists:categories,id',
            'image' => 'nullable|url|max:500',
            'location' => 'required|string|max:200',
            'start_at' => 'required|date|after:now',
            'end_at' => 'required|date|after:start_at',
            'capacity' => 'required|integer|min:1|max:100000',
            'price' => 'required|numeric|min:0|max:100000000',
            'organizer_kind' => 'nullable|in:INSTITUTION,PRIVATE',
        ];
    }

    private function own(Request $r, Event $e): void
    {
        $a = $r->attributes->get('auth');
        abort_unless($a['role'] === 'ADMIN' || $e->organizer_id === $a['id'], 403, 'Cet événement ne vous appartient pas.');
    }

    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    /** Catalogue public : uniquement les événements APPROVED à venir. */
    public function index(Request $r)
    {
        $r->validate(['category_id' => 'nullable|uuid', 'price_max' => 'nullable|numeric|min:0', 'q' => 'nullable|string|max:100', 'organizer_kind' => 'nullable|in:INSTITUTION,PRIVATE']);
        Hold::expireStale();
        $q = Event::with('category')->where('status', 'APPROVED')->where('end_at', '>=', now());
        if ($r->filled('q')) {
            $like = '%'.mb_strtolower($r->query('q')).'%';
            $q->where(fn ($w) => $w->whereRaw('LOWER(title) LIKE ?', [$like])->orWhereRaw('LOWER(location) LIKE ?', [$like]));
        }
        if ($r->filled('category_id')) {
            $q->where('category_id', $r->query('category_id'));
        }
        if ($r->boolean('free')) {
            $q->where('price', 0);
        } elseif ($r->filled('price_max')) {
            $q->where('price', '<=', $r->query('price_max'));
        }
        if ($r->filled('organizer_kind')) {
            $q->where('organizer_kind', $r->query('organizer_kind'));
        }
        return $q->orderBy('start_at')->paginate(12);
    }

    public function show(string $id)
    {
        Hold::expireStale();
        return Event::with('category')->where('status', 'APPROVED')->findOrFail($id);
    }

    public function store(Request $r)
    {
        $d = $r->validate($this->rules());
        $e = new Event($d);
        $e->organizer_id = $r->attributes->get('auth')['id'];
        $e->organizer_kind = $d['organizer_kind'] ?? 'PRIVATE';
        $e->available_places = $d['capacity'];
        $e->status = 'DRAFT';
        $e->save();
        return response()->json($e->load('category'), 201);
    }

    public function update(Request $r, string $id)
    {
        $e = Event::findOrFail($id);
        $this->own($r, $e);
        abort_unless(in_array($e->status, ['DRAFT', 'REJECTED'], true), 409, 'Seuls les événements en brouillon ou rejetés sont modifiables.');
        $d = $r->validate($this->rules());
        $e->fill($d);
        $e->available_places = $d['capacity'];
        $e->save();
        return $e->load('category');
    }

    public function destroy(Request $r, string $id)
    {
        $e = Event::findOrFail($id);
        $this->own($r, $e);
        abort_unless(in_array($e->status, ['DRAFT', 'REJECTED', 'CANCELLED'], true), 409, 'Suppression impossible pour cet état.');
        $e->delete();
        return response()->noContent();
    }

    public function myEvents(Request $r)
    {
        return Event::with('category')->where('organizer_id', $r->attributes->get('auth')['id'])->orderByDesc('created_at')->get();
    }

    public function myEvent(Request $r, string $id)
    {
        $e = Event::with('category')->findOrFail($id);
        $this->own($r, $e);
        return $e;
    }

    public function submit(Request $r, string $id)
    {
        $e = Event::findOrFail($id);
        $this->own($r, $e);
        abort_unless(in_array($e->status, ['DRAFT', 'REJECTED'], true), 409, 'Cet événement ne peut pas être soumis.');
        $e->update(['status' => 'PENDING', 'rejection_reason' => null]);
        return $e->load('category');
    }

    public function approve(string $id)
    {
        $e = Event::findOrFail($id);
        abort_unless($e->status === 'PENDING', 409, 'Seuls les événements en attente peuvent être validés.');
        $e->update(['status' => 'APPROVED', 'rejection_reason' => null]);
        return $e->load('category');
    }

    public function reject(Request $r, string $id)
    {
        $d = $r->validate(['reason' => 'required|string|min:3|max:500']);
        $e = Event::findOrFail($id);
        abort_unless($e->status === 'PENDING', 409, 'Seuls les événements en attente peuvent être rejetés.');
        $e->update(['status' => 'REJECTED', 'rejection_reason' => $d['reason']]);
        return $e->load('category');
    }

    public function cancel(Request $r, string $id)
    {
        $e = Event::findOrFail($id);
        $this->own($r, $e);
        abort_if($e->status === 'CANCELLED', 409, 'Événement déjà annulé.');
        $e->update(['status' => 'CANCELLED']);
        return $e->load('category');
    }

    /** Administration : tous les événements, filtre par statut. */
    public function adminIndex(Request $r)
    {
        $r->validate(['status' => 'nullable|in:DRAFT,PENDING,APPROVED,REJECTED,CANCELLED']);
        $q = Event::with('category')->orderByDesc('created_at');
        if ($r->filled('status')) {
            $q->where('status', $r->query('status'));
        }
        return $q->paginate(50);
    }

    /** Statistiques : ADMIN = global, ORGANIZER = ses événements. */
    public function stats(Request $r)
    {
        $a = $r->attributes->get('auth');
        $q = Event::query();
        // ADMIN et NATIONAL_AUTHORITY supervisent l'ensemble du territoire ;
        // un ORGANIZER ne voit que ses propres événements.
        if (! in_array($a['role'], ['ADMIN', 'NATIONAL_AUTHORITY'], true)) {
            $q->where('organizer_id', $a['id']);
        }
        $byStatus = (clone $q)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $approved = (clone $q)->where('status', 'APPROVED');
        return [
            'events_by_status' => $byStatus,
            'events_total' => (int) $byStatus->sum(),
            'capacity_total' => (int) (clone $approved)->sum('capacity'),
            'places_sold' => (int) (clone $approved)->selectRaw('COALESCE(SUM(capacity - available_places),0) as s')->value('s'),
        ];
    }
}
