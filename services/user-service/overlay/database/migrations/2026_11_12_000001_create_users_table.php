<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('first_name');
            $t->string('last_name');
            $t->string('email')->unique();
            $t->string('password');
            $t->string('phone')->nullable();
            $t->string('role')->default('PARTICIPANT')->index(); // ADMIN, ORGANIZER, PARTICIPANT
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
