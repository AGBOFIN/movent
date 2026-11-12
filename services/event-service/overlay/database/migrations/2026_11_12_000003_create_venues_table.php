<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('type');                       // HOTEL, PALACE, STADIUM, CULTURAL_CENTER, RESORT...
            $t->string('city');
            $t->string('address')->nullable();
            $t->text('description')->nullable();
            $t->unsignedInteger('capacity')->nullable(); // capacité indicative publique (personnes)
            $t->text('amenities')->nullable();        // équipements, liste séparée par des virgules ou sauts de ligne
            $t->string('website')->nullable();
            $t->string('image')->nullable();
            $t->string('image_credit')->nullable();      // attribution licence (ex. « Photo : X — CC BY 4.0 »)
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::table('events', function (Blueprint $t) {
            $t->foreignUuid('venue_id')->nullable()->after('category_id')->constrained('venues')->nullOnDelete();
            $t->string('image_credit')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $t) {
            $t->dropConstrainedForeignId('venue_id');
            $t->dropColumn('image_credit');
        });
        Schema::dropIfExists('venues');
    }
};
