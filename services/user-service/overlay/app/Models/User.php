<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasUuids;

    protected $fillable = ['first_name', 'last_name', 'email', 'password', 'phone', 'role', 'is_active'];
    protected $hidden = ['password'];
    protected $casts = ['is_active' => 'boolean', 'password' => 'hashed'];
}
