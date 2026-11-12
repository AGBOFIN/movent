<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $t) {
            // INSTITUTION (ministère, administration, collectivité, cérémonie officielle)
            // ou PRIVATE (entreprise, association, agence, organisateur indépendant)
            $t->string('organizer_kind')->default('PRIVATE')->index()->after('organizer_id');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $t) {
            $t->dropIndex(['organizer_kind']);
            $t->dropColumn('organizer_kind');
        });
    }
};
