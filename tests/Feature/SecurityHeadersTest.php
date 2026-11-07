<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_public_pages_carry_baseline_security_headers(): void
    {
        // /login renders without DB seeding; headers are global middleware.
        $response = $this->get('/login');

        $response->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
    }

    public function test_api_responses_carry_baseline_security_headers(): void
    {
        $response = $this->getJson('/api/v1/plans');

        $response->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_hsts_is_absent_outside_production(): void
    {
        $this->get('/')
            ->assertHeaderMissing('Strict-Transport-Security');
    }
}
