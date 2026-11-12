<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('participant_id')->index();      // référence externe (User Service)
            $t->uuid('event_id')->index();            // référence externe (Event Service)
            $t->uuid('organizer_id')->index();        // instantané : permet à l'organisateur de lister ses réservations
            $t->string('participant_name');           // instantanés historiques (titre, date, lieu) : voir README (dénormalisation assumée)
            $t->string('event_title');
            $t->timestamp('event_start_at')->nullable();
            $t->string('event_location')->nullable();
            $t->string('reference')->unique();
            $t->integer('quantity');
            $t->decimal('unit_price', 12, 2);
            $t->decimal('total_amount', 12, 2);
            $t->string('status')->default('PENDING')->index(); // PENDING, CONFIRMED, FAILED, CANCELLED, EXPIRED
            $t->timestamps();
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('booking_id')->constrained('bookings')->cascadeOnDelete();
            $t->decimal('amount', 12, 2);
            $t->string('method')->default('SIMULATED');
            $t->string('status')->default('PENDING')->index(); // PENDING, SUCCESS, FAILED
            $t->string('transaction_reference')->unique();
            $t->timestamps();
        });

        Schema::create('tickets', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('booking_id')->constrained('bookings')->cascadeOnDelete();
            $t->string('ticket_reference')->unique();
            $t->string('qr_token')->unique();
            $t->string('status')->default('VALID')->index();   // VALID, CANCELLED, USED
            $t->timestamps();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_qty_chk CHECK (quantity > 0)');
            DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_amount_chk CHECK (unit_price >= 0 AND total_amount >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bookings');
    }
};
