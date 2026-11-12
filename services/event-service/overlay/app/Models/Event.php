<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasUuids;

    protected $fillable = ['category_id', 'venue_id', 'title', 'description', 'image', 'image_credit', 'location', 'start_at', 'end_at', 'capacity', 'price', 'organizer_kind'];
    protected $casts = ['start_at' => 'datetime', 'end_at' => 'datetime', 'capacity' => 'integer', 'available_places' => 'integer', 'price' => 'decimal:2'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}
