<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): string
    {
        $u = User::create([
            'first_name' => 'Root', 'last_name' => 'Admin', 'email' => 'root@movent.test',
            'password' => 'Password123!', 'role' => 'ADMIN', 'is_active' => true,
        ]);
        return $u->id;
    }

    public function test_participant_ne_peut_pas_lister_les_utilisateurs(): void
    {
        $this->getJson('/api/users', $this->auth(['role' => 'PARTICIPANT']))->assertStatus(403);
        $this->getJson('/api/users', $this->auth(['role' => 'ORGANIZER']))->assertStatus(403);
    }

    public function test_admin_peut_lister_les_utilisateurs(): void
    {
        $this->getJson('/api/users', $this->auth(['role' => 'ADMIN', 'sub' => $this->admin()]))
            ->assertOk()->assertJsonStructure(['data', 'total']);
    }

    public function test_admin_peut_changer_un_role(): void
    {
        $adminId = $this->admin();
        $target = User::create([
            'first_name' => 'A', 'last_name' => 'B', 'email' => 'a@b.test',
            'password' => 'Password123!', 'role' => 'PARTICIPANT', 'is_active' => true,
        ]);
        $this->patchJson("/api/users/{$target->id}/role", ['role' => 'ORGANIZER'], $this->auth(['role' => 'ADMIN', 'sub' => $adminId]))
            ->assertOk()->assertJsonPath('role', 'ORGANIZER');
    }

    public function test_admin_ne_peut_pas_changer_son_propre_role(): void
    {
        $adminId = $this->admin();
        $this->patchJson("/api/users/{$adminId}/role", ['role' => 'PARTICIPANT'], $this->auth(['role' => 'ADMIN', 'sub' => $adminId]))
            ->assertStatus(409);
    }

    public function test_admin_peut_desactiver_un_compte(): void
    {
        $adminId = $this->admin();
        $target = User::create([
            'first_name' => 'C', 'last_name' => 'D', 'email' => 'c@d.test',
            'password' => 'Password123!', 'role' => 'PARTICIPANT', 'is_active' => true,
        ]);
        $this->patchJson("/api/users/{$target->id}/status", ['is_active' => false], $this->auth(['role' => 'ADMIN', 'sub' => $adminId]))
            ->assertOk()->assertJsonPath('is_active', false);
    }

    public function test_autorite_nationale_supervision_en_lecture_seule(): void
    {
        $h = $this->auth(['role' => 'NATIONAL_AUTHORITY', 'sub' => (string) Str::uuid()]);
        $this->getJson('/api/users', $h)->assertOk()->assertJsonStructure(['data']);

        $target = User::create([
            'first_name' => 'G', 'last_name' => 'H', 'email' => 'g@h.test',
            'password' => 'Password123!', 'role' => 'PARTICIPANT', 'is_active' => true,
        ]);
        // aucune écriture pour l'autorité nationale
        $this->patchJson("/api/users/{$target->id}/role", ['role' => 'ADMIN'], $h)->assertStatus(403);
        $this->patchJson("/api/users/{$target->id}/status", ['is_active' => false], $h)->assertStatus(403);
    }

    public function test_role_inconnu_refuse(): void
    {
        $adminId = $this->admin();
        $target = User::create([
            'first_name' => 'E', 'last_name' => 'F', 'email' => 'e@f.test',
            'password' => 'Password123!', 'role' => 'PARTICIPANT', 'is_active' => true,
        ]);
        $this->patchJson("/api/users/{$target->id}/role", ['role' => 'SUPERADMIN'], $this->auth(['role' => 'ADMIN', 'sub' => $adminId]))
            ->assertStatus(422);
    }
}
