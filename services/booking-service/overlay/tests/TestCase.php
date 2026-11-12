<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $this->setEnv('JWT_SECRET', 'phpunit-jwt-secret-key-0123456789abcdef');
        $this->setEnv('INTERNAL_API_KEY', 'phpunit-internal-key');
        $this->setEnv('EVENT_SERVICE_URL', 'http://event-service.test');
        $this->setEnv('HOLD_MINUTES', '10');
        parent::setUp();
    }

    protected function setEnv(string $key, string $value): void
    {
        $_SERVER[$key] = $_ENV[$key] = $value;
        putenv($key.'='.$value);
    }

    /** JWT de test au format émis par AuthController. */
    protected function jwt(array $claims = []): string
    {
        $now = time();
        return \Firebase\JWT\JWT::encode($claims + [
            'sub' => '00000000-0000-4000-8000-00000000ffff',
            'role' => 'PARTICIPANT',
            'email' => 'test@movent.test',
            'name' => 'Test User',
            'iat' => $now,
            'exp' => $now + 3600,
        ], (string) env('JWT_SECRET'), 'HS256');
    }

    protected function auth(array $claims = []): array
    {
        return ['Authorization' => 'Bearer '.$this->jwt($claims)];
    }
}
