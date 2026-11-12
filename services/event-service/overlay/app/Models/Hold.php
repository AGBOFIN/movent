<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** Blocage temporaire de places pendant une réservation (HELD -> CONFIRMED | RELEASED | EXPIRED). */
class Hold extends Model
{
    use HasUuids;

    protected $guarded = [];
    protected $casts = ['expires_at' => 'datetime'];

    /** Libère les places des blocages expirés (appelé à la demande, sans scheduler). */
    public static function expireStale(): void
    {
        DB::transaction(function () {
            $stale = static::where('status', 'HELD')->where('expires_at', '<', now())->lockForUpdate()->get();
            foreach ($stale as $h) {
                Event::where('id', $h->event_id)->increment('available_places', $h->quantity);
                $h->update(['status' => 'EXPIRED']);
            }
        });
    }
}
