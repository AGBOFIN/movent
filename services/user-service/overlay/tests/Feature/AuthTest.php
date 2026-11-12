<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $over = []): array
    {
        return array_merge([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ], $over);
    }

    public function test_register_cree_un_compte_et_renvoie_un_token(): void
    {
        $r = $this->postJson('/api/auth/register', $this->payload());
        $r->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'first_name', 'email', 'role']])
            ->assertJsonPath('user.role', 'PARTICIPANT');
        $this->assertNotEmpty($r->json('token'));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_register_refuse_un_email_deja_utilise(): void
    {
        $this->postJson('/api/auth/register', $this->payload())->assertCreated();
        $this->postJson('/api/auth/register', $this->payload(['first_name' => 'Autre']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_register_refuse_le_role_admin(): void
    {
        $this->postJson('/api/auth/register', $this->payload(['role' => 'ADMIN']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('role');
    }

    public function test_login_avec_bons_identifiants(): void
    {
        $this->postJson('/api/auth/register', $this->payload());
        $r = $this->postJson('/api/auth/login', ['email' => 'test@example.com', 'password' => 'Password123!']);
        $r->assertOk()->assertJsonStructure(['token', 'user']);
    }

    public function test_login_mauvais_mot_de_passe(): void
    {
        $this->postJson('/api/auth/register', $this->payload());
        $this->postJson('/api/auth/login', ['email' => 'test@example.com', 'password' => 'mauvais'])
            ->assertStatus(401);
    }

    public function test_login_compte_desactive(): void
    {
        $this->postJson('/api/auth/register', $this->payload());
        User::where('email', 'test@example.com')->update(['is_active' => false]);
        $this->postJson('/api/auth/login', ['email' => 'test@example.com', 'password' => 'Password123!'])
            ->assertStatus(403);
    }

    public function test_me_exige_un_token(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_me_avec_token_valide(): void
    {
        $r = $this->postJson('/api/auth/register', $this->payload());
        $token = $r->json('token');
        $this->withToken($token)->getJson('/api/me')
            ->assertOk()->assertJsonPath('email', 'test@example.com');
    }

    public function test_token_invalide(): void
    {
        $this->withToken('token-invalide')->getJson('/api/me')->assertStatus(401);
    }

    public function test_mise_a_jour_du_profil(): void
    {
        $r = $this->postJson('/api/auth/register', $this->payload());
        $this->withToken($r->json('token'))->putJson('/api/me', ['first_name' => 'Nouveau', 'phone' => '+22890000000'])
            ->assertOk()->assertJsonPath('first_name', 'Nouveau');
    }
}
