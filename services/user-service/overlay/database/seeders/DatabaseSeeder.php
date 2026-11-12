<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** UUID fixes : partagés avec les seeders des autres services (organisateurs). Mot de passe démo : Password123! */
    public function run(): void
    {
        $rows = [
            ['00000000-0000-4000-8000-000000000001', 'Admin', 'MOVENT', 'admin@movent.test', 'ADMIN'],
            ['00000000-0000-4000-8000-000000000011', 'Koffi', 'Organisateur', 'organizer1@movent.test', 'ORGANIZER'],
            ['00000000-0000-4000-8000-000000000012', 'Ama', 'Événements', 'organizer2@movent.test', 'ORGANIZER'],
            ['00000000-0000-4000-8000-000000000021', 'Yao', 'Participant', 'participant1@movent.test', 'PARTICIPANT'],
            ['00000000-0000-4000-8000-000000000022', 'Afi', 'Participante', 'participant2@movent.test', 'PARTICIPANT'],
            ['00000000-0000-4000-8000-000000000023', 'Kodjo', 'Participant', 'participant3@movent.test', 'PARTICIPANT'],
            // Autorité nationale : supervision en lecture seule (événements, utilisateurs, réservations)
            ['00000000-0000-4000-8000-000000000031', 'Nyavi', 'Supervision', 'autorite@movent.test', 'NATIONAL_AUTHORITY'],
        ];
        foreach ($rows as [$id, $first, $last, $email, $role]) {
            $u = User::firstOrNew(['email' => $email]);
            if (! $u->exists) {
                $u->id = $id;
                $u->fill(['first_name' => $first, 'last_name' => $last, 'password' => 'Password123!', 'role' => $role, 'is_active' => true]);
                $u->save();
            }
        }
    }
}
