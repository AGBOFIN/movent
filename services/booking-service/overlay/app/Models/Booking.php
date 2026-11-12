<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasUuids;

    protected $guarded = [];
    protected $casts = ['quantity' => 'integer', 'unit_price' => 'decimal:2', 'total_amount' => 'decimal:2', 'event_start_at' => 'datetime'];

    public function payment() { return $this->hasOne(Payment::class); }
    public function tickets() { return $this->hasMany(Ticket::class); }
}
