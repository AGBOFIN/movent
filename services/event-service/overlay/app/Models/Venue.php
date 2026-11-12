<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'slug', 'type', 'city', 'address', 'description', 'capacity', 'amenities', 'website', 'image', 'image_credit', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'capacity' => 'integer'];

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
