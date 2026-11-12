<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $r)
    {
        $r->validate(['role' => 'nullable|in:ADMIN,NATIONAL_AUTHORITY,ORGANIZER,PARTICIPANT', 'q' => 'nullable|string|max:100']);
        $q = User::query()->orderByDesc('created_at');
        if ($r->filled('role')) {
            $q->where('role', $r->query('role'));
        }
        if ($r->filled('q')) {
            $like = '%'.mb_strtolower($r->query('q')).'%';
            $q->where(fn ($w) => $w->whereRaw('LOWER(email) LIKE ?', [$like])
                ->orWhereRaw('LOWER(first_name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(last_name) LIKE ?', [$like]));
        }
        return $q->paginate(50);
    }

    public function updateRole(Request $r, string $id)
    {
        $d = $r->validate(['role' => 'required|in:ADMIN,NATIONAL_AUTHORITY,ORGANIZER,PARTICIPANT']);
        $u = User::findOrFail($id);
        abort_if($u->id === $r->attributes->get('auth')['id'], 409, 'Vous ne pouvez pas modifier votre propre rôle.');
        $u->update($d);
        return $u;
    }

    public function updateStatus(Request $r, string $id)
    {
        $d = $r->validate(['is_active' => 'required|boolean']);
        $u = User::findOrFail($id);
        abort_if($u->id === $r->attributes->get('auth')['id'], 409, 'Vous ne pouvez pas désactiver votre propre compte.');
        $u->update($d);
        return $u;
    }
}
