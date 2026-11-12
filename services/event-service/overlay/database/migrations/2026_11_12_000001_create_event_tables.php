<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('name')->unique();
            $t->text('description')->nullable();
            $t->timestamps();
        });

        Schema::create('events', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('organizer_id')->index();                      // référence externe (User Service), pas de FK
            $t->foreignUuid('category_id')->constrained('categories'); // FK autorisée : même base
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('image')->nullable();
            $t->string('location');
            $t->timestamp('start_at')->index();
            $t->timestamp('end_at');
            $t->integer('capacity');
            $t->integer('available_places');
            $t->decimal('price', 12, 2)->default(0);
            $t->string('status')->default('DRAFT')->index();        // DRAFT, PENDING, APPROVED, REJECTED, CANCELLED
            $t->text('rejection_reason')->nullable();
            $t->timestamps();
        });

        Schema::create('holds', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            $t->uuid('booking_id')->unique();                        // référence externe (Booking Service)
            $t->integer('quantity');
            $t->string('status')->default('HELD')->index();          // HELD, CONFIRMED, RELEASED, EXPIRED
            $t->timestamp('expires_at');
            $t->timestamps();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE events ADD CONSTRAINT events_capacity_chk CHECK (capacity >= 0 AND available_places >= 0 AND available_places <= capacity)');
            DB::statement('ALTER TABLE events ADD CONSTRAINT events_price_chk CHECK (price >= 0)');
            DB::statement('ALTER TABLE holds ADD CONSTRAINT holds_qty_chk CHECK (quantity > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('holds');
        Schema::dropIfExists('events');
        Schema::dropIfExists('categories');
    }
};
