<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_limitation_de_tentatives_de_connexion_s_applique(): void
    {
        // throttle:20,1 sur /api/auth/login — la 21e tentative dans la minute est refusée
        $codes = [];
        for ($i = 0; $i < 21; $i++) {
            $codes[] = $this->postJson('/api/auth/login', [
                'email' => 'inconnu@example.com', 'password' => 'x',
            ])->getStatusCode();
        }
        $this->assertContains(429, $codes);
    }
}
